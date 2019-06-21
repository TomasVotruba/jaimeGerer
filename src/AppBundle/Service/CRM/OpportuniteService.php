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

    public function getDataChartActionsCoAnalytique3Mois($company){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
            'company' => $company,
            'parametre' => 'analytique',
            'module' => 'CRM'
        ));

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');

        $today = new \DateTime(date('Y-m-d'));
        $threeMonthsAgo = new \DateTime(date("Y-m-d", strtotime("-3 months")));

        $arr_opportunite = $opportuniteRepo->findBetweenDates($company, $threeMonthsAgo, $today);

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

    public function getDataChartActionsCoRhoneAlpes3Mois($company, $year){

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
        
        $today = new \DateTime(date('Y-m-d'));
        $threeMonthsAgo = new \DateTime(date("Y-m-d", strtotime("-3 months")));

        $arr_opportunite = $opportuniteRepo->findBetweenDates($company, $threeMonthsAgo, $today);

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

    public function getDataChartTempsCommercialeAnalytique($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
          'company' => $company,
          'parametre' => 'analytique',
          'module' => 'CRM'
        ));

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       

        $arr_temps = array();
        foreach($arr_analytiques as $analytique){
            $arr_temps[$analytique->getValeur()] = 0;

            $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAnalytiqueHavingTempsCommercial($company, $year, $analytique);
            $tempsTotal = 0;
            $nbActions = 0;
            foreach($arr_opportunite as $opportunite){
                $tempsTotal+= $opportunite->getTempsCommercial();
                $nbActions++; 
            }
            if($tempsTotal > 0){
                $arr_temps[$analytique->getValeur()] = $tempsTotal/$nbActions;
            }
        }

        return $arr_temps;
    }

    public function getDataChartTempsCommercialeAO($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       

        $arr_temps = array();
        
        $arr_temps['AO'] = 0;
        $arr_temps['Pas AO'] = 0;

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAOHavingTempsCommercial($company, $year, true);
        $tempsTotal = 0;
        $nbActions = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
            $nbActions++; 
        }
        if($tempsTotal > 0){
            $arr_temps['AO'] = $tempsTotal/$nbActions;
        }

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAOHavingTempsCommercial($company, $year, false);
        $tempsTotal = 0;
        $nbActions = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
            $nbActions++; 
        }
        if($tempsTotal > 0){
            $arr_temps['Pas AO'] = $tempsTotal/$nbActions;
        }
        
        return $arr_temps;
    }

    public function getDataChartTempsCommercialeAnalytiqueRepartition($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $arr_analytiques = $settingsRepo->findBy(array(
            'company' => $company,
            'parametre' => 'analytique',
            'module' => 'CRM'
        ));

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       
        $arr_temps = array();
        foreach($arr_analytiques as $analytique){
            $arr_temps[$analytique->getValeur()] = 0;

            $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAnalytiqueHavingTempsCommercial($company, $year, $analytique);
            $tempsTotal = 0;
            foreach($arr_opportunite as $opportunite){
                $tempsTotal+= $opportunite->getTempsCommercial();
            }
            if($tempsTotal > 0){
                $arr_temps[$analytique->getValeur()] = $tempsTotal;
            }
        }
        return $arr_temps;
    }

    public function getDataChartTempsCommercialeAORepartition($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       

        $arr_temps = array();
        
        $arr_temps['AO'] = 0;
        $arr_temps['Pas AO'] = 0;

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAOHavingTempsCommercial($company, $year, true);
        $tempsTotal = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
        }
        if($tempsTotal > 0){
            $arr_temps['AO'] = $tempsTotal;
        }

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearAndAOHavingTempsCommercial($company, $year, false);
        $tempsTotal = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
        }
        if($tempsTotal > 0){
            $arr_temps['Pas AO'] = $tempsTotal;
        }
        
        return $arr_temps;
    }

    public function getDataChartTempsCommercialePrivePublic($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       

        $arr_temps = array();
        
        $arr_temps['Privé'] = 0;
        $arr_temps['Public'] = 0;

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearPrivePublicHavingTempsCommercial($company, $year, 'PRIVE');
        $tempsTotal = 0;
        $nbActions = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
            $nbActions++; 
        }
        if($tempsTotal > 0){
            $arr_temps['Privé'] = $tempsTotal/$nbActions;
        }

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearPrivePublicHavingTempsCommercial($company, $year, 'PUBLIC');
        $tempsTotal = 0;
        $nbActions = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
            $nbActions++; 
        }
        if($tempsTotal > 0){
            $arr_temps['Public'] = $tempsTotal/$nbActions;
        }
        
        return $arr_temps;
    }

    public function getDataChartTempsCommercialePrivePublicRepartition($company, $year){

        $settingsRepo = $this->em->getRepository('AppBundle:Settings');
        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
       
        $arr_temps = array();
        
        $arr_temps['Privé'] = 0;
        $arr_temps['Public'] = 0;

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearPrivePublicHavingTempsCommercial($company, $year, 'PRIVE');
        $tempsTotal = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
        }
        if($tempsTotal > 0){
            $arr_temps['Privé'] = $tempsTotal;
        }

        $arr_opportunite = $opportuniteRepo->findForCompanyByYearPrivePublicHavingTempsCommercial($company, $year, 'PUBLIC');
        $tempsTotal = 0;
        foreach($arr_opportunite as $opportunite){
            $tempsTotal+= $opportunite->getTempsCommercial();
        }
        if($tempsTotal > 0){
            $arr_temps['Public'] = $tempsTotal;
        }
        
        return $arr_temps;
    }

    public function getDataChartTempsCommercialParMontant($company, $year){

        $opportuniteRepo = $this->em->getRepository('AppBundle:CRM\Opportunite');
        $arr_temps = array();

        $tranches = array(
            'Moins de 1000 €' => array(0, 1000),
            '1001 - 5000 €' =>array(1001, 5000),
            '5001 - 10000 €' =>array(5001, 10000),
            '10001 - 20000 €' =>array(10001, 20000),
            '20001 - 50000 €' =>array(20001, 50000),
            'Plus de 50000 €' =>array(50000, 99999999999999999999),
        );

        foreach($tranches as $label => $tranche){

            $min = $tranche[0];
            $max = $tranche[1];

            $montantMin = $opportuniteRepo->findMinForCompanyByYearAndMontantTempsCommercial($company, $year, $min, $max);
            $montantMax = $opportuniteRepo->findMaxForCompanyByYearAndMontantTempsCommercial($company, $year, $min, $max);

            $arr_temps[] = array($label, $montantMin[1], $montantMin[1], $montantMax[1], $montantMax[1]);
            $arr_temps[] = array($label, 10, 10, 20, 20);

        }

        return $arr_temps;
    }

}
