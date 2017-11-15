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

    public function creerBalanceGenerale($company, $periode, $equilibre){
        $ccRepo = $this->em->getRepository('AppBundle:Compta\CompteComptable');
        $arr_all_cc = $ccRepo->findBy(
            array(
                'company' => $company
            ), array(
                'num' => 'ASC'
        ));

        $totalSoldeDebiteur = 0;
        $totalSoldeCrediteur = 0;
        $arr_cc = array();

        foreach($arr_all_cc as $cc){
            $ok = false;
            if($equilibre == 'ALL'){
                $ok = true;
            } else if($equilibre == 'EQUILIBRE' && ($cc->getSoldeDebiteur($periode) == 0 && $cc->getSoldeCrediteur($periode) == 0)){
                $ok = true;
            } else if($equilibre == 'DESEQUILIBRE' && ($cc->getSoldeDebiteur($periode) != 0 || $cc->getSoldeCrediteur($periode) != 0)){
                $ok = true;
            }

            if($ok){
                $arr_cc[] = $cc;
                $totalSoldeDebiteur+= $cc->getTotalDebit($periode);
                $totalSoldeCrediteur+= $cc->getTotalCredit($periode);
            }
        }

        $soldeDebiteur = 0;
        $soldeCrediteur = 0;
        if($totalSoldeDebiteur > $totalSoldeCrediteur){
            $soldeDebiteur = $totalSoldeDebiteur - $totalSoldeCrediteur;
        } else {
            $soldeCrediteur = $totalSoldeCrediteur - $totalSoldeDebiteur;
        }

        return array(
           'arr_cc' => $arr_cc,
           'totalSoldeDebiteur' => $totalSoldeDebiteur,
           'totalSoldeCrediteur' => $totalSoldeCrediteur,
           'soldeDebiteur' => $soldeDebiteur,
           'soldeCrediteur' => $soldeCrediteur,
        );
    }

}
