<?php

namespace AppBundle\Service\TimeTracker;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class TimeTrackerService extends ContainerAware {

    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function getDataChartTempsParMois($company, $year){

        $tempsRepo = $this->em->getRepository('AppBundle:TimeTracker\Temps');
       
        $arr_data = array();

        for($i = 1; $i<=12; $i++){
            $arr_data[$i] = 0;
        }

        $arr_temps = $tempsRepo->findForCompanyByYear($company, $year);
       
        foreach($arr_temps as $temps){
            $arr_data[$temps->getDate()->format('n')]+= $temps->getDuree();
        }
        
        return $arr_data;
    }

    public function getDataChartTempsParAnnee($company){

        $activationRepo = $this->em->getRepository('AppBundle:SettingsActivationOutil');
        $activation = $activationRepo->findOneBy(array(
            'company' => $company,
            'outil' => 'CRM'
        ));
        $yearActivation = $activation->getDate()->format('Y');
        $currentYear = date('Y');

        $tempsRepo = $this->em->getRepository('AppBundle:TimeTracker\Temps');
       
        $arr_data = array();

        for($i = $yearActivation; $i<=$currentYear; $i++){
            $arr_data[$i] = 0;
            $arr_temps = $tempsRepo->findForCompanyByYear($company, $i);
            foreach($arr_temps as $temps){
                $arr_data[$i]+= $temps->getDuree();
            }
        }
        
        return $arr_data;
    }

}
