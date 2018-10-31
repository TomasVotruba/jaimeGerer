<?php
namespace AppBundle\Service\CRM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Psr\Log\LoggerInterface;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\Compta\CompteComptable;

/**
 * @copyright  Copyright (c) 2018
 * @author blancsebastien
 * Created on 29 oct. 2018, 10:06:17
 */
class CompteService
{

    private $em;
    private $tokenStorage;
    private $logger;
    private $fieldsToCheck = ['nom', 'telephone', 'adresse', 'ville', 'codePostal', 'region', 'pays', 'url', 'fax', 'codeEvoliz', 'priveOrPublic', 'compteComptableClient', 'compteComptableFournisseur'];

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * Fusionner 2 comptes ensemble.
     * 
     * @param Compte $compteA compte à garder
     * @param Compte $compteB compte à supprimer
     * @param CompteComptable $compteComptableClientToKeep
     * @param CompteComptable $compteComptableFournisseurToKeep
     * 
     * @return bool
     */
    public function mergeComptes(Compte $compteA, Compte $compteB, CompteComptable $compteComptableClientToKeep = null, CompteComptable $compteComptableFournisseurToKeep = null)
    {
        // Check params validity
        if(!$this->checkMergeParams($compteA, $compteB)){
            // Il faut sélectionner les champs à garder
            return false;
        }
        // Set data if missing
        foreach ($this->fieldsToCheck as $field){
            if(!self::needToChooseField($compteA, $compteB, $field)){
                $getVal = 'get' . ucfirst($field);
                $setVal = 'set' . ucfirst($field);
                if($compteB->$getVal()){
                    $compteA->$setVal($compteB->$getVal());
                }
            }
        }
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;
        // Description
        $userName = $user ? $this->tokenStorage->getToken()->getUsername() : 'Inconnu';
        $compteA->setDescription($compteA->getDescription() . ' -- ' . $compteA->getNom() . ' fusionné avec ' . $compteB->getNom() . ' le ' . (new \DateTime())->format('d/m/Y') . ' par ' . $userName . ' -- ' .$compteB->getDescription());
        // Modifié le / par
        if($user){
            $compteA->setUserEdition($user);
        }
        $compteA->setDateEdition(new \DateTime());
        // Compte comptable client : Journal de ventes, achats, banque, operations diverses
        if($compteA->getCompteComptableClient() && $compteB->getCompteComptableClient() && $compteA->getCompteComptableClient() !== $compteB->getCompteComptableClient()){
            if(!$compteComptableClientToKeep){
                // Il faut selectionner un compte comptable client à garder
                return false;
            }
            $compteToCopyFrom = $compteComptableClientToKeep === $compteA->getCompteComptableClient() ? $compteB->getCompteComptableClient() : $compteA->getCompteComptableClient();
            // Journal de ventes
            foreach ($compteToCopyFrom->getJournalVentes() as $journalVente){
                $journalVente->setCompteComptable($compteA->getCompteComptableClient());
            }
            // Journal d'achats
            foreach ($compteToCopyFrom->getJournalAchats() as $journalAchat){
                $journalAchat->setCompteComptable($compteA->getCompteComptableClient());
            }
            // Journal de banques
            foreach ($compteToCopyFrom->getJournalBanque() as $journalBanque){
                $journalBanque->setCompteComptable($compteA->getCompteComptableClient());
            }
            // Opérations diverses
            foreach ($compteToCopyFrom->getOperationsDiverses() as $operationDiverse){
                $operationDiverse->setCompteComptable($compteA->getCompteComptableClient());
            }
        }
        // Compte comptable fournisseur : Journal de ventes, achats, banque, operations diverses
        if($compteA->getCompteComptableFournisseur() && $compteB->getCompteComptableFournisseur() && $compteA->getCompteComptableFournisseur() !== $compteB->getCompteComptableFournisseur()){
            if(!!$compteComptableFournisseurToKeep){
                // Il faut selectionner un compte comptable fournisseur à garder
                return false;
            }            
            $compteToCopyFrom = $compteComptableFournisseurToKeep === $compteA->getCompteComptableFournisseur() ? $compteB->getCompteComptableFournisseur() : $compteA->getCompteComptableFournisseur();
            // Journal de ventes
            foreach ($compteToCopyFrom->getJournalVentes() as $journalVente){
                $journalVente->setCompteComptable($compteA->getCompteComptableFournisseur());
            }
            // Journal d'achats
            foreach ($compteToCopyFrom->getJournalAchats() as $journalAchat){
                $journalAchat->setCompteComptable($compteA->getCompteComptableFournisseur());
            }
            // Journal de banques
            foreach ($compteToCopyFrom->getJournalBanque() as $journalBanque){
                $journalBanque->setCompteComptable($compteA->getCompteComptableFournisseur());
            }
            // Opérations diverses
            foreach ($compteToCopyFrom->getOperationsDiverses() as $operationDiverse){
                $operationDiverse->setCompteComptable($compteA->getCompteComptableFournisseur());
            }
        }
        // Factures & Devis
        foreach ($compteB->getDocumentPrixs() as $documentPrix){
            $documentPrix->setCompte($compteA);
        }
        // Actions Commerciales
        foreach ($compteB->getOpportunites() as $opportunite){
            $opportunite->setCompte($compteA);
        }
        // Contacts
        foreach ($compteB->getContacts() as $contact){
            $contact->setCompte($compteA);
        }
        // Dépenses
        foreach ($compteB->getDepenses() as $depense){
            $depense->setCompte($compteA);
        }        
        
        try{
            $this->em->beginTransaction();
            $this->em->flush();
            $this->em->remove($compteB);
            $this->em->commit();
            
            return true;
        }catch(\Exception $e){
            $this->logger->critical('Error while merging Comptes ' . $compteA->getId() . ' and ' . $compteB->getNom() . ' : ' . $e->getMessage());
            
            return false;
        }
    }

    /**
     * Return true if data inside $newCompte is OK to be merged
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * 
     * @return boolean
     */
    private function checkMergeParams(Compte $compteA, Compte $compteB)
    {
        foreach ($this->fieldsToCheck as $field) {
            if (self::needToChooseField($compteA, $compteB, $field)) {
                $method = 'get' . ucfirst($field);
                if(!$compteA->$method()){
                    
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Return true if a given field must be chosen between compteA and compteB
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * @param string $field
     * 
     * @return boolean
     */
    public static function needToChooseField(Compte $compteA, Compte $compteB, $field)
    {
        $method = 'get' . ucfirst($field);
        if (method_exists(Compte::class, $method) && $compteA->$method() && $compteB->$method() && $compteA->$method() !== $compteB->$method()) {

            return true;
        }

        return false;
    }
}
