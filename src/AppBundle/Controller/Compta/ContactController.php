<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\Compta\CompteComptable;
use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\Compta\ContactType;

class ContactController extends Controller
{
	/**
	 * @Route("/compta/contact/{type}", name="compta_contact_liste")
	 */
	public function contactListeAction($type)
	{
		return $this->render('compta/contact/compta_contact_liste.html.twig', array('type' => $type ));
	}

	/**
	 * @Route("/compta/contact/liste/ajax/{type}/{year}",
	 *  name="compta_contact_liste_ajax",
	 *  options={"expose"=true}
	 * )
	 */
	public function contactListeAjaxAction($type, $year)
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');

		$arr_search = $requestData->get('search');

		if($type == "CLIENT"){
			$list = $repository->findForListClient(
					$this->getUser()->getCompany(),
					$requestData->get('length'),
					$requestData->get('start'),
					$arr_cols[$col]['data'],
					$arr_sort[0]['dir'],
					$arr_search['value']
			);

			$factureRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
			for($i=0; $i<count($list); $i++){

				$compteId = ($list[$i]['id']);
				$compte = $repository->find($compteId);
				$arr_factures = $factureRepository->findBy(array('compte' => $compte));

				$year = date("Y");
				$first =  new \DateTime($year.'-01-01');
				$last =  new \DateTime($year.'-12-31');
				$total = 0;
				$total_annee = 0;
				foreach($arr_factures as $facture){
					$totaux = $facture->getTotaux();
					$total+=$totaux['HT'];
					if($facture->getDateCreation() >= $first && $facture->getDateCreation() <= $last){
						$total_annee+=$totaux['HT'];
					}
				}

				$list[$i]['total'] = $total;
				$list[$i]['total_annee'] = $total_annee;

			}

			$response = new JsonResponse();
			$response->setData(array(
					'draw' => intval( $requestData->get('draw') ),
					'recordsTotal' => $repository->count($this->getUser()->getCompany()),
					'recordsFiltered' => $repository->countForListClient($this->getUser()->getCompany(), $arr_search['value']),
					'aaData' => $list,
			));
		} else {
			$list = $repository->findForListFournisseur(
					$this->getUser()->getCompany(),
					$requestData->get('length'),
					$requestData->get('start'),
					$arr_cols[$col]['data'],
					$arr_sort[0]['dir'],
					$arr_search['value'],
					$year
			);

			$depenseRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
			for($i=0; $i<count($list); $i++){

				$compteId = ($list[$i]['id']);
				$compte = $repository->find($compteId);
				$arr_depenses = $depenseRepository->findBy(array('compte' => $compte));

				$year = date("Y");
				$first =  new \DateTime($year.'-01-01');
				$last =  new \DateTime($year.'-12-31');
				$total= 0;
				$total_annee = 0;
				foreach($arr_depenses as $depense){
					$totaux = $depense->getTotaux();
					$total+=$totaux['HT'];
					if($depense->getDate() >= $first && $depense->getDate() <= $last){
						$total_annee+=$totaux['HT'];
					}
				}

				$list[$i]['total'] = $total;
				$list[$i]['total_annee'] = $total_annee;

			}

			$response = new JsonResponse();
			$response->setData(array(
					'draw' => intval( $requestData->get('draw') ),
					'recordsTotal' => $repository->count($this->getUser()->getCompany()),
					'recordsFiltered' => $repository->countForListFournisseur($this->getUser()->getCompany(), $arr_search['value'], $year),
					'aaData' => $list,
			));
		}
		return $response;
	}

	/**
	 * Display an organization as customer or supplier
	 * @param  string $type   customer or supplier (an organization can be both)
	 * @param  Compte $compte organization to display
	 * @return Response       rendered view
	 *
	 * @Route("/compta/contact/voir/{type}/{id}",
	 * 	name="compta_contact_voir",
	 * 	options={"expose"=true}
	 * )
	 */
	public function comptaContactVoirAction($type, Compte $compte)
	{
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$arr_contacts = $contactRepository->findByCompte($compte);

		if($type == "CLIENT"){

			$docPrixRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
			$arr_devis = $docPrixRepository->findBy(array('compte' => $compte, 'type' => 'DEVIS'));
			$arr_factures = $docPrixRepository->findBy(array('compte' => $compte, 'type' => 'FACTURE'));

			$arr_non_payees = $docPrixRepository->findFacturesNonPayees($compte);
			$creances = 0;
			foreach($arr_non_payees as $facture){
				$creances+=$facture->getTotalTTC();
			}

			$ca_annee = 0;
			$lifetime_value = 0;
			$year = date("Y");

			$first =  new \DateTime($year.'-01-01');
			$last =  new \DateTime($year.'-12-31');

			foreach($arr_factures as $facture){
				$lifetime_value+=$facture->getTotalHT();
				if($facture->getDateValidite() >= $first && $facture->getDateValidite() <= $last){
					$ca_annee+=$facture->getTotalHT();
				}
			}

			$arr_retard = $docPrixRepository->findFacturesRetard($compte);
			$arr_retard_temps = array();
			foreach($arr_retard as $facture){

				$today = new \DateTime();
				$retard = $today->diff($facture->getDateValidite());

				$arr_retard_temps[$facture->getId()] = $retard->days;
			}

			return $this->render('compta/contact/compta_contact_client_voir.html.twig', array(
					'compte' => $compte,
					'arr_contacts' => $arr_contacts,
					'arr_devis' => $arr_devis,
					'arr_factures' => $arr_factures,
					'creances' => $creances,
					'ca_annee' => $ca_annee,
					'lifetime_value' => $lifetime_value,
					'arr_factures_retard' => $arr_retard,
					'arr_retard_temps' => $arr_retard_temps,
					'type' => $type
			));
		} else {

			$depenseRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
			$arr_depenses = $depenseRepository->findBy(array('compte' => $compte));

			$depenses_annee= 0;
			$total_depenses = 0;

			$year = date("Y");
			$first =  new \DateTime($year.'-01-01');
			$last =  new \DateTime($year.'-12-31');

			foreach($arr_depenses as $depense){
				$totaux = $depense->getTotaux();
				$total_depenses+=$totaux['HT'];
				if($depense->getDate() >= $first && $depense->getDate() <= $last){
					$depenses_annee+=$totaux['HT'];
				}
			}

			$arr_retard = $depenseRepository->findRetard($compte);


			return $this->render('compta/contact/compta_contact_fournisseur_voir.html.twig', array(
					'compte' => $compte,
					'arr_contacts' => $arr_contacts,
					'arr_depenses' => $arr_depenses,
					'total_depenses' => $total_depenses,
					'depenses_annee' => $depenses_annee,
					'arr_depenses_retard' => $arr_retard,
					'type' => $type

			));
		}

	}

	/**
	 * @Route("/compta/contact/ajouter/{type}", requirements={"type" = "CLIENT|FOURNISSEUR"}, name="compta_contact_ajouter")
	 */
	public function comptaContactAjouterAction($type)
	{
		$em = $this->getDoctrine()->getManager();
		$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		
		$type = strtoupper($type);
		
		$compte = new Compte();
		$compte->setUserGestion($this->getUser());
		$compte->setCompany($this->getUser()->getCompany());
		
		$form = $this->createForm(
			new ContactType(
				$compte->getUserGestion()->getId(),
				$this->getUser()->getCompany()->getId(),
				null,
				$type
			), 
			$compte
		);
		$form->add('addressPicker', 'text', array(
			'label' => 'Veuillez renseigner l\'adresse ici',
			'mapped' => false,
			'required' => false,
		))
		->add('both', 'checkbox', array(
			'required' => false,
			'label' => 'Fournisseur et client à la fois',
			'mapped' => false,
		));
		
		$form->remove('compteComptableFournisseur');
		$form->remove('compteComptableClient');

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			
			$both = $form['both']->getData();
			$compte->setDateCreation(new \DateTime(date('Y-m-d')));
			$compte->setUserCreation($this->getUser());
		

			if($type == 'CLIENT' || $both){
				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

				$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
				$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

				$em->persist($compteComptable);

				$compte->setClient(true);
				$compte->setCompteComptableClient($compteComptable);

			}

			if($type == 'FOURNISSEUR' || $both){

				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

				$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
				$compteComptable = $compteComptableService->createCompteComptableForCompte('401', $compte->getNom());

				$em->persist($compteComptable);

				$compte->setFournisseur(true);
				$compte->setCompteComptableFournisseur($compteComptable);
			}

			$em->persist($compte);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'compta_contact_voir',
				array('id' => $compte->getId(), 'type' => $type)
			));
		}

		return $this->render('compta/contact/compta_contact_ajouter.html.twig', array(
			'form' => $form->createView(),
			'type' => $type,
			'ajout' => true
		));
	}

	/**
	 * @Route("/compta/contact/ajouter-existant/{type}", requirements={"type" = "CLIENT|FOURNISSEUR"}, name="compta_contact_ajouter_existant")
	 */
	public function comptaContactAjouterExistantAction($type)
	{
		$em = $this->getDoctrine()->getManager();
		$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$compteRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		
		$type = strtoupper($type);

		$builder = $this->createFormBuilder();
		$builder->add('compte_name', 'text', array(
			'required' => true,
			'label' => 'Organisation',
			'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off' )
		))
		->add('compte', 'hidden', array(
			'required' => true,
			'attr' => array('class' => 'entity-compte'),
		));
		$form = $builder->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			
			$compteId = $form['compte']->getData();
			$compte = $compteRepo->find($compteId);

			if($type == 'CLIENT'){
				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

				$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
				$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

				$em->persist($compteComptable);

				$compte->setClient(true);
				$compte->setCompteComptableClient($compteComptable);

			} else if($type == 'FOURNISSEUR'){

				$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
				$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());

				$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
				$compteComptable = $compteComptableService->createCompteComptableForCompte('401', $compte->getNom());

				$em->persist($compteComptable);

				$compte->setFournisseur(true);
				$compte->setCompteComptableFournisseur($compteComptable);
			}

			$em->persist($compte);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'compta_contact_voir',
				array('id' => $compte->getId(), 'type' => $type)
			));

		}

		return $this->render('compta/contact/compta_contact_ajouter_existant.html.twig', array(
			'form' => $form->createView(),
			'type' => $type,
		));

	}


	/**
	 * @Route("/compta/contact/editer/{type}/{id}",
	 *   name="compta_contact_editer",
	 *   requirements={"type" = "CLIENT|FOURNISSEUR"},
	 *   options={"expose"=true}
	 * )
	 */
	public function comptaContactEditerAction(Compte $compte, $type)
	{
		$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');

		$form = $this->createForm(
			new ContactType(
				$compte->getUserGestion()->getId(),
				$this->getUser()->getCompany()->getId(),
				null,
				$type
			), 
			$compte
		);
		$form->add('addressPicker', 'text', array(
			'label' => 'Veuillez renseigner l\'adresse ici',
			'mapped' => false,
			'required' => false,
		));
		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$compte->setDateEdition(new \DateTime(date('Y-m-d')));
			$compte->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($compte);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'compta_contact_voir',
				array(
					'id' => $compte->getId(),
					'type' => $type
				)
			));
		}

		$compte401 = null;
		$compte411 = null;
		if($type == "CLIENT"){
			$compte411 = $ccRepo->findCompte411( $this->getUser()->getCompany() );
		} else {
			$compte401 = $ccRepo->findCompte401( $this->getUser()->getCompany() );
		}

		return $this->render('compta/contact/compta_contact_editer.html.twig', array(
			'form' => $form->createView(),
			'type' => $type,
			'compte401' => $compte401,
			'compte411' => $compte411
		));
	}

}
