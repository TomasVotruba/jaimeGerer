<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Compta\CompteComptable;

class CompteComptableService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function createCompteComptable($company, $num, $nom){
    $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
    $cc = $ccRepo->findBy(array(
      'company' => $company,
      'num' => $num
    ));
    if($cc){
      throw new \Exception('Le compte '.$num.' existe déjà.');
    }

    $cc = new CompteComptable();
		$cc->setCompany($company);
    $cc->setNum($num);
    $cc->setNom($nom);

    return $cc;
  }

  public function getCompteAttente($company){
    $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
    $cc = $ccRepo->findOneBy(array(
      'company' => $company,
      'num' => 471
    ));

    if(!$cc){
      throw new \Exception('Le compte d\'attente (471) n\'existe pas.');
    }

    return $cc;
  }


}
