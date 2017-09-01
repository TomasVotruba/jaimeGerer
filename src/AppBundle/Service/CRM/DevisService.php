<?php

namespace AppBundle\Service\CRM;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\CRM\Devis;

class DevisService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function win($devis){

    $devis->win();
    $this->em->persist($devis);
    $this->em->flush();

    return $devis;

  }

  public function lose($devis){

    $devis->lose();
    $this->em->persist($devis);
    $this->em->flush();

    return $devis;

  }


}
