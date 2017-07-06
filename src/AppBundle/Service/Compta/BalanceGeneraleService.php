<?php

namespace AppBundle\Service\Compta;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;


class BalanceGeneraleService extends ContainerAware {

  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em)
  {
    $this->em = $em;
  }

  public function creerBalanceGenerale($company, $periode){
    $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
     $arr_cc = $ccRepo->findBy(
       array(
         'company' => $company
       ), array(
         'num' => 'ASC'
     ));

     $totalSoldeDebiteur = 0;
     $totalSoldeCrediteur = 0;

     foreach($arr_cc as $cc){
       $totalSoldeDebiteur+=	$cc->getTotalDebit($periode);
       $totalSoldeCrediteur+=	$cc->getTotalCredit($periode);
     }

     return array(
       'arr_cc' => $arr_cc,
       'totalSoldeDebiteur' => $totalSoldeDebiteur,
       'totalSoldeCrediteur' => $totalSoldeCrediteur
     );
  }

}
