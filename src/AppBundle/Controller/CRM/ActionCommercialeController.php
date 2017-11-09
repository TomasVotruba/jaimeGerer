<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use AppBundle\Entity\CRM\Opportunite;
use AppBundle\Entity\CRM\OpportuniteSousTraitance;
use AppBundle\Entity\CRM\OpportuniteRepartition;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\CRM\DocumentPrix;
use AppBundle\Entity\Settings;
use AppBundle\Entity\Rapport;

use AppBundle\Form\CRM\OpportuniteType;
use AppBundle\Form\CRM\OpportuniteDevisType;
use AppBundle\Form\CRM\OpportuniteRepartitionType;
use AppBundle\Form\CRM\OpportuniteWonRepartitionType;
use AppBundle\Form\CRM\OpportuniteSousTraitanceType;
use AppBundle\Form\CRM\OpportuniteFilterType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;

use \DateTime;


class ActionCommercialeController extends Controller
{

	/**
	 * @Route("/crm/action-commerciale/ajouter",
	 *   name="crm_action_commerciale_ajouter",
	 *  )
	 */
	public function actionCommercialeAjouterAction()
	{
		$em = $this->getDoctrine()->getManager();
		$devisService = $this->get('appbundle.crm_devis_service');

		$opportunite = new Opportunite();
		$devis = new DocumentPrix($this->getUser()->getCompany(),'DEVIS', $em);

		$opportunite->setUserGestion($this->getUser());
		$form = $this->createForm(
			new OpportuniteDevisType(
					$opportunite->getUserGestion()->getId(),
					$this->getUser()->getCompany()->getId(),
					$this->getRequest()
			),
			$opportunite
		);

		$form->get('dateValidite')->setData($devis->getDateValidite());
		$form->get('date')->setData(new \DateTime(date('Y-m-d')));
		$form->get('cgv')->setData($devis->getCgv());

		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$opportunite->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));
			$opportunite->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));
			$opportunite->setDateCreation(new \DateTime(date('Y-m-d')));
			$opportunite->setUserCreation($this->getUser());
			$opportunite->setMontant($form->get('totalHT')->getData());

			$opportunite->setDevis($devis);

			$em->persist($opportunite);

			$devis = $devisService->createFromOpportunite($devis, $opportunite);

			$devis = $devisService->setNum($devis);
			$devis->setDateValidite($form->get('dateValidite')->getData());
			$devis->setAdresse($form->get('adresse')->getData());
			$devis->setVille($form->get('ville')->getData());
			$devis->setCodePostal($form->get('codePostal')->getData());
			$devis->setRegion($form->get('region')->getData());
			$devis->setPays($form->get('pays')->getData());
			$devis->setDescription($form->get('description')->getData());
			$devis->setCGV($form->get('cgv')->getData());
			$devis->setRemise($form->get('remise')->getData());
			$devis->setTaxe($form->get('taxe')->getData());
			$devis->setTaxePercent($form->get('taxePercent')->getData());

			foreach($form->get('produits')->getData() as $produit){
				$devis->addProduit($produit);
			}
			
			$em->persist($devis);
			$em->flush();
			
			return $this->redirect($this->generateUrl(
					'crm_opportunite_voir',
					array('id' => $opportunite->getId())
			));
		}

		return $this->render('crm/action-commerciale/crm_action_commerciale_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/action-commerciale/voir/{id}",
	 *   name="crm_action_commerciale_voir",
	 *  )
	 */
	public function actionCommercialeVoirAction(Opportunite $actionCommerciale)
	{

		return $this->render('crm/action-commerciale/crm_action_commerciale_voir.html.twig', array(
			'opportunite' => $actionCommerciale
		));

	}	
}