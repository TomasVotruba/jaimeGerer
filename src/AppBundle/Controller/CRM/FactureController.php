<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Swift_Attachment;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Form\FormError;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\CRM\DocumentPrix;
use AppBundle\Entity\CRM\Produit;
use AppBundle\Entity\CRM\PriseContact;
use AppBundle\Entity\Compta\CompteComptable;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;
use AppBundle\Form\CRM\FactureType;

use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style_Fill;

class FactureController extends Controller
{
	const FILTER_DATE_NONE      = 0;
	const FILTER_DATE_MONTH     = 1;
	const FILTER_DATE_2MONTH    = 2;
	const FILTER_DATE_YEAR      = 3;
	const FILTER_DATE_CUSTOM    = -1;


	/**
	 * @Route("/crm/facture/liste", name="crm_facture_liste")
	 * @Route("/crm/factures/devis/liste/{id}", name="crm_factures_devis_liste")
	 */
	public function factureListeAction(DocumentPrix $DevisParent=NULL)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$numSettings = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
		$currentNum = $numSettings->getValeur() - 1;
		$prefixe = date('Y').'-';
		if($currentNum < 10){
			$prefixe.='00';
		} else if ($currentNum < 100){
			$prefixe.='0';
		}
		$lastNum = $prefixe.$currentNum;
		$ajax_url = is_null($DevisParent) ? $this->generateUrl('crm_facture_liste_ajax') : $this->generateUrl('crm_factures_devis_liste_ajax', array('id' => $DevisParent->getId()));
		$titre_page = is_null($DevisParent) ? 'Factures' : 'Factures liées au devis n° : '. $DevisParent->getNum();
		//~ var_dump($ajax_url);exit;
		return $this->render('crm/facture/crm_facture_liste.html.twig', array(
				'lastNum' => $lastNum,
				'ajax_url' =>$ajax_url,
				'titre_page' =>$titre_page
		));
	}

	/**
	 * @Route("/crm/facture/liste/ajax", name="crm_facture_liste_ajax")
	 * @Route("/crm/factures/devis/liste/ajax/{id}", name="crm_factures_devis_liste_ajax")
	 */
	public function factureListeAjaxAction(DocumentPrix $DevisParent=null)
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$arr_search = $requestData->get('search');

		$dateSearch = $requestData->get('date_search', 0);
		$startDate = null;
		$endDate = null;
		switch ($dateSearch) {
			case self::FILTER_DATE_MONTH:
				$startDate = new \DateTime('first day of this month');
				$endDate = new \DateTime('last day of this month');
				break;
			case self::FILTER_DATE_2MONTH:
				$startDate = new \DateTime('first day of previous month');
				$endDate = new \DateTime('last day of this month');
				break;
			case self::FILTER_DATE_YEAR:
				$startDate = new \DateTime('first day of january');
				$endDate = new \DateTime('last day of december');
				break;
			case self::FILTER_DATE_CUSTOM:
				$startDate = $requestData->get('start_date', null);
				$startDate = ($startDate ? new \DateTime($startDate) : null);
				$endDate = $requestData->get('end_date', null);
				$endDate = ($endDate ? new \DateTime($endDate) : null);
				break;
		}
		$dateRange = null;
		if ($startDate) {
			$dateRange = array(
				'start' => $startDate,
				'end'   => $endDate
			);
		}

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				'FACTURE',
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				null,
				$DevisParent,
				null,
				$dateRange

		);

		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$facture = $repository->find($arr_f['id']);
			$totaux = $facture->getTotaux();
			$list[$i]['totaux'] = $totaux;

			if(count($facture->getAvoirs()) != 0){
				$list[$i]['avoir'] = true;
			} else {
				$list[$i]['avoir'] = false;
			}
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany(), 'FACTURE', NULL, $DevisParent),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), 'FACTURE', $arr_search['value'], $DevisParent, NULL,NULL,$dateRange),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/facture", name="crm_facture_datatables")
	 */
	public function factureDatatablesAction(DocumentPrix $facture)
	{
	}

	/**
	 * @Route("/crm/facture/voir/{id}", name="crm_facture_voir")
	 */
	public function factureVoirAction(DocumentPrix $facture)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$numSettings = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
		$currentNum = $numSettings->getValeur() - 1;
		$prefixe = date('Y').'-';
		if($currentNum < 10){
			$prefixe.='00';
		} else if ($currentNum < 100){
			$prefixe.='0';
		}
		$lastNum = $prefixe.$currentNum;

		$priseContactsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\PriseContact');
		$listPriseContacts = $priseContactsRepository->findByDocumentPrix($facture);

		$relancesRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Relance');
		$listRelances = $relancesRepository->findByFacture($facture);

		return $this->render('crm/facture/crm_facture_voir.html.twig', array(
				'facture' => $facture,
				'lastNum' => $lastNum,
				'listPriseContacts' => $listPriseContacts,
				'listRelances' => $listRelances
		));
	}

	/**
	 * @Route("/crm/facture/ajouter", name="crm_facture_ajouter")
	 */
	public function factureAjouterAction()
	{
		$em = $this->getDoctrine()->getManager();
		$facture = new DocumentPrix($this->getUser()->getCompany(),'FACTURE', $em);
		$facture->setUserGestion($this->getUser());
		$form = $this->createForm(
				new FactureType(
						$facture->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId()
				),
				$facture
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$facture->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));
			$facture->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));

			$settingsRepository = $em->getRepository('AppBundle:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();

			if($facture->getDateCreation() == null){
				$facture->setDateCreation(new \DateTime(date('Y-m-d')));
			}
			$factureYear = $facture->getDateCreation()->format('Y');

			if($factureYear != date("Y")){
				//si la facture est antidatée, récupérer le dernier numéro de facture de l'année concernée
				$prefixe = $factureYear.'-';
				$factureRepo = $em->getRepository('AppBundle:CRM\DocumentPrix');
				$lastNum = $factureRepo->findMaxNumForYear('FACTURE', $factureYear, $this->getUser()->getCompany());
				$lastNum = substr($lastNum, 5);
				$lastNum++;
				$facture->setNum($prefixe.$lastNum);
			} else {
				$prefixe = date('Y').'-';
				if($currentNum < 10){
					$prefixe.='00';
				} else if ($currentNum < 100){
					$prefixe.='0';
				}
				$facture->setNum($prefixe.$currentNum);

				//mise à jour du numéro de facture
				$currentNum++;
				$settingsNum->setValeur($currentNum);
				$em->persist($settingsNum);
			}

			$facture->setUserCreation($this->getUser());

			$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));
			if($activationCompta){
				$facture->setCompta(true);
			}
			else{
				$facture->setCompta(false);
			}

			$em->persist($facture);

			//si le compte comptable du client n'existe pas, on le créé
			$compte = $facture->getCompte();
			if($compte->getClient() == false || $compte->getCompteComptableClient() == null){

				$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
				$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

				$em->persist($compteComptable);

				$compte->setClient(true);
				$compte->setCompteComptableClient($compteComptable);
				$em->persist($compte);
			}

			$em->flush();

			if($activationCompta){
				//ecrire dans le journal de vente
				$journalVenteService = $this->container->get('appbundle.compta_journal_ventes_controller');
				$result = $journalVenteService->journalVentesAjouterFactureAction($facture);
			}

			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));

		}

		return $this->render('crm/facture/crm_facture_ajouter.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/facture/editer/{id}", name="crm_facture_editer")
	 */
	public function factureEditerAction(DocumentPrix $facture)
	{
		$_compte = $facture->getCompte();
		$_contact = $facture->getContact();
		$dateCreation = $facture->getDateCreation();
		$facture->setCompte($_compte->getId());
		if($_contact!=null){
			$facture->setContact($_contact->getId());
		}

		$form = $this->createForm(
				new FactureType(
						$facture->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId()
				),
				$facture
		);


		$form->get('compte_name')->setData($_compte->__toString());
		if($_contact!=null){
			$form->get('contact_name')->setData($_contact->__toString());
		}

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$facture->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));
			$facture->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));
			$facture->setDateCreation($dateCreation);
			$facture->setDateEdition(new \DateTime(date('Y-m-d')));
			$facture->setUserEdition($this->getUser());

			if($facture->getEtat() == 'DRAFT'){
				if($facture->getNumBCInterne() != null){
					$facture->setEtat('READY');
				}
			}

			$em->persist($facture);

			$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
			$activationCompta = $settingsActivationRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'outil' => 'COMPTA',
			));

			if($activationCompta){
				//supprimer les lignes du journal de vente
				$journalVentesRepo = $em->getRepository('AppBundle:Compta\JournalVente');
				$arr_lignes = $journalVentesRepo->findByFacture($facture);
				foreach($arr_lignes as $ligne){
					$em->remove($ligne);
				}
				//ecrire dans le journal de vente
				$journalVenteService = $this->container->get('appbundle.compta_journal_ventes_controller');
				$result = $journalVenteService->journalVentesAjouterFactureAction($facture);
			}

			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));
		}

		return $this->render('crm/facture/crm_facture_editer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/facture/supprimer/{id}", name="crm_facture_supprimer")
	 */
	public function factureSupprimerAction(DocumentPrix $facture)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($facture);

			$settingsRepository = $em->getRepository('AppBundle:Settings');
			$numSettings = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
			$numSettings->setValeur($numSettings->getValeur() - 1);
			$em->persist($numSettings);


			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_facture_liste'
			));
		}

		return $this->render('crm/facture/crm_facture_supprimer.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/exporter/{id}", name="crm_facture_exporter")
	 */
	public function factureExporterAction(DocumentPrix $facture)
	{
		//~ if( is_null($facture->getNumBCInterne()) )
		//~ { echo "hich"; }
		//~ var_dump($facture); exit;
		if( is_null($facture->getNumBCInterne()) )
		{
			return $this->redirect($this->generateUrl(
					'crm_facture_voir', array('id' => $facture->getId())
			));
		}
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$footerfacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE', 'company'=>$this->getUser()->getCompany()));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB', 'company'=>$this->getUser()->getCompany()));

		$html = $this->renderView('crm/facture/crm_facture_exporter.html.twig',array(
				'facture' => $facture,
				'footer' => $footerfacture,
				'pub' => $arr_pub,
				'contact_admin' => $contactAdmin->getValeur(),
				'RIB' => $rib
		));

		$nomClient = strtolower(str_ireplace(' ','', $facture->getCompte()->getNom()));
		$filename = $facture->getNum().'.'.$nomClient.'.pdf';

		//$filename = $facture->getNum().'.'.$nomClient.'.pdf';
		return new Response(
				$this->get('knp_snappy.pdf')->getOutputFromHtml($html,
						array(
								'margin-bottom' => '10mm',
								'margin-top' => '10mm',
								'zoom' => 0.8, //prod only, zoom level is not the same on Windows
								'default-header'=>false,
						)
				),
				200,
				array(
						'Content-Type'          => 'application/pdf',
						'Content-Disposition'   => 'attachment; filename='.$filename,
				)
		);
	}

	private function _factureCreatePDF(DocumentPrix $facture)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$footerFacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE', 'company'=>$this->getUser()->getCompany()));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB'));

		$html = $this->renderView('crm/facture/crm_facture_exporter.html.twig',array(
				'facture' => $facture,
				'footer' => $footerFacture,
				'pub' => $arr_pub,
				'contact_admin' => $contactAdmin->getValeur(),
				'RIB' => $rib
		));

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../web/files/crm/'.$this->getUser()->getCompany()->getId().'/facture/';

		$nomClient = strtolower(str_ireplace(' ','', $facture->getCompte()->getNom()));
		$accents = array('á','à','â','ä','ã','å','ç','é','è','ê','ë','í','ì','î','ï','ñ','ó','ò','ô','ö','õ','ú','ù','û','ü','ý','ÿ');
		$sans_accents = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y');
		$nomClient=str_ireplace($accents, $sans_accents, $nomClient);
		$fileName =$pdfFolder.$facture->getNum().'.'.$nomClient.'.pdf';

		$this->get('knp_snappy.pdf')->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	/**
	 * @Route("/crm/facture/envoyer/{id}", name="crm_facture_envoyer")
	 */
	public function factureEnvoyerAction(DocumentPrix $facture)
	{

		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', 'text', array(
				'required' => true,
				'label' => 'Objet',
				'data' => 'Facture : '.$facture->getObjet()
		));

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $this->renderView('crm/facture/crm_facture_email.html.twig', array(
						'facture' => $facture
				)),
				'attr' => array('class' => 'tinymce')
		));

      $form->add('addcc', 'checkbox', array(
      		'required' => false,
			'label' => 'Recevoir une copie de l\'email'
      ));

		$form->add('submit', 'submit', array(
				'label' => 'Envoyer',
				'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$objet = $form->get('objet')->getData();
			$message = $form->get('message')->getData();

			$filename = $this->_factureCreatePDF($facture);

			try{
				$mail = \Swift_Message::newInstance()
				->setSubject($objet)
				->setFrom($this->getUser()->getEmail())
				->setTo($facture->getContact()->getEmail())
				->setBody($message, 'text/html')
				->attach(Swift_Attachment::fromPath($filename))
				;
				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'La facture a bien été envoyée.'
				);
				unlink($filename);

				$priseContact = new PriseContact();
				$priseContact->setType('FACTURE');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi de la facture");
				$priseContact->setDocumentPrix($facture);
				$priseContact->setContact($facture->getContact());
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				if($facture->getEtat() != 'SENT'){
					$facture->setEtat('SENT');
				}

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->persist($facture);
				$em->flush();

			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}


			return $this->redirect($this->generateUrl(
					'crm_facture_voir',
					array('id' => $facture->getId())
			));
		}

		return $this->render('crm/facture/crm_facture_envoyer.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/crm/facture/dupliquer/{id}", name="crm_facture_dupliquer")
	 */
	public function factureDupliquerAction(DocumentPrix $facture)
	{
		if( is_null($facture->getNumBCInterne()) || is_null($facture->getAnalytique()) )
		{
			return $this->redirect($this->generateUrl(
					'crm_facture_voir', array('id' => $facture->getId())
			));
		}
		$em = $this->getDoctrine()->getManager();
		$newFacture = clone $facture;
		$newFacture->setUserCreation($this->getUser());
		$newFacture->setDateCreation(new \DateTime(date('Y-m-d')));
		$newFacture->setObjet('COPIE '.$facture->getObjet());

		$settingsRepository = $em->getRepository('AppBundle:Settings');
		$settingsNum = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'NUMERO_FACTURE', 'company'=>$this->getUser()->getCompany()));
		$currentNum = $settingsNum->getValeur();

		$prefixe = date('Y').'-';
		if($currentNum < 10){
			$prefixe.='00';
		} else if ($currentNum < 100){
			$prefixe.='0';
		}
		$newFacture->setNum($prefixe.$currentNum);

		$currentNum++;
		$settingsNum->setValeur($currentNum);
		$em->persist($settingsNum);

		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$activationCompta = $settingsActivationRepo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'COMPTA',
		));
		if($activationCompta){
			$newFacture->setCompta(true);
		}
		else{
			$newFacture->setCompta(false);
		}

		$em->persist($newFacture);

		//si le compte comptable du client n'existe pas, on le créé
		$compte = $newFacture->getCompte();
		if($compte->getClient() == false){

			$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');
			$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());

			$em->persist($compteComptable);

			$compte->setClient(true);
			$compte->setCompteComptableClient($compteComptable);
			$em->persist($compte);
		}

		if($activationCompta){
			//ecrire dans le journal de vente
			$journalVenteService = $this->container->get('appbundle.compta_journal_ventes_controller');
			$result = $journalVenteService->journalVentesAjouterFactureAction($facture);
		}

		$em->flush();

		return $this->redirect($this->generateUrl(
				'crm_facture_voir',
				array('id' => $newFacture->getId())
		));
	}

	/**
	 * @Route("/crm/facture/export/evoliz", name="crm_facture_export_evoliz")
	 */
	public function factureExportEvolizAction()
	{
		$form = $this->createFormBuilder()->getForm();

		$form->add('num', 'text', array(
				'required' => true,
				'label' => 'Exporter à partir de quel numéro de facture (inclus) ?',
		));

		$form->add('submit', 'submit', array(
				'label' => 'Télécharger',
				'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$num = $form->get('num')->getData();
			$em = $this->getDoctrine()->getManager();
			$repository = $em->getRepository('AppBundle:CRM\DocumentPrix');
			$arr_factures = $repository->findForExportEvoliz($this->getUser()->getCompany(), $num);

			$arr_codesEvolizReq = $em->getRepository('AppBundle:CRM\Compte')->findAllCodesEvoliz();
			$arr_codesEvoliz = array();

			foreach($arr_codesEvolizReq as $arr_codeReq){
				$arr_codesEvoliz[] = $arr_codeReq['codeEvoliz'];
			}

			$path = __DIR__.'/../../../../web/files/crm/facture/';

			/***
			 * Creation du fichier d'import des clients dans Evoliz - on met les nouveaux en haut de la liste
			 ***/
			$fileName = 'template_evoliz_clients.xlsx';
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($path.$fileName);
			$line = 2;
			$arr_contacts = array();
			foreach($arr_factures as $facture){

				$compte = $facture->getCompte();

				if(!in_array($compte->getId(), $arr_contacts)){

					if($compte->getCodeEvoliz() == null){

						$nbChars = 6;

						$newCodeEvoliz = mb_strtoupper(substr($compte->getNom(),0,$nbChars), 'UTF-8');
						$newCodeEvoliz = str_replace(" ", "-", $newCodeEvoliz);

						//replace code if it already exists
						while(in_array($newCodeEvoliz, $arr_codesEvoliz)){
							$nbChars++;
							$newCodeEvoliz = strtoupper(substr($compte->getNom(),0,$nbChars));
							$newCodeEvoliz = str_replace(" ", "-", $newCodeEvoliz);
						}

						$compte->setCodeEvoliz($newCodeEvoliz);
						$em->persist($compte);

						$phpExcelObject->getActiveSheet()
						->getStyle('A'.$line.':V'.$line)
						->applyFromArray(
								array(
										'font' => array(
											'color' => array('rgb' => 'EC741B')
										)
								)
						);


					}

					//code
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('A'.$line, $compte->getCodeEvoliz());
					//nom
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B'.$line, $compte->getNom());
					//adresse
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('K'.$line, $compte->getAdresse());
					//code postal
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('M'.$line, $compte->getCodePostal());
					//ville
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('N'.$line, $compte->getVille());
					//pays
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('O'.$line, $compte->getPays());
					//telephone
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('V'.$line, $compte->getTelephone());

					$arr_contacts[] = $compte->getId();

					$line++;
				}

			}

			$fileNameContacts = '1_evoliz_contacts.xlsx';

			$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
			$writer->save($path.$fileNameContacts);


			/***
			 * Creation du fichier d'import des factures dans Evoliz
			***/
			$fileName = 'template_evoliz_factures.xlsx';
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($path.$fileName);
			$line = 2;
			foreach($arr_factures as $facture){

				$arr_produits = $facture->getProduits();
				foreach($arr_produits as $produit){
					//num facture
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('A'.$line, $facture->getNum());
					//date facture
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('B'.$line, ($facture->getDateCreation()->format('d/m/Y')));
					//code client
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('C'.$line, $facture->getCompte()->getCodeEvoliz());
					//code métier Evoliz
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('D'.$line, ($produit->getType()->getValeur()));
					//conditions de règlement
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('R'.$line, "30 jours");
					//ref
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AA'.$line, ($produit->getType()->getValeur()));
					//designation
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AB'.$line, strip_tags($produit->getDescription()));
					//quantité
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AC'.$line, $produit->getQuantite());
					//tarif
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AE'.$line, $produit->getTarifUnitaire());
					//remise
					$remise = $produit->getRemise();
					if($produit->getRemise() == null){
						$remise = 0;
					}
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AF'.$line, $remise);
					//TVA
					$phpExcelObject->setActiveSheetIndex(0)->setCellValue('AG'.$line, ($facture->getTaxePercent()*100));

					$line++;
				}
			}

			$fileNameFactures = '2_evoliz_factures.xlsx';
			$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
			$writer->save($path.$fileNameFactures);


			/***
			 * Creation du fichier zip contenant les 2 fichiers Excel
			***/
			$zip = new \ZipArchive();
			$zipName = 'export_evoliz.zip';
			$zip->open($path.$zipName,  \ZipArchive::CREATE);

			$content = file_get_contents($path.$fileNameFactures);
			$zip->addFromString(basename($path.$fileNameFactures),$content);

			$content = file_get_contents($path.$fileNameContacts);
			$zip->addFromString(basename($path.$fileNameContacts),$content);

			$zip->close();

			$em->flush();

			return new Response($zipName, 200, array());

		}

		return $this->render('crm/facture/crm_facture_export_evoliz.html.twig', array(
			'form' => $form->createView(),
		));
	}

}
