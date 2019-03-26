<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\SettingsActivationOutil;

class CRMController extends Controller
{
	/**
	 * @Route("/crm", name="crm_index")
	 */
	public function indexAction()
	{
		//vérifier si l'outil CRM a été activé
		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$settingsActivationCRM = $settingsActivationRepo->findBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'CRM'
		));
		 
		//outil non activé : paramétrage
		if($settingsActivationCRM == null){
			return $this->redirect($this->generateUrl('crm_activer_start'));
		}

		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$nbContacts = $contactRepository->count($this->getUser()->getCompany());
		$nbNoEmail = $contactRepository->countNoEmail($this->getUser()->getCompany());
		$nbNoTel = $contactRepository->countNoTel($this->getUser()->getCompany());
		$nbBounce = $contactRepository->countBounce($this->getUser()->getCompany());

		$todoList = array(
			'today' => array(),
			'week' => array(),
			'late' => array(),
		);
		$planPaiementRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\PlanPaiement');
		$arr_planPaiementToday = $planPaiementRepository->findToday($this->getUser());
		$arr_planPaiementLate = $planPaiementRepository->findLate($this->getUser());

		$todoList['today'] = $arr_planPaiementToday;
		$todoList['late'] = $arr_planPaiementLate;

		return $this->render('crm/crm_index.html.twig', array(
			'nbContacts' => $nbContacts,
			'nbNoEmail' => $nbNoEmail,
			'nbNoTel' => $nbNoTel,
			'nbBounce' => $nbBounce,
			'todoList' => $todoList
		));
	}

	/**
	 * @Route("/crm/activation/start", name="crm_activer_start")
	 */
	public function activationStartAction(){
		return $this->render('crm/activation/crm_activation_start.html.twig');
	}
	
	/**
	 * @Route("/crm/activation/import", name="crm_activer_import")
	 */
	public function activationImportAction(){
		return $this->render('crm/activation/crm_activation_import.html.twig');
	}
	
	/**
	 * @Route("/crm/activation", name="crm_activer")
	 */
	public function activationAction(){
		
		//activer la CRM
		$em = $this->getDoctrine()->getManager();
		$activationCRM = new SettingsActivationOutil();
		$activationCRM->setCompany($this->getUser()->getCompany());
		$activationCRM->setDate(new \DateTime(date('Y-m-d')));
		$activationCRM->setOutil('CRM');
		$em->persist($activationCRM);
		$em->flush();
		
		
		return $this->render('crm/activation/crm_activation.html.twig');
	}
	
}
