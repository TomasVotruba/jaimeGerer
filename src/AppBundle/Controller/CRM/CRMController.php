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
		 
		//outil activé : index de la CRM
		$compteRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		$nbComptes = $compteRepository->count($this->getUser()->getCompany());
		
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$nbContacts = $contactRepository->count($this->getUser()->getCompany());
		
		$opportuniteRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
		$nbOpportunites = $opportuniteRepository->count($this->getUser()->getCompany());
		
		$devisRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$nbDevis = $devisRepository->count($this->getUser()->getCompany(), "DEVIS");
		$nbFactures = $devisRepository->count($this->getUser()->getCompany(), "FACTURE");
		
		$rapportRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Rapport');
		$nbRapports = $rapportRepository->count($this->getUser()->getCompany());
		
		return $this->render('crm/crm_index.html.twig', array(
			'nb_comptes' => $nbComptes,
			'nb_contacts' => $nbContacts,
			'nb_opportunites' => $nbOpportunites,
			'nb_devis' => $nbDevis,
			'nb_factures' => $nbFactures,
			'nb_rapports' => $nbRapports,
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
