<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Service\Compta\CompteComptableService;

class OperationDiverseService extends ContainerAware {

  protected $em;
  protected $compteComptableService;

  public function __construct(\Doctrine\ORM\EntityManager $em, CompteComptableService $compteComptableService)
  {
    $this->em = $em;
    $this->compteComptableService = $compteComptableService;
  }

  public function corrigerAffectationAvecOD($ligneJournal, $compteChoisi){

    //ecriture d'une ligne Opération Diverse au compte choisi
    $od =  new OperationDiverse();
    $od->setDate(new \DateTime(date('Y-m-d')));

    $libelle = "";
    $codeJournal = $ligneJournal->getCodeJournal();
    if($codeJournal == 'VE'){
      if($ligneJournal->getFacture() != null){
        $libelle = 'Facture '.$ligneJournal->getFacture()->getNum();
        $od->setFacture($ligneJournal->getFacture());
      } elseif($ligneJournal->getAvoir() != null){
        $libelle = 'Avoir '.$ligneJournal->getAvoir()->getNum();
        $od->setAvoir($ligneJournal->getAvoir());
      }
    } elseif($codeJournal == 'AC'){
      if($ligneJournal->getDepense() != null){
        $libelle = 'Dépense '.$ligneJournal->getDepense()->getNum();
        $od->setDepense($ligneJournal->getDepense());
      } elseif($ligneJournal->getAvoir() != null){
        $libelle = 'Avoir '.$ligneJournal->getAvoir()->getNum();
        $od->setAvoir($ligneJournal->getAvoir());
      }
    } elseif($codeJournal != 'OD') {
      $libelle = $ligneJournal->getNom();
    }
    $od->setLibelle($libelle.' - correction depuis compte 471');
    $od->setCodeJournal('OD');
    if($ligneJournal->getDebit() != null){
      $od->setCredit(null);
      $od->setDebit($ligneJournal->getDebit());
    } else {
      $od->setDebit(null);
      $od->setCredit($ligneJournal->getCredit());
    }
    $od->setCompteComptable($compteChoisi);
    $this->em->persist($od);


    //ecriture d'une ligne Opération Diverse au compte 471
    $compteAttente =   $this->compteComptableService->getCompteAttente($compteChoisi->getCompany());
    $od = new OperationDiverse();
    $od->setDate(new \DateTime(date('Y-m-d')));
    $od->setLibelle($libelle.' - correction pour compte '.$compteChoisi->getNum());
    $od->setCodeJournal('OD');
    //inverser le debit et le credit
    if($ligneJournal->getDebit() != null){
      $od->setDebit(null);
      $od->setCredit($ligneJournal->getDebit());
    } else {
      $od->setCredit(null);
      $od->setDebit($ligneJournal->getCredit());
    }
    $od->setCompteComptable($compteAttente);
    $this->em->persist($od);

    $this->em->flush();
  }


}
