<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class NumService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function getNextNum($type, $company){

    $settingsRepository = $this->em->getRepository('AppBundle:Settings');

    switch($type){
      case 'DEPENSE':

        //find num
        $settingsNum = $settingsRepository->findOneBy(array(
          'module' => 'COMPTA',
          'parametre' => 'NUMERO_DEPENSE',
          'company'=> $company
        ));

        //initialize num if it doens't exist
        if( count($settingsNum) == 0) {
          $settingsNum = new Settings();
          $settingsNum->setModule('COMPTA');
          $settingsNum->setParametre('NUMERO_DEPENSE');
          $settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
          $settingsNum->setTitre('Numéro de dépense');
          $settingsNum->setType('NUM');
          $settingsNum->setNoTVA(false);
          $settingsNum->setCategorie('DEPENSE');
          $settingsNum->setCompany($company);
          $settingsNum->setValeur(1);
        }

        //find prefix
        $prefixe = 'D-'.date('Y').'-';
        break;
    }

    $currentNum = $settingsNum->getValeur();


    if($currentNum < 10){
      $prefixe.='00';
    } else if ($currentNum < 100){
      $prefixe.='0';
    }

    $arr_num = array(
      'prefixe' => $prefixe,
      'num' => $currentNum
    );

    return $arr_num;
  }


  public function getDepenseNum($company, $depense){

    $settingsRepository = $this->em->getRepository('AppBundle:Settings');

    $settingsNum = $settingsRepository->findOneBy(array(
      'module' => 'COMPTA',
      'parametre' => 'NUMERO_DEPENSE',
      'company'=> $company
    ));

    if( count($settingsNum) == 0) {
      $settingsNum = new Settings();
      $settingsNum->setModule('COMPTA');
      $settingsNum->setParametre('NUMERO_DEPENSE');
      $settingsNum->setHelpText('Le numéro de dépense courant - ne pas modifier si vous n\'êtes pas sûr de ce que vous faites !');
      $settingsNum->setTitre('Numéro de dépense');
      $settingsNum->setType('NUM');
      $settingsNum->setNoTVA(false);
      $settingsNum->setCategorie('DEPENSE');
      $settingsNum->setCompany($company);
      $currentNum = 1;
    } else {
      $currentNum = $settingsNum->getValeur();
    }

  	$depenseYear = $depense->getDate()->format('Y');
    if($depenseYear != date("Y")){

      //si la dépense est antidatée, récupérer le dernier numéro de dépense de l'année concernée
      $prefixe = 'D-'.$depenseYear.'-';
      $depenseRepository = $this->em->getRepository('AppBundle:Compta\Depense');
      $lastNum = $depenseRepository->findMaxNumForYear('DEPENSE', $depenseYear, $company);
      $lastNum = substr($lastNum, 7);
      $lastNum++;

      $num = $lastNum;

    } else {

      $prefixe = 'D-'.date('Y').'-';
      if($currentNum < 10){
        $prefixe.='00';
      } else if ($currentNum < 100){
        $prefixe.='0';
      }

      $num = $currentNum;
    }

    $arr_num = array(
      'prefixe' => $prefixe,
      'num' => $num
    );

    return $arr_num;
  }

  public function updateDepenseNum($company, $num){

    $settingsRepository = $this->em->getRepository('AppBundle:Settings');
    $settingsNum = $settingsRepository->findOneBy(array(
      'module' => 'COMPTA',
      'parametre' => 'NUMERO_DEPENSE',
      'company'=> $company
    ));

    //mise à jour du numéro de depense
    $settingsNum->setValeur($num);
    $this->em->persist($settingsNum);
    $this->em->flush();

    return new Response();

  }



}
