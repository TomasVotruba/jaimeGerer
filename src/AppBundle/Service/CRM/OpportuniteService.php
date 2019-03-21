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

        if($opportunite->getCompte()->getCompany()->isNicomak()){
            $settingsRepo = $this->em->getRepository('AppBundle:Settings');
            $probabiliteGagne = $settingsRepo->findOneBy(array(
                'company' => $opportunite->getUserCreation()->getCompany(),
                'parametre' => 'OPPORTUNITE_STATUT',
                'valeur' => 'Gagné'
            ));

            if($probabiliteGagne){
                $opportunite->setProbabilite($probabiliteGagne);
            }
        }
       
        $this->em->persist($opportunite);
        $this->em->flush();

        return $opportunite;
    }

    public function lose($opportunite){

        $opportunite->lose();
        $this->em->persist($opportunite);
        $this->em->flush();

        if($opportunite->getCompte()->getCompany()->isNicomak()){
            $settingsRepo = $this->em->getRepository('AppBundle:Settings');
            $probabiliteGagne = $settingsRepo->findOneBy(array(
                'company' => $opportunite->getUserCreation()->getCompany(),
                'parametre' => 'OPPORTUNITE_STATUT',
                'valeur' => 'Perdu'
            ));

            if($probabiliteGagne){
                $opportunite->setProbabilite($probabiliteGagne);
            }
        }

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

            $listItemDate = intval($listItem->getDate()->format("Y"));

          if($listItem->isWon() && $listItemDate == intval($year)){
              $won++;
          } elseif($listItem->isLost() && $listItemDate == intval($year)){
            $lost++;
          }
        }

        return array(
            'won' => $won,
            'lost' => $lost,
        );

    }


    public function getDataChartActionsCoAnalytique($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
          'company' => $company,
          'parametre' => 'analytique',
          'module' => 'CRM'
        ));

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
        $arr_opportunite = $opportuniteRepo->findForCompanyByYear($company, $year);

        $arr_total = array();
        foreach($arr_analytiques as $analytique){
            $arr_total[$analytique->getValeur()] = array(
                'public' => 0,
                'prive' => 0
            );
        }
        
        foreach($arr_opportunite as $opportunite){
           
            if($opportunite->isSecteurPublic()){
                $arr_total[$opportunite->getAnalytique()->getValeur()]['public']+= $opportunite->getMontant();
            } else {
                $arr_total[$opportunite->getAnalytique()->getValeur()]['prive']+= $opportunite->getMontant();
            }
        }
        return $arr_total;
    }

    public function getDataChartActionsCoRhoneAlpes($company, $year){

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
        $arr_opportunite = $opportuniteRepo->findForCompanyByYear($company, $year);

        $arr_total = array();
       
        $arr_total['Rhône-Alpes'] = array(
            'public' => 0,
            'prive' => 0
        );
        $arr_total['Hors Rhône-Alpes'] = array(
            'public' => 0,
            'prive' => 0
        );

        
        foreach($arr_opportunite as $opportunite){
           
            if( (substr($opportunite->getCompte()->getCodePostal(),0,2) === '73') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '38') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '74') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '69') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '01') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '26') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '07')
            ){
                if($opportunite->isSecteurPublic()){
                    $arr_total['Rhône-Alpes']['public']+= $opportunite->getMontant();
                } else {
                    $arr_total['Rhône-Alpes']['prive']+= $opportunite->getMontant();
                }
            } else {
                if($opportunite->isSecteurPublic()){
                    $arr_total['Hors Rhône-Alpes']['public']+= $opportunite->getMontant();
                } else {
                    $arr_total['Hors Rhône-Alpes']['prive']+= $opportunite->getMontant();
                }
            }
        }
        return $arr_total;
    }

    public function getDataChartCAAnalytique($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
          'company' => $company,
          'parametre' => 'analytique',
          'module' => 'CRM'
        ));

        $opportuniteRepartitionRepo = $this->em->getRepository('AppBundle:CRM\OpportuniteRepartition');
        $arr_opportuniteRepartitions = $opportuniteRepartitionRepo->findForCompanyByYear($company, $year);

        $arr_total = array();
        foreach($arr_analytiques as $analytique){
            $arr_total[$analytique->getValeur()] = array(
                'public' => 0,
                'prive' => 0
            );
        }
        
        foreach($arr_opportuniteRepartitions as $opportuniteRepartition){
           
            if($opportuniteRepartition->getOpportunite()->isSecteurPublic()){
                $arr_total[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur()]['public']+= $opportuniteRepartition->getMontantMonetaire();
            } else {
                $arr_total[$opportuniteRepartition->getOpportunite()->getAnalytique()->getValeur()]['prive']+= $opportuniteRepartition->getMontantMonetaire();
            }
        }
        return $arr_total;
    }

    public function getDataChartCARhoneAlpes($company, $year){

        $opportuniteRepartitionRepo = $this->em->getRepository('AppBundle:CRM\OpportuniteRepartition');
        $arr_opportuniteRepartitions = $opportuniteRepartitionRepo->findForCompanyByYear($company, $year);


        $arr_total = array();
       
        $arr_total['Rhône-Alpes'] = array(
            'public' => 0,
            'prive' => 0
        );
        $arr_total['Hors Rhône-Alpes'] = array(
            'public' => 0,
            'prive' => 0
        );

        
        foreach($arr_opportuniteRepartitions as $opportuniteRepartition){

            $opportunite = $opportuniteRepartition->getOpportunite();
           
            if( (substr($opportunite->getCompte()->getCodePostal(),0,2) === '73') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '38') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '74') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '69') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '01') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '26') ||
                (substr($opportunite->getCompte()->getCodePostal(),0,2) === '07')
            ){
                if($opportunite->isSecteurPublic()){
                    $arr_total['Rhône-Alpes']['public']+= $opportuniteRepartition->getMontantMonetaire();
                } else {
                    $arr_total['Rhône-Alpes']['prive']+= $opportuniteRepartition->getMontantMonetaire();
                }
            } else {
                if($opportunite->isSecteurPublic()){
                    $arr_total['Hors Rhône-Alpes']['public']+= $opportuniteRepartition->getMontantMonetaire();
                } else {
                    $arr_total['Hors Rhône-Alpes']['prive']+= $opportuniteRepartition->getMontantMonetaire();
                }
            }
        }
        return $arr_total;
    }

}
