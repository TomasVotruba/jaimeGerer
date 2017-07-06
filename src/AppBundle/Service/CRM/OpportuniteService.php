<?php

namespace AppBundle\Service\CRM;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\CRM\Opportunite;

class OpportuniteService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function win($opportunite){

    $opportunite->win();
    $this->em->persist($opportunite);
    $this->em->flush();

    return $opportunite;

  }

  public function lose($opportunite){

    $opportunite->lose();
    $this->em->persist($opportunite);
    $this->em->flush();

    return $opportunite;

  }

  public function findOpportunitesSousTraitancesAFacturer($company){
    $repo = $this->em->getRepository('AppBundle:CRM\OpportuniteSousTraitance');
    $arr_all = $repo->findForCompany($company);
    $arr_a_facturer = array();
    foreach($arr_all as $sousTraitance){
      if($sousTraitance->getResteAFacturer() > 0){
        $arr_a_facturer[$sousTraitance->getId()] = $sousTraitance;
      }
    }
    return $arr_a_facturer;
  }

  public function getTauxTransformationData($company, $year){

    $repo = $this->em->getRepository('AppBundle:CRM\Opportunite');
    $list = $repo->getClosedOpportunity($company, $year);

    $won = 0;
    $lost = 0;

    foreach($list as $listItem){
      if($listItem->isWon()){
          $won++;
      } elseif($listItem->isLost()){
        $lost++;
      }
    }

    return array(
        'won' => $won,
        'lost' => $lost,
    );

  }

}
