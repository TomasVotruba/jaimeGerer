<?php

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class ActivationOutilsService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function isActive($outil, $company){

    $activationSettingsRepo = $this->em->getRepository('AppBundle:SettingsActivationOutil');
    $activation = $activationSettingsRepo->findOneBy(array(
      'company' => $company,
      'outil' => $outil
    ));

    if($activation === null){
      return false;
    }

    return true;

  }



}
