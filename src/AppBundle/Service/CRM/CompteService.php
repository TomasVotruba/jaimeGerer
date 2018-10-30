<?php
namespace AppBundle\Service\CRM;

use Doctrine\ORM\EntityManagerInterface;
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
    private $fieldsToCheck = ['nom', 'telephone', 'adresse', 'ville', 'codePostal', 'region', 'pays', 'url', 'fax', 'codeEvoliz', 'priveOrPublic', 'compteComptableClient', 'compteComptableFournisseur'];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Fusionner 2 comptes ensemble.
     * 
     * @param Compte $compteA compte à garder
     * @param Compte $compteB compte à supprimer
     * @param Compte $newCompte compte utilisé pour le merge
     * 
     * @return bool
     */
    public function mergeComptes(Compte $compteA, Compte $compteB, Compte $newCompte)
    {
        $result = false;
        // Check params validity
        if(!$this->checkMergeParams($compteA, $compteB, $newCompte)){
            
            return false;
        }
        // Set data if missing
        foreach ($this->fieldsToCheck as $field){
            if(!self::needToChooseField($compteA, $compteB, $field)){
                $getVal = 'get' . ucfirst($field);
                $setVal = 'set' . ucfirst($field);
                if($compteA->$getVal()){
                    $newCompte->$setVal($compteA->$getVal());
                }elseif($compteB->$getVal()){
                    $newCompte->$setVal($compteB->$getVal());
                }
            }
        }
        // Journal de ventes, achats, banque, operations diverses
        if($compteA->getCompteComptableClient() && $compteB->getCompteComptableClient() && $compteA->getCompteComptableClient() !== $compteB->getCompteComptableClient()){
            $compteToCopyFrom = $newCompte->getCompteComptableClient() === $compteA->getCompteComptableClient() ? $compteB->getCompteComptableClient() : $compteA->getCompteComptableClient();
            // Journal de ventes
            foreach ($compteToCopyFrom->getJournalVentes() as $journalVente){
                $journalVente->setCompteCompableClient($newCompte);
            }
            // Journal d'achats
            foreach ($compteToCopyFrom->getJournalAchats() as $journalAchat){
                $journalAchat->setCompteCompableClient($journalAchat);
            }
            // Journal de banques
            foreach ($compteToCopyFrom->getJournalBanque() as $journalBanque){
                $journalBanque->setCompteCompableClient($journalBanque);
            }
            // Opérations diverses
            foreach ($compteToCopyFrom->getOperationsDiverses() as $operationDiverse){
                $operationDiverse->setCompteCompableClient($operationDiverse);
            }
        }
        
        
        dump($newCompte);exit;
        
        return $result;
    }

    /**
     * Return true if data inside $newCompte is OK to be merged
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * @param Compte $newCompte
     * 
     * @return boolean
     */
    private function checkMergeParams(Compte $compteA, Compte $compteB, Compte $newCompte)
    {
        foreach ($this->fieldsToCheck as $field) {
            if (self::needToChooseField($compteA, $compteB, $newCompte)) {
                $method = 'get' . ucfirst($field);
                if(!$newCompte->$method()){
                    
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
