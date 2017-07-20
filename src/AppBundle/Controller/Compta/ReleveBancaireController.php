<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Compta\CompteBancaire;

use AppBundle\Form\Compta\MouvementBancaireType;
use AppBundle\Form\Compta\UploadReleveBancaireType;
use AppBundle\Form\Compta\UploadReleveBancaireMappingType;
use AppBundle\Entity\Compta\SoldeCompteBancaire;

class ReleveBancaireController extends Controller
{
	/**
	 * @Route("/compta/releve-bancaire", name="compta_releve_bancaire_index")
	 */
	public function releveBancaireIndexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$compteBancaireRepo = $em->getRepository('AppBundle:Compta\CompteBancaire');
		$activationRepo = $em->getRepository('AppBundle:SettingsActivationOutil');

		$arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());

		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'COMPTA'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$currentMonth = date('m');
	
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
			$arr_years[$i] = $i;
		}
		$arr_months = array();
		for($i = 1 ; $i<=12; $i++){
			$month = str_pad($i, 2, "0", STR_PAD_LEFT);
			$arr_months[$month] = $month;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder
			->add('compte', 'entity', array(
				'required' => true,
				'class' => 'AppBundle:Compta\CompteBancaire',
				'label' => 'Compte bancaire',
				'choices' => $arr_comptesBancaires,
				'attr' => array('class' => 'compte-select')
			))
			->add('year', 'choice', array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'month-select'),
				'data' => $currentYear
			))
			->add('month', 'choice', array(
				'required' => true,
				'label' => 'Mois',
				'choices' => $arr_months,
				'attr' => array('class' => 'year-select'),
				'data' => $currentMonth
			))
			->add('submit', 'submit', array(
				'attr' => array('class' => 'btn btn-success'),
				'label' => 'Afficher'
			));

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_index.html.twig', array(
			'form' => $formBuilder->getForm()->createView()
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/voir", name="compta_releve_bancaire_voir", options={"expose"=true})
	 */
	public function releveBancaireVoirAction()
	{
		$em = $this->getDoctrine()->getManager();
		$compteBancaireRepo = $em->getRepository('AppBundle:Compta\CompteBancaire');
		$mouvementBancaireRepo = $em->getRepository('AppBundle:Compta\MouvementBancaire');

		$form = $this->get('request')->request->get('form');

		$arr_mouvements = $mouvementBancaireRepo->findByDateAndCompteBancaire($form['year'], $form['month'], $form['compte']);

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_voir.html.twig', array(
			'arr_mouvements' => $arr_mouvements
		));
	}

	
	/**
	 * @Route("/compta/releve-bancaire/importer/form", name="compta_releve_bancaire_importer_form")
	 */
	public function releveBancaireImporterFormAction()
	{
		$form = $this->createForm(new UploadReleveBancaireType($this->getUser()->getCompany()));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			//recuperation des données du formulaire
			$data = $form->getData();
			$compteBancaire = $data['compteBancaire'];
			$file = $data['file'];
			$solde = $data['solde'];
			$dateFormat = $data['dateFormat'];

			//mise à jour du compte bancaire si le solde a été modifié
			$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
			$latestSolde = $soldeRepo->findLatest($compteBancaire);
			if($solde != $latestSolde->getMontant()){
				$newSolde = new SoldeCompteBancaire();
				$newSolde->setCompteBancaire($compteBancaire);
				$newSolde->setDate(new \DateTime(date('Y-m-d')));
				$newSolde->setMontant($solde);
				$em = $this->getDoctrine()->getManager();
				$em->persist($newSolde);
				$em->flush();
			}

			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$compteBancaire->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/releve_bancaire';
			$file->move($path, $filename);

			$session = $request->getSession();
			$session->set('import_releve_filename', $filename);
			$session->set('import_releve_compte_bancaire_id', $compteBancaire->getId());
			$session->set('import_releve_compte_date_format', $dateFormat);


			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('compta_releve_bancaire_importer_mapping'));
		}

		//pour permettre de changer le solde du compte bancaire avant d'importer le fichier
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());
		$arr_soldes_id = array();
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		foreach($arr_comptesBancaires as $compteBancaire){
			$solde = $soldeRepo->findLatest($compteBancaire);
			$arr_soldes_id[$compteBancaire->getId()] = $solde->getMontant();
		}

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_form.html.twig', array(
			'form' => $form->createView(),
			'arr_soldes' => $arr_soldes_id
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/importer/mapping", name="compta_releve_bancaire_importer_mapping")
	 */
	public function releveBancaireImporterMappingAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$em = $this->getDoctrine()->getManager();
		
		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/releve_bancaire';
		$filename = $session->get('import_releve_filename');
		$fh = fopen($path.'/'.$filename, 'r+');

		//récupération de la première ligne pour créer le formulaire de mapping
		$arr_headers = array();
		$i = 0;
		while( ($row = fgetcsv($fh, 8192)) !== FALSE && $i<1 ) {
			//convert because CSV from Excel is not encoded in UTF8
			$row = array_map("utf8_encode", $row);
			$arr_headers = explode(';',$row[$i]);
			$i++;
		}
		fclose($fh);
		$arr_headers = array_combine($arr_headers, $arr_headers); //pour que l'array ait les mêmes clés et valeurs
		$form_mapping = $this->createForm(new UploadReleveBancaireMappingType($arr_headers));

		$request = $this->getRequest();
		$form_mapping->handleRequest($request);

		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {
			//recuperation des données du formulaire
			$data = $form_mapping->getData();

			$session->set('import_releve_compte_col_date', $data['date']);
			$session->set('import_releve_compte_col_libelle', $data['libelle']);
			$session->set('import_releve_compte_col_debit', $data['debit']);
			$session->set('import_releve_compte_col_credit', $data['credit']);
	
			return $this->redirect(
				$this->generateUrl('compta_releve_bancaire_importer_validation')
			);
		}

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_mapping.html.twig', array(
			'form' => $form_mapping->createView(),
		));

	}

	/**
	 * @Route("/compta/releve-bancaire/importer/validation", name="compta_releve_bancaire_importer_validation")
	 */
	 public function releveBancaireImporterValidationAction(){

	 	$session = $this->getRequest()->getSession();
		$releveBancaireService = $this->get('appbundle.compta_releve_bancaire_service');
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');

		$compte_bancaire_id = $session->get('import_releve_compte_bancaire_id');
		$compteBancaire = $compteBancaireRepo->find($compte_bancaire_id);

		$filename = $session->get('import_releve_filename');
	 	$dateFormat = $session->get('import_releve_compte_date_format');
	 	$colDate = $session->get('import_releve_compte_col_date');
	 	$colLibelle = $session->get('import_releve_compte_col_libelle');
	 	$colDebit = $session->get('import_releve_compte_col_debit');
	 	$colCredit = $session->get('import_releve_compte_col_credit');

		$arr_parsed = $releveBancaireService->parseReleveCSV($colDate, $colLibelle, $colDebit, $colCredit, $dateFormat, $filename, $compteBancaire);

		$ancienSolde = $soldeRepo->findLatest($compteBancaire);
		$nouveauSolde = $ancienSolde->getMontant()+$arr_parsed['total'];

		return $this->render('compta/releve_bancaire/compta_releve_bancaire_importer_validation.html.twig', array(
			'arr_mouvements' => $arr_parsed['arr_mouvements'],
			'total' => $arr_parsed['total'],
			'ancienSolde' => $ancienSolde->getMontant(),
			'nouveauSolde' => $nouveauSolde,
		));
	}

	/**
	 * @Route("/compta/releve-bancaire/importer", name="compta_releve_bancaire_importer")
	 */
	 public function releveBancaireImporterAction(){

	 	$session = $this->getRequest()->getSession();
	 	$em = $this->getDoctrine()->getManager();
		$releveBancaireService = $this->get('appbundle.compta_releve_bancaire_service');
		$compteBancaireRepo = $em->getRepository('AppBundle:Compta\CompteBancaire');
		$soldeRepo = $em->getRepository('AppBundle:Compta\SoldeCompteBancaire');

		$compte_bancaire_id = $session->get('import_releve_compte_bancaire_id');
		$compteBancaire = $compteBancaireRepo->find($compte_bancaire_id);

		$filename = $session->get('import_releve_filename');
	 	$dateFormat = $session->get('import_releve_compte_date_format');
	 	$colDate = $session->get('import_releve_compte_col_date');
	 	$colLibelle = $session->get('import_releve_compte_col_libelle');
	 	$colDebit = $session->get('import_releve_compte_col_debit');
	 	$colCredit = $session->get('import_releve_compte_col_credit');

		$arr_parsed = $releveBancaireService->parseReleveCSV($colDate, $colLibelle, $colDebit, $colCredit, $dateFormat, $filename, $compteBancaire);

		foreach($arr_parsed['arr_mouvements'] as $mouvement){
			$em->persist($mouvement);
		}

		$ancienSolde = $soldeRepo->findLatest($compteBancaire);

		$newSolde = new SoldeCompteBancaire();
		$newSolde->setCompteBancaire($compteBancaire);
		$newSolde->setDate(new \DateTime(date('Y-m-d')));
		$newSolde->setMontant($ancienSolde->getMontant()+$arr_parsed['total']);
		$em->persist($newSolde);

		$em->flush();

		return $this->redirect($this->generateUrl('compta_releve_bancaire_index'));
	}
	
}
