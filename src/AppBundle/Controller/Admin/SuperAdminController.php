<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SuperAdminController extends Controller
{
    /**
     * @Route("/superadmin/stats", name="superadmin_stats")
     */
    public function dashboardAction()
    {

    	$companyRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Company');
    	$userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');
    	$activationOutilRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');

    	$arr_stats_entreprises = array();
    	$arr_stats_entreprises['nbEntreprisesInscrites'] = $companyRepo->count();
    	$arr_stats_entreprises['nbEntreprisesActives'] = $companyRepo->countActives();

    	$arr_stats_users = array();
    	$arr_stats_users['nbUsersInscrits'] = $userRepo->count();
    	$arr_stats_users['nbUsersValides'] = $userRepo->countValides();
    	$arr_stats_users['nbUsersActifs'] = $userRepo->countActifs();

    	$arr_stats_outils = array();
    	$arr_stats_outils['JaimeLeCommercial'] = $activationOutilRepo->countByOutil('CRM');
    	$arr_stats_outils['JaimeLaCompta'] = $activationOutilRepo->countByOutil('COMPTA');
    	$arr_stats_outils['JaimeCommuniquer'] = $activationOutilRepo->countByOutil('EMAILING');
    	$arr_stats_outils['JaimeRecruter'] = $activationOutilRepo->countByOutil('RECRUTEMENT');


    	$arr = $activationOutilRepo->countByNbOutil(1);
    	if($arr){
    		$arr_stats_outils['nbUnOutil'] = count($arr);
    	}else {
    		$arr_stats_outils['nbUnOutil'] = 0;
    	}
    	$arr = $activationOutilRepo->countByNbOutil(2);
    	if($arr){
    		$arr_stats_outils['nbDeuxOutils'] = count($arr);
    	}else {
    		$arr_stats_outils['nbDeuxOutils'] = 0;
    	}
    	$arr = $activationOutilRepo->countByNbOutil(3);
    	if($arr){
    		$arr_stats_outils['nbTroisOutils'] = count($arr);
    	}else {
    		$arr_stats_outils['nbTroisOutils'] = 0;
    	}
    	$arr = $activationOutilRepo->countByNbOutil(4);
    	if($arr){
    		$arr_stats_outils['nbQuatreOutils'] = count($arr);
    	}else {
    		$arr_stats_outils['nbQuatreOutils'] = 0;
    	}

    	return $this->render('superadmin/superadmin_stats.html.twig', array(
    		'arr_stats_entreprises' => $arr_stats_entreprises,
    		'arr_stats_users' => $arr_stats_users,
    		'arr_stats_outils' => $arr_stats_outils
    	));

    }


}
