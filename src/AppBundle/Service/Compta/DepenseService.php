<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Compta\Depense;

class DepenseService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }


}
