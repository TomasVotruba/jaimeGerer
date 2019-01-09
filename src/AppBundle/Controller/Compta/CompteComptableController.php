<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

use AppBundle\Entity\Compta\CompteComptable;
use AppBundle\Entity\Compta\AffectationDiverse;
use AppBundle\Entity\CRM\Compte;

use AppBundle\Form\Compta\CompteComptableType;
use AppBundle\Form\Compta\LigneJournalCorrectionType;


use PHPExcel_IOFactory;

class CompteComptableController extends Controller
{
	/**
	 * @Route("/compta/plan/liste", name="compta_compte_liste")
	 */
	public function compteListeAction()
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$form = $this->createFormBuilder()->getForm();

		$form->add('recherche', 'text', array(
			'label' => 'Rechercher',
			'required' => true,
		));

		$form->add('submit', 'submit', array(
			'label' => 'Rechercher',
			'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);
		$dataCount = array();
		$dataBigCount = array();
		if ($form->isSubmitted() && $form->isValid() ) {
			$data = $form->getData();
			$arr_comptes = $repo->SearchByName($data['recherche'],$this->getUser()->getCompany());
			foreach( $arr_comptes as $compte )
			{
				if( isset($dataBigCount[substr($compte->getNum(),0,1)]) )
					$dataBigCount[substr($compte->getNum(),0,1)]++;
				else
					$dataBigCount[substr($compte->getNum(),0,1)] = 1;
				if ( isset($dataCount[substr($compte->getNum(),0,2)]) )
					$dataCount[substr($compte->getNum(),0,2)]++;
				else
					$dataCount[substr($compte->getNum(),0,2)] = 1 ;
			}
		}
		else
			$arr_comptes = $repo->findBy(array(
					'company' => $this->getUser()->getCompany()
				),
			array('num' => 'ASC'));

		$arr_categories = array(
			1 => "Comptes de capitaux",
			2 => "Comptes d'immobilisations",
			3 => "Comptes de stocks et en-cours",
			4 => "Comptes de tiers",
			5 => "Comptes financiers",
			6 => "Comptes de charges",
			7 => "Comptes de produits",
			8 => "Comptes spéciaux",
			10 => "Capital et réserves",
			11 => "Report à nouveau",
			12 => "Résultat de l'exercice",
			13 => "Subventions d'investissement",
			14 => "Provisions réglementées",
			15 => "Provisions pour risques et charges",
			16 => "Emprunts et dettes assimilées",
			17 => "Dettes rattachées à des participations",
			18 => "Comptes de liaison des établissements et sociétés en participation",
			19 => "",
			20 => "Immobilisations incorporelles",
			21 => "Immobilisations corporelles",
			22 => "Immobilisations mises en concession",
			23 => "Immobilisations en cours",
			24 => "",
			25 => "Parts dans des entreprises liées et créances sur des entreprises liées ",
			26 => "Participations et créances rattachées à des participations ",
			27 => "Autres immobilisations financières ",
			28 => "Amortissements des immobilisations",
			29 => "Dépréciations des immobilisations ",
			30 => "",
			31 => "Matières premières (et fournitures) ",
			32 => "Autres approvisionnements",
			33 => "En-cours de production de biens ",
			34 => "En-cours de production de services ",
			35 => "Stocks de produits",
			36 => "",
			37 => "Stocks de marchandises",
			38 => "",
			39 => "Provisions pour dépréciation des stocks et en-cours ",
			40 => "Fournisseurs comptes et rattachés",
			41 => "Clients et comptes rattachés",
			42 => "Personnel et comptes rattachés",
			43 => "Sécurité sociale et autres organismes sociaux ",
			44 => "État et autres collectivités publiques ",
			45 => "Groupe et associés ",
			46 => "Débiteurs divers et créditeurs divers ",
			47 => "Comptes transitoires ou d'attente ",
			48 => "Comptes de régularisation",
			49 => "Provisions pour dépréciation des comptes de tiers",
			50 => "Valeurs mobilières de placement",
			51 => "Banques, établissements financiers et assimilés",
			52 => "Instruments de trésorerie",
			53 => "Caisse",
			54 => "Régies d'avance et accréditifs",
			55 => "",
			56 => "",
			57 => "",
			58 => "Virements internes",
			59 => "Provisions pour dépréciation des comptes financiers ",
			60 => "Achats",
			61 => "Services extérieurs",
			62 => "Autres services extérieurs ",
			63 => "Impôts, taxes et versements assimilés",
			64 => "Charges de personnel",
			65 => "Autres charges de gestion courante ",
			66 => "Charges financières",
			67 => "Charges exceptionnelles",
			68 => "Dotations aux amortissements et aux provisions ",
			69 => "Participation des salariés - impôts sur les bénéfices et assimilés ",
			70 => "Ventes de produits fabriqués, prestations de services, marchandises",
			71 => "Production stockée (ou déstockage)",
			72 => "Production immobilisée",
			73 => "",
			74 => "Subventions d'exploitation",
			75 => "Autres produits de gestion courante ",
			76 => "Produits financiers",
			77 => "Produits exceptionnels",
			78 => "Reprises sur amortissements et provisions",
			79 => "Transferts de charges",
			80 => "Comptes spéciaux"
 		);

		return $this->render('compta/plan/compta_plan_liste.html.twig', array(
				'arr_comptes' => $arr_comptes,
				'arr_categories' => $arr_categories,
				'form' =>$form->createView(),
				'dataBigCount' => $dataBigCount,
				'dataCount' => $dataCount
		));
	}

	/**
	 * @Route("/compta/plan/rechercher/{search}", name="compta_compte_rechercher")
	 */
	public function compteRechercherAction($search)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$arr_comptes = $repo->SearchByName($search,$this->getUser()->getCompany());
			
		$arr_categories = array(
			1 => "Comptes de capitaux",
			2 => "Comptes d'immobilisations",
			3 => "Comptes de stocks et en-cours",
			4 => "Comptes de tiers",
			5 => "Comptes financiers",
			6 => "Comptes de charges",
			7 => "Comptes de produits",
			8 => "Comptes spéciaux",
			10 => "Capital et réserves",
			11 => "Report à nouveau",
			12 => "Résultat de l'exercice",
			13 => "Subventions d'investissement",
			14 => "Provisions réglementées",
			15 => "Provisions pour risques et charges",
			16 => "Emprunts et dettes assimilées",
			17 => "Dettes rattachées à des participations",
			18 => "Comptes de liaison des établissements et sociétés en participation",
			19 => "",
			20 => "Immobilisations incorporelles",
			21 => "Immobilisations corporelles",
			22 => "Immobilisations mises en concession",
			23 => "Immobilisations en cours",
			24 => "",
			25 => "Parts dans des entreprises liées et créances sur des entreprises liées ",
			26 => "Participations et créances rattachées à des participations ",
			27 => "Autres immobilisations financières ",
			28 => "Amortissements des immobilisations",
			29 => "Dépréciations des immobilisations ",
			30 => "",
			31 => "Matières premières (et fournitures) ",
			32 => "Autres approvisionnements",
			33 => "En-cours de production de biens ",
			34 => "En-cours de production de services ",
			35 => "Stocks de produits",
			36 => "",
			37 => "Stocks de marchandises",
			38 => "",
			39 => "Provisions pour dépréciation des stocks et en-cours ",
			40 => "Fournisseurs comptes et rattachés",
			41 => "Clients et comptes rattachés",
			42 => "Personnel et comptes rattachés",
			43 => "Sécurité sociale et autres organismes sociaux ",
			44 => "État et autres collectivités publiques ",
			45 => "Groupe et associés ",
			46 => "Débiteurs divers et créditeurs divers ",
			47 => "Comptes transitoires ou d'attente ",
			48 => "Comptes de régularisation",
			49 => "Provisions pour dépréciation des comptes de tiers",
			50 => "Valeurs mobilières de placement",
			51 => "Banques, établissements financiers et assimilés",
			52 => "Instruments de trésorerie",
			53 => "Caisse",
			54 => "Régies d'avance et accréditifs",
			55 => "",
			56 => "",
			57 => "",
			58 => "Virements internes",
			59 => "Provisions pour dépréciation des comptes financiers ",
			60 => "Achats",
			61 => "Services extérieurs",
			62 => "Autres services extérieurs ",
			63 => "Impôts, taxes et versements assimilés",
			64 => "Charges de personnel",
			65 => "Autres charges de gestion courante ",
			66 => "Charges financières",
			67 => "Charges exceptionnelles",
			68 => "Dotations aux amortissements et aux provisions ",
			69 => "Participation des salariés - impôts sur les bénéfices et assimilés ",
			70 => "Ventes de produits fabriqués, prestations de services, marchandises",
			71 => "Production stockée (ou déstockage)",
			72 => "Production immobilisée",
			73 => "",
			74 => "Subventions d'exploitation",
			75 => "Autres produits de gestion courante ",
			76 => "Produits financiers",
			77 => "Produits exceptionnels",
			78 => "Reprises sur amortissements et provisions",
			79 => "Transferts de charges",
			80 => "Comptes spéciaux"
 		);

		return $this->render('compta/plan/compta_plan_recherche_resultats.html.twig', array(
			'arr_comptes' => $arr_comptes,
			'arr_categories' => $arr_categories,
			'search' => $search
		));
	}

	/**
	 * @Route("/compta/plan/utiliser-general/{initialisation}", name="compta_compte_utiliser_general")
	 */
	public function compteUtiliserGeneralAction($initialisation = false)
	{
		$em = $this->getDoctrine()->getManager();

		//creation des comptes courants pour l'entreprise à partir des comptes du plan comptable général
		$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
		$arr_comptes = $ccRepo->findByCompany(NULL); //les comptes comptables du plan comptable général ont company=NULL
		foreach($arr_comptes as $compte){
			$newCompte = clone $compte;
			$newCompte->setCompany($this->getUser()->getCompany());
			$em->persist($newCompte);
		}

		$em->flush();

		if($initialisation){
			return $this->redirect($this->generateUrl('compta_activer_produits', array('prev' => 3)));
		}

		return $this->redirect($this->generateUrl('compta_compte_liste'));
	}


	/**
	 * @Route("compta/compte/importer/upload", name="compta_compte_importer_upload")
	 */
	public function compteImporterUploadAction()
	{
		$requestData = $this->getRequest();

		$arr_files = $requestData->files->all();
		$file = $arr_files["files"][0];

		//enregistrement temporaire du fichier uploadé
		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/plan_comptable';
		$file->move($path, $filename);

		//enregistrement en session du chemin vers le fichier temporaire
		$session = $requestData->getSession();
		$session->set('import_comptes_comptables_file', $path.'/'.$filename);

		$response = new JsonResponse();
		$response->setData(array(
				'filename' => $filename
		));

		return $response;
	}

	/**
	 * @Route("compta/compte/importer/mapping/{initialisation}", name="compta_compte_importer_mapping_clients_fournisseurs")
	 */
	public function compteImporterMappingClientsFournisseursAction($initialisation){

		$em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();
		$session = $request->getSession();

		//recuperation et ouverture du fichier temporaire uploadé
		$filepath = $session->get('import_comptes_comptables_file');

		//lecture du fichier Excel
		$fileType = PHPExcel_IOFactory::identify($filepath);
		$readerObject = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $readerObject->load($filepath);
		//fichier dans un array associatif
		$arr_data = $objPHPExcel->getActiveSheet()->toArray(null,true,false,true);

		//recherche des numéro de comptes comptables existants
		$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
		$arr_cc = $ccRepo->findByCompany($this->getUser()->getCompany());
		$arr_num = array();
		foreach($arr_cc as $compte){
			$arr_num[] = $compte->getNum();
		}

		//recherche des comptes clients (411) et fournisseurs (401) dans le fichier à importer
		$arr_clients = array();
		$arr_fournisseurs = array();
		foreach($arr_data as $data){
 			if(!in_array($data['A'], $arr_num)){
				if($this->startsWith($data['A'], '411') && !$this->endsWith($data['A'], '0000' ) && $data['A'] != '411'){
					$arr_clients[$data['B']] = $data['A'];
				}

				 if($this->startsWith($data['A'], '401') && !$this->endsWith($data['A'], '0000') && $data['A'] != '401'){
					$arr_fournisseurs[$data['B']] = $data['A'];
				}
			}
		}

		//Récupération des comptes et contacts de la CRM
		$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
		$arr_all_comptes = $compteRepo->findByCompany($this->getUser()->getCompany());
		$arr_comptes = array();
		foreach($arr_all_comptes as $compte){
			$arr_comptes[$compte->getId()] = $compte->getNom();
		}

		$contactRepo = $em->getRepository('AppBundle:CRM\Contact');
		$arr_all_contacts = $contactRepo->findByCompany($this->getUser()->getCompany());
		$arr_contacts = array();
		foreach($arr_all_contacts as $contact){
			$arr_contacts[$contact->getId()] = $contact->getNom();
		}

		//création du formulaire de mapping
		$formBuilder = $this->createFormBuilder();

		foreach($arr_clients as $key => $compteClient){
			//chercher s'il existe un client au nom similaire dans la CRM
			$dataName = null;
			$dataCompte = null;
			$nomCompteComptable = substr($compteClient,3); //

			$found = false;
			foreach($arr_all_comptes as $compte){
				$nomCompteClient = strtoupper($compte->getNom());
				if($this->startsWith($nomCompteClient, $nomCompteComptable)){
					$dataName = $compte->getNom();
					$dataCompte = $compte->getId();
					$found = true;
					break;
				}
			}

			//chercher dans les contacts s'il n'y a pas de correspondance dans les comptes
			if(!$found){
				foreach($arr_all_contacts as $contact){
					$nomCompteClient = strtoupper($contact->getNom());
					if($this->startsWith($nomCompteClient, $nomCompteComptable)){
						$dataName = $contact->getCompte()->getNom().' ('.$contact.')';
						$dataCompte = $contact->getCompte()->getId();
						break;
					}
				}
			}

			$selectedOption = 'new';
			if($dataName){
				$selectedOption = 'existing';
			}
			//ajout des input autocomplete au formulaire de mapping
			$formBuilder->add($compteClient.'-radio', 'choice', array(
					'required' => true,
					'mapped' => false,
					'expanded' => true,
					'choices' => array(
						'existing' => 'Utiliser un client existant dans la CRM',
						'new' => 'Créer un nouveau client dans la CRM',
						'none' => 'Ne pas créer de client dans la CRM'
					),
					'data' => $selectedOption

			))
			->add($compteClient.'-name', 'text', array(
 					'required' => false,
					'mapped' => false,
					'label' => $compteClient,
					'attr' => array('class' => 'typeahead-compte'),
					'data' => $dataName,
			))
			->add($compteClient.'-compte', 'hidden', array(
 					'required' => false,
					'attr' => array('class' => 'entity-compte'),
					'data' => $dataCompte,
			))
			->add($compteClient.'-label', 'hidden', array(
					'required' => false,
					'attr' => array('class' => 'entity-compte'),
					'data' => $key,
			));
		}

		//idem pour les fournisseurs
		foreach($arr_fournisseurs as $key => $compteFournisseur){

			$dataName = null;
			$dataCompte = null;
			$nomCompteComptable = substr($compteFournisseur,3);
			$found = false;

			foreach($arr_all_comptes as $compte){
				$nomCompteFournisseur = strtoupper($compte->getNom());
				if($this->startsWith($nomCompteFournisseur, $nomCompteComptable)){
					$dataName = $compte->getNom();
					$dataCompte = $compte->getId();
					$found = true;
					break;
				}
			}

			if(!$found){
				foreach($arr_all_contacts as $contact){
					$nomContactFournisseur = strtoupper($contact->getNom());
					if($this->startsWith($nomContactFournisseur, $nomCompteComptable)){
						$dataName = $contact->getCompte()->getNom().' ('.$contact.')';
						$dataCompte = $contact->getCompte()->getId();
						break;
					}
				}
			}

			//ajout des boutons radio au formulaire de mapping
			$selectedOption = 'new';
			if($dataName){
				$selectedOption = 'existing';
			}
			$formBuilder->add($compteFournisseur.'-radio', 'choice', array(
					'required' => true,
					'mapped' => false,
					'expanded' => true,
					'choices' => array(
						'existing' => 'Utiliser un fournisseur existant dans la CRM',
						'new' => 'Créer un nouveau fournisseur dans la CRM',
						'none' => 'Ne pas créer de fournisseur dans la CRM'
					),
					'data' => $selectedOption

			))
			->add($compteFournisseur.'-name', 'text', array(
					'required' => false,
					'mapped' => false,
					'label' => 'Fournisseur',
					'attr' => array('class' => 'typeahead-compte'),
					'data' => $dataName,
			))
			->add($compteFournisseur.'-compte', 'hidden', array(
					'required' => false,
					'attr' => array('class' => 'entity-compte'),
					'data' => $dataCompte,
			))
			->add($compteFournisseur.'-label', 'hidden', array(
					'required' => false,
					'attr' => array('class' => 'entity-compte'),
					'data' => $key,
			));
		}

		$formBuilder->add('submit','submit', array(
				'label' => 'Suite',
				'attr' => array('class' => 'btn btn-success')
		));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);

		if (($form->isSubmitted() && $form->isValid())  || (count($arr_all_comptes) == 0)) {

			foreach($arr_data as $data){
				if(!in_array($data['A'], $arr_fournisseurs) && !in_array($data['A'], $arr_clients)){
					//créer compte comptable
					$cc = new CompteComptable();
					$cc->setNum($data['A']);
					$cc->setNom($data['B']);
					$cc->setCompany($this->getUser()->getCompany());
					$em->persist($cc);
				}
			}

			foreach($arr_fournisseurs as $label => $code){

				if(!in_array($code, $arr_num)){
					//créer compte comptable
					$cc = new CompteComptable();
					$cc->setNum($code);
					$cc->setNom($label);
					$cc->setCompany($this->getUser()->getCompany());
					$em->persist($cc);
				}

				if($form[$code.'-radio']->getData() == "new"){
					$compteFournisseur = new Compte();
					$compteFournisseur->setNom($label);
					$compteFournisseur->setFournisseur(true);
					$compteFournisseur->setCompteComptableFournisseur($cc);
					$compteFournisseur->setCompany($this->getUser()->getCompany());
					$compteFournisseur->setUserCreation($this->getUser());
					$compteFournisseur->setUserGestion($this->getUser());
					$compteFournisseur->setDateCreation(new \DateTime(date('Y-m-d')));
					$em->persist($compteFournisseur);

				} elseif($form[$code.'-radio']->getData() == "existing"){
					$compteFournisseur = $compteRepo->find($form[$code.'-compte']->getData());
					$compteFournisseur->setFournisseur(true);
					$compteFournisseur->setCompteComptableFournisseur($cc);
					$em->persist($compteFournisseur);
				}

			}

			foreach($arr_clients as $label => $code){

				if(!in_array($code, $arr_num)){
					//créer compte comptable
					$cc = new CompteComptable();
					$cc->setNum($code);
					$cc->setNom($label);
					$cc->setCompany($this->getUser()->getCompany());
					$em->persist($cc);
				}

				if($form[$code.'-radio']->getData() == "new"){
					$compteFournisseur = new Compte();
					$compteFournisseur->setNom($label);
					$compteFournisseur->setFournisseur(true);
					$compteFournisseur->setCompteComptableFournisseur($cc);
					$compteFournisseur->setCompany($this->getUser()->getCompany());
					$compteFournisseur->setUserCreation($this->getUser());
					$compteFournisseur->setUserGestion($this->getUser());
					$compteFournisseur->setDateCreation(new \DateTime(date('Y-m-d')));
					$em->persist($compteFournisseur);

				} elseif($form[$code.'-radio']->getData() == "existing"){
					$compteFournisseur = $compteRepo->find($form[$code.'-compte']->getData());
					$compteFournisseur->setFournisseur(true);
					$compteFournisseur->setCompteComptableFournisseur($cc);
					$em->persist($compteFournisseur);
				}

			}

			$em->flush();

			//suppression du fichier temporaire
			unlink($filepath);
			//suppression de la variable de session
			$session->remove('import_comptes_comptables_file');

			if($initialisation){
				return $this->render('compta/plan/compta_plan_importer_mapping_ok_modal.html.twig');
			}
			return $this->redirect($this->generateUrl('compta_compte_liste'));
		}

		return $this->render('compta/plan/compta_plan_importer_mapping.html.twig', array(
				'form' => $form->createView(),
				'arr_clients' => $arr_clients,
				'arr_fournisseurs' => $arr_fournisseurs,
				'arr_comptes' => $arr_all_comptes
		));
	}

	/**
	 * @Route("/compta/compte/importer/{initialisation}", name="compta_compte_importer")
	 */
	public function compteImporterAction($initialisation = false)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->add('fichier', 'file', array(
				'required' => true,
				'label' => 'Fichier Excel',
		));

		$form->add('submit', 'submit', array(
				'label' => 'Importer mon plan comptable',
				'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['fichier']->getData() == null){
				throw new \Exception("Vous n'avez pas uploadé de fichier.");
			}

			//recuperation des données du formulaire
			$data = $form->getData();
			$file = $data['fichier'];

			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/plan_comptable';
			$file->move($path, $filename);

			//enregistrement en session du chemin vers le fichier temporaire
			$session = $request->getSession();
			$session->set('import_comptes_comptables_file', $path.'/'.$filename);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('compta_compte_importer_mapping_clients_fournisseurs'));

		}

		if($initialisation == true){
			return $this->render('compta/plan/compta_plan_importer_initialisation.html.twig', array(
					'form' => $form->createView(),
			));
		}

		return $this->render('compta/plan/compta_plan_importer.html.twig', array(
				'form' => $form->createView(),
		));

	}

	/**
	 * @Route("/compta/compte/ajouter", name="compta_compte_ajouter")
	 */
	public function compteAjouterAction()
	{
		$cc = new CompteComptable();
		$cc->setCompany($this->getUser()->getCompany());
		$form = $this->createForm(new CompteComptableType(), $cc);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($cc);
			$em->flush();

			return $this->redirect($this->generateUrl('compta_compte_liste'));
		}

		return $this->render('compta/plan/compta_plan_ajouter.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/compte/ajouter-sous-compte/{id}/{redirect}", name="compta_compte_ajouter_sous_compte")
	 */
	public function compteAjouterSousCompteAction(CompteComptable $compte, $redirect=0){

		$oldNum = $compte->getNum();
		if(substr( $compte->getNum(), -strlen( '00000' ) ) == '00000'){
			$num = substr($compte->getNum(), 0, strlen($compte->getNum())-5);
			$compte->setNum($num);
		}

		$cc = new CompteComptable();
		$cc->setCompany($this->getUser()->getCompany());
		$form = $this->createForm(new CompteComptableType(), $cc);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$numCC = $compte->getNum().$cc->getNum();
			$cc->setNum($numCC);
			$compte->setNum($oldNum);

			$em = $this->getDoctrine()->getManager();
			$em->persist($cc);
			$em->flush();

			if($redirect == 1){
				return $this->redirect($this->generateUrl('compta_compte_liste'));
			}

			return new JsonResponse(array(
				'id' => $cc->getId(),
				'num' => $cc->getNum(),
				'nom' => $cc->getNom()
			));

		}

		return $this->render('compta/plan/compta_plan_ajouter_sous_compte.html.twig', array(
				'form' => $form->createView(),
				'parent' => $compte,
				'redirect' => $redirect
		));
	}

	/**
	 * @Route("/compta/compte/supprimer-sous-compte/{id}", name="compta_compte_supprimer_sous_compte")
	 */
	public function compteSupprimerSousCompteAction(CompteComptable $compte){

		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($compte);
			$em->flush();

			return $this->redirect($this->generateUrl('compta_compte_liste'));
		}

		return $this->render('compta/plan/compta_plan_supprimer_sous_compte.html.twig', array(
				'form' => $form->createView(),
				'compte' => $compte
		));
	}

	/**
	 * @Route("/compta/compte/voir/{id}",
	 *   name="compta_compte_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function compteVoirAction(CompteComptable $compteComptable){

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
				$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder->add('years', 'choice', array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear
		));

		$form = $formBuilder->getForm();

		return $this->render('compta/compte_comptable/compta_compte_comptable_voir.html.twig', array(
				'form' => $form->createView(),
				'compteComptable' => $compteComptable
		));
	}

	/**
	 * @Route("/compta/compte/voir/annee/{id}/{year}",
	 *   name="compta_compte_voir_annee",
	 *   options={"expose"=true}
	 * )
	 */
	public function compteVoirAnneeAction(CompteComptable $compteComptable, $year){

		$start = new \DateTime($year.'-01-01');
    	$end = new \DateTime($year.'-12-31');

		//lignes du journal de ventes pour le compte $compte
		$repoJournalVente = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
		$arr_journal_vente = $repoJournalVente->findByCompteForCompany($compteComptable, null, $start, $end);

		//lignes du journal d'achats pour le compte $compte
		$repoJournalAchat = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalAchat');
		$arr_journal_achat = $repoJournalAchat->findByCompteForCompany($compteComptable, null, $start, $end);

		//lignes du journal de banque pour le compte $compte
		$repoJournalBanque = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
		$arr_journal_banque = $repoJournalBanque->findByCompteForCompany($compteComptable, null, $start, $end);

		//lignes des opérations diverses
		$repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_operation_diverse = $repoOperationDiverse->findByCompteForCompany($compteComptable, null, $start, $end);

		//regroupement dans 1 seul array
		$arr_lignes = array_merge($arr_journal_vente, $arr_journal_achat, $arr_journal_banque, $arr_operation_diverse);

		usort($arr_lignes, function($a, $b) {

	   		if( $a->getLettrage() == $b->getLettrage()){
	   			if($a->getDebit() > $b->getDebit()){
	   				return 1;
	   			}
	   			return -1;
	   		}

	   		if($a->getLettrage() > $b->getLettrage()){
	   			return 1;
	   		}
	   		return -1;
		});

		//calcul des totaux debit et credit
		$total_debit = 0;
		$total_credit = 0;
		foreach($arr_lignes as $ligne){
			if($ligne->getDebit() != null){
				$total_debit+=$ligne->getDebit();
			}
			if($ligne->getCredit() != null){
				$total_credit+=$ligne->getCredit();
			}
		}

		return $this->render('compta/compte_comptable/compta_compte_comptable_voir_annee.html.twig', array(
				'arr_lignes' => $arr_lignes,
				'total_debit' => $total_debit,
				'total_credit' => $total_credit
		));
	}

	private function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	private function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

	/**
	 * @Route("/compta/compte/generer/{baseNum}/{compte_id}", name="compta_compte_generer")
	 */
	public function compteGenererAction($baseNum, $compteId){

		$em = $this->getDoctrine()->getManager();
		$compteComptableRepo = $em->getRepository('AppBundle:Compta\CompteComptable');

		$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
		$compte = $compteRepo->find($compteId);

		$compteComptable = $this->createCompteComptableForCompte($baseNum, $compte->getNom());

		$em->persist($compteComptable);
		$em->flush();

		return $compteComptable;
	}

	/**
	 * @Route("/compta/compte/get-from-piece/{piece_id}/{type}", name="compta_compte_get_from_piece", options={"expose"=true})
	 */
	public function compteGetFromPieceAction($piece_id, $type) {

		$cc = null;

		if($type =="CLIENT"){
			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
			$facture = $repository->find($piece_id);
			$cc = $facture->getCompte()->getCompteComptableClient();


		} else if ($type == "FOURNISSEUR"){
			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Depense');
			$depense = $repository->find($piece_id);
			$cc = $depense->getCompte()->getCompteComptableFournisseur();

		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($cc->getId()));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/compta/compte/create-for-compte/{base_num}/{nom}", name="create_compte_comptable_for_compte", options={"expose"=true})
	 */
	public function createCompteComptableForCompte($baseNum, $nom){

		$nbChars = 3;

		$num= $baseNum.mb_strtoupper(substr($nom,0,$nbChars), 'UTF-8');
		$arr_replace = array(' ','_','&','\'','(',')');
		$num = str_replace($arr_replace, "", $num);

		//find array of existing nums for this company
		$compteComptableRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$arr_nums = $compteComptableRepo->findAllNumForCompany($this->getUser()->getCompany());
		$arr_existings_nums = array();
		foreach($arr_nums as $arr){
			$arr_existings_nums[] = $arr['num'];
		}

		//max 8 characters
		while(in_array($num, $arr_existings_nums) && $nbChars<=5) {
			$nbChars++;
			$num = $baseNum;
			$num.= strtoupper(substr($nom,0,$nbChars));
			$arr_replace = array(' ','_','&','\'');
			$num = str_replace($arr_replace, "", $num);
		}

		if(in_array($num, $arr_existings_nums)){
			throw new \Exception('Le compte comptable existe déjà.');
		}

		$compteComptable = new CompteComptable();
		$compteComptable->setNom($nom);
		$compteComptable->setCompany($this->getUser()->getCompany());
		$compteComptable->setNum($num);

		if($baseNum == '401'){
			$compteTVA = $compteComptableRepo->findOneBy(array(
				'num' => '44566000',
				'company' => $this->getUser()->getCompany()
			));
			$compteComptable->setCompteTVA($compteTVA);
		}
		return $compteComptable;
	}

	/**
	 * @Route("/compta/plan/exporter", name="compta_plan_exporter")
	 */
	public function planComptableExporterAction()
	{

		$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$arr_cc = $ccRepo->findBy(
			array(
				'company' => $this->getUser()->getCompany()
			), array(
				'num' => 'ASC'
		));

		//convert UTF8 strings to  ISO-8859-1 since most people will open this CSV file with Excel which doesn't handle UTF8
		$header[] = utf8_decode ('Compte');
		$header[] = utf8_decode ('Libellé');

		$respdata = [];
		$respdata[] = $header;

		foreach($arr_cc as $cc){
			$ccData = array();
			$ccData[0] =  utf8_decode ($cc->getNum() );
			$ccData[1] =  utf8_decode ($cc->getNom() );
			$respdata[] = $ccData;
		}

		$response = new StreamedResponse();
		$response->setCallback(
			function () use ($respdata) {
				$handle = fopen('php://output', 'r+');
				foreach ($respdata as $row) {
					fputcsv($handle, $row);
				}
				fclose($handle);
			}
		);

		$response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
		$response->headers->set('Content-Encoding', 'UTF-8');
		$response->headers->set('Content-Disposition', 'attachment; filename="plan_comptable.csv"');

		return $response;

	}

	/**
	 * @Route("/compta/compte-comptable/corriger/{id}/{codeJournal}/{redirectRoute}", name="compta_compte-comptable_corriger")
	 */
	public function compteComptableCorrigerAction($id, $codeJournal, $redirectRoute = null){

	 	$em = $this->getDoctrine()->getManager();
	 	$banque = false;
		switch($codeJournal){

			case 'VE':
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
				break;

			case 'AC':
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalAchat');
				break;

			case 'OD':
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
				break;

			default:
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
				$banque = true;
				break;

		}

		$ligneJournal = $repo->find($id);
		$oldCompteComptable = $ligneJournal->getCompteComptable();

		//creation du formulaire
		$form = $this->createForm(new LigneJournalCorrectionType($this->getUser()->getCompany()));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$validator = $this->get('validator');

	 	   	$compteNotInList = $form->get('compteNotInList')->getData();
 	   	 	if($compteNotInList){

			  $prefixe = $form->get('comptePrefixe')->getData();
				$num = $form->get('compteNum')->getData();
				$nom = $form->get('compteNom')->getData();

				 $compteComptableService = $this->get('appbundle.compta_compte_comptable_service');
				 $newCompteComptable = null;
				 try{
					 $newCompteComptable = $compteComptableService->createCompteComptable($this->getUser()->getCompany(), $prefixe.$num, $nom);
					 $em->persist($newCompteComptable);
				 } catch(\Exception $e){

					 //handle form errors in a modal in the most ugliest way
					$jsonResponse = new JsonResponse(
		    			array(
		    				'message' => $e->getMessage()
							),
	    				400);

					return $jsonResponse;

				 }

			 } else {
				 $newCompteComptable = $form->get('compteComptable')->getData();
			 }

			 if($form->get('corriger')->isClicked()){
				 //correction directe
				 $ligneJournal->setCompteComptable($newCompteComptable);
				 $em->persist($ligneJournal);
			   	 $em->flush();
			 } else {
				 //OD
				 $operationDiverseService = $this->get('appbundle.compta_operation_diverse_service');
				 $operationDiverseService->corrigerAffectationAvecOD($ligneJournal, $newCompteComptable);
			 }

			 //on corrige également le rapprochement concerné pour garder le tableau de trésorerie à jour
			 if($banque == true){
			 	 $rapprochements = $ligneJournal->getMouvementBancaire()->getRapprochements();
			 	 foreach( $rapprochements as $rapprochement ){
			 	 	if( $rapprochement->getAffectationDiverse() ){
			 	 		$affectationDiverse = $rapprochement->getAffectationDiverse();
			 	 		if( $affectationDiverse->getCompteComptable() ==  $oldCompteComptable){

			 	 			if( $affectationDiverse->getRecurrent() ){

			 	 				$newAffectationDiverse = new AffectationDiverse();
			 	 				$newAffectationDiverse->setNom("Correction");
			 	 				$newAffectationDiverse->setType($affectationDiverse->getType());
			 	 				$newAffectationDiverse->setCompteComptable($newCompteComptable);
			 	 				$newAffectationDiverse->setCompany($this->getUser()->getCompany());
			 	 				$newAffectationDiverse->setRecurrent(false);
			 					$em->persist($newAffectationDiverse);
			 	 				$rapprochement->setAffectationDiverse($newAffectationDiverse);
			 	 				$em->persist($rapprochement);
			 	 			} else {
			 	 				$affectationDiverse->setCompteComptable($newCompteComptable);
			 	 				$em->persist($affectationDiverse);
			 	 			}

			 	 			$em->flush();

			 	 		}
			 	 	}
			 	}
			 }
			
			return new Response();
		}

		$compteId = null;
		if($redirectRoute == "compta_compte_voir"){
			$compteId = $ligneJournal->getCompteComptable()->getId();
		}

		return $this->render('compta/compte_comptable/compta_compte_comptable_corriger_modal.html.twig', array(
				'form' => $form->createView(),
				'ligneJournal' => $ligneJournal,
				'codeJournal' => $codeJournal,
				'redirectRoute' => $redirectRoute,
				'compteId' => $compteId
		));

	}

	/**
	 * @Route("/compta/compte/corriger-lettrage/{id}/{journal}", name="compta_compte_corriger_lettrage")
	 */
	public function compteCorrigerLettrageAction($id, $journal){
		$response = new Response();

		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:Compta\JournalBanque');
		if($journal == 'VE'){
			$repo = $em->getRepository('AppBundle:Compta\JournalVente');
		} else if($journal == 'AC'){
			$repo = $em->getRepository('AppBundle:Compta\JournalAchat');
		} else if($journal == 'OD'){
			$repo = $em->getRepository('AppBundle:Compta\OperationDiverse');
		}

		$requestData = $this->getRequest();
		$valeur = $requestData->get('value');

		$ligne = $repo->find($id);
		$ligne->setLettrage($valeur);
		$em->persist($ligne);
		$em->flush();

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/compta/compte/commenter/{id}/{journal}", name="compta_compte_commenter")
	 */
	public function compteCommenterAction($id, $journal){
		$response = new Response();

		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:Compta\JournalBanque');
		if($journal == 'VE'){
			$repo = $em->getRepository('AppBundle:Compta\JournalVente');
		} else if($journal == 'AC'){
			$repo = $em->getRepository('AppBundle:Compta\JournalAchat');
		}  else if($journal == 'OD'){
			$repo = $em->getRepository('AppBundle:Compta\OperationDiverse');
		}

		$requestData = $this->getRequest();
		$valeur = $requestData->get('value');

		$ligne = $repo->find($id);
		$ligne->setCommentaire($valeur);
		$em->persist($ligne);
		$em->flush();

		$response->setStatusCode(200);
		return $response;
	}


}
