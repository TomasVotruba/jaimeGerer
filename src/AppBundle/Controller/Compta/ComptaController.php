<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\SettingsActivationOutil;
use AppBundle\Entity\Compta\AffectationDiverse;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

class ComptaController extends Controller
{
	/**
	 * @Route("/compta", name="compta_index")
	 */
	public function indexAction()
	{
		//vérifier si l'outil Compta a été activé
		$settingsActivationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$settingsActivationCompta = $settingsActivationRepo->findBy(array(
				'company' => $this->getUser()->getCompany(),
				'outil' => 'COMPTA'
		));

		//outil non activé : paramétrage
		if($settingsActivationCompta == null){
			return $this->redirect($this->generateUrl('compta_activer_start'));
		}

		$opportuniteService = $this->get('appbundle.crm_opportunite_service');
		$chartService = $this->get('appbundle.chart_service');

		$dataChartCAAnalytique = $opportuniteService->getDataChartCAAnalytique($this->getUser()->getCompany(), date('Y'));		
		$chartCAAnalytique = $chartService->caAnalytique($dataChartCAAnalytique);

		$dataChartCARhoneAlpes = $opportuniteService->getDataChartCARhoneAlpes($this->getUser()->getCompany(), date('Y'));		
		$chartCARhoneAlpes = $chartService->caAnalytique($dataChartCARhoneAlpes);

		return $this->render('compta/compta_index.html.twig', array(
			'chartCAAnalytique' => $chartCAAnalytique,
			'chartCARhoneAlpes' => $chartCARhoneAlpes
		));
	}

	/**
	 * @Route("/compta/activation/start", name="compta_activer_start")
	 */
	public function activationStartAction(){
		return $this->render('compta/activation/compta_activation_start.html.twig');
	}

	/**
	 * @Route("/compta/activation/reutiliser", name="compta_activer_reutiliser")
	 */
	public function activationReutiliserAction(){
		return $this->render('compta/activation/compta_activation_reutiliser.html.twig');
	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau", name="compta_activer_reports_a_nouveau")
	 */
	public function activationReportsANouveauAction(){
		return $this->render('compta/activation/compta_activation_reports_a_nouveau.html.twig');
	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau/importer", name="compta_activer_reports_a_nouveau_importer")
	 */
	public function activationReportsANouveauImporterAction(){
		return $this->render('compta/activation/compta_activation_importer_reports_a_nouveau.html.twig');
	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau/importer/upload", name="compta_activer_reports_a_nouveau_importer_upload")
	 */
	public function activationReportsANouveauImporterUploadAction(){

		$em = $this->getDoctrine()->getManager();
		$requestData = $this->getRequest();

		$arr_files = $requestData->files->all();
		$file = $arr_files["files"][0];
		//enregistrement temporaire du fichier uploadé
		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/reports_a_nouveau';
		$file->move($path, $filename);

		//lecture du fichier Excel
		$fileType = PHPExcel_IOFactory::identify($path.'/'.$filename);
		$readerObject = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $readerObject->load($path.'/'.$filename);
		//fichier dans un array associatif
		$arr_data = $objPHPExcel->getActiveSheet()->toArray(null,true,false,true);

		//recherche des numéro de comptes comptables existants
		$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
		$arr_cc = $ccRepo->findByCompany($this->getUser()->getCompany());
		$arr_num = array();
		foreach($arr_cc as $compte){
			$arr_num[$compte->getNum()] = $compte;
		}

		foreach($arr_data as $data){
			$num = strval($data['A']);
			if(array_key_exists($num, $arr_num)){
				$compte = $arr_num[$num];
				$compte->setReportDebit($data['B']);
				$compte->setReportCredit($data['C']);

				$em->persist($compte);
			}

		}
		$em->flush();
		unlink($path.'/'.$filename);

		$response = new JsonResponse();

		return $response;

	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau/importer/ok", name="compta_activer_reports_a_nouveau_importer_ok")
	 */
	public function activationReportsANouveauImporterOKAction(){
		return $this->render('compta/activation/compta_activation_importer_reports_a_nouveau_ok_modal.html.twig');
	}

	/**
	 * @Route("/compta/activation", name="compta_activer")
	 */
	public function activationAction(){

		$em = $this->getDoctrine()->getManager();
		$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');

		//création des affectations diverses de vente et d'achat de base pour l'entreprise
		$affDivRepo = $em->getRepository('AppBundle:Compta\AffectationDiverse');
		$arr_aff = $affDivRepo->findByCompany(NULL); //les affectations diverses de base ont company=NULL
		foreach($arr_aff as $aff){
			$newAff = clone $aff;
			$newAff->setCompany($this->getUser()->getCompany());

			//lier les affectations diverses au compte comptable de l'entreprise
			$compte = $ccRepo->findOneBy(array(
					'num' => $aff->getCompteComptable()->getNum(),
					'company' => $this->getUser()->getCompany()
			));
			$newAff->setCompteComptable($compte);
			$em->persist($newAff);
		}

		//activer la compta
		$activationCompta = new SettingsActivationOutil();
		$activationCompta->setCompany($this->getUser()->getCompany());
		$activationCompta->setDate(new \DateTime(date('Y-m-d')));
		$activationCompta->setOutil('COMPTA');
		$em->persist($activationCompta);
		$em->flush();

		return $this->render('compta/activation/compta_activation.html.twig');
	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau/importer/apres", name="compta_activer_import_apres_report")
	 */
	public function activationReportsANouveauImporterApresAction(){
		return $this->render('compta/activation/compta_activation_import_apres_report.html.twig');
	}

	/**
	 * @Route("/compta/activation/reports-a-nouveau/call-me", name="compta_activer_reports_a_nouveau_call_me_baby")
	 */
	public function activationReportsANouveauCallMeBabyAction(){
		return $this->render('compta/activation/compta_activer_reports_a_nouveau_call_me_baby.html.twig');
	}

	/**
	 * @Route("/compta/activation/import/depense/ok", name="compta_activer_importer_depense_ok")
	 */
	public function activationDepenseImporterOKAction(){
		return $this->render('compta/activation/compta_activation_importer_depense_ok_modal.html.twig');
	}

	/**
	 * @Route("/compta/activation/compte-tva-achats/{prev}", name="compta_activer_compte_tva_achats")
	 */
	public function activationCompteTVAAchatsAction($prev){

		$em = $this->getDoctrine()->getManager();
		$compteComptableRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
		$formBuilder = $this->createFormBuilder();

		$compteTVA = $compteComptableRepo->findOneBy(array(
			'num' => '4456',
			'company' => $this->getUser()->getCompany()
		));
		if($compteTVA == null){
			$choice = null;
		} else {
			$choice = $compteTVA->getId();
		}

		$all_comptesTVA = $compteComptableRepo->findAllByNum('445', $this->getUser()->getCompany());
		$arr_choices_tva = array();
		foreach($all_comptesTVA as $cc){
			$arr_choices_tva[$cc->getId()] = $cc;
		}

		$all_comptesNoTVA = $compteComptableRepo->findAllByNum('6', $this->getUser()->getCompany());
		$arr_choices_no_tva = array();
		foreach($all_comptesNoTVA as $cc){
			$arr_choices_no_tva[$cc->getId()] = $cc;
		}

		$formBuilder
			->add('compteComptableTVA', 'choice', array(
            		'choices' => $arr_choices_tva,
            		'required' => true,
            		'label' => 'Compte de TVA sur achats à utiliser',
					'data' => $choice
         	))
			->add('compteComptableNoTVA', 'choice', array(
					'choices' => $arr_choices_no_tva,
					'required' => false,
					'label' => 'Ne pas utiliser de TVA sur les comptes suivants',
					'multiple' => true
			))
			->add('submit', 'submit', array(
				'attr' => array('class' => 'btn btn-success'),
				'label' => 'OK'
			))
		;

		$form = $formBuilder->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$arr_noTVA = $form['compteComptableNoTVA']->getData();
			$id = $form['compteComptableTVA']->getData();
			$ccTVA = $compteComptableRepo->find($id);

			foreach($all_comptesNoTVA as $compte6){
				if(!in_array($compte6->getId(), $arr_noTVA)){
					$compte6->setCompteTVA($ccTVA);
					$em->persist($compte6);
					$em->flush();
				}
			}

			switch($prev){
				case 1:
					return $this->redirect($this->generateUrl('compta_activer_analytique_tva'));

				case 2:
					return $this->redirect($this->generateUrl('compta_depense_importer_historique', array('initialisation' => true)));

				case 3:
					return $this->redirect($this->generateUrl('compta_activer_analytique_tva'));
			}

		}

		return $this->render('compta/activation/compta_activation_compte_tva_achats.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/activation/analytique-tva", name="compta_activer_analytique_tva")
	 */
	public function activationAnalytiqueTVAAction(){

		$em = $this->getDoctrine()->getManager();
		$settingsRepo = $em->getRepository('AppBundle:Settings');

		$arr_analytiques = $settingsRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'module' => 'CRM',
			'parametre' => 'ANALYTIQUE'
		));

		if(count($arr_analytiques) == 0){
			return $this->redirect($this->generateUrl('compta_compte_bancaire_ajouter', array('initialisation' => true)));
		}

		$formBuilder = $this->createFormBuilder();

		$arr_choice = array(
			'0' => 'Soumis à TVA',
			'1' => 'Non soumis à TVA'
		);

		foreach($arr_analytiques as $analytique){
			if($analytique->getValeur()){
				$formBuilder->add('analytique-'.$analytique->getId(), 'choice', array(
					'choices' => $arr_choice,
					'label' => $analytique->getValeur(),
					'expanded' => true,
					'multiple' => false,
					'data' => $analytique->getNoTVA()
				));
			}
		}

		$formBuilder->add('submit', 'submit', array(
				'attr' => array('class' => 'btn btn-success'),
				'label' => 'OK'
		))
		;

		$form = $formBuilder->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			foreach($arr_analytiques as $analytique){
				if($analytique->getValeur()){
					$noTva = $form['analytique-'.$analytique->getId()]->getData();
					if($noTva != $analytique->getNoTVA()){
						$analytique->setNoTva($noTva);
						$em->persist($analytique);
					}
				}
			}

			$em->flush();
			return $this->redirect($this->generateUrl('compta_compte_bancaire_ajouter', array('initialisation' => true)));

		}

		return $this->render('compta/activation/compta_activation_analytique_tva.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/activation/import/facture", name="compta_activer_importer_facture")
	 */
	public function activationFactureImporterAction(){

		$setActRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$crmActive = $setActRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'CRM'
		));

		//CRM activée
// 		if($crmActive != null){
// 			$documentPrixRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
// 			$count = $documentPrixRepo->count($this->getUser()->getCompany(), 'FACTURE');

// 			if($count != 0){
// 				return $this->redirect($this->generateUrl('compta_facture_choisir', array('initialisation' => true)));
// 			}
// 		}

		return $this->render('compta/activation/compta_activation_importer_factures.html.twig');
	}

	/**
	 * @Route("/compta/activation/import/facture/ok", name="compta_activer_importer_facture_ok")
	 */
	public function activationFactureImporterOKAction(){
		return $this->render('compta/activation/compta_activation_importer_facture_ok_modal.html.twig');
	}

	/**
	 * @Route("/compta/activation/tva", name="compta_activer_tva")
	 */
	public function activationTVAAction(){
		return $this->render('compta/activation/compta_activation_tva.html.twig');
	}

	/**
	 * @Route("/compta/activation/tva/enregistrer/{type}", name="compta_activer_tva_enregistrer")
	 */
	public function activationTVAEnregistrerAction($type){

		$em = $this->getDoctrine()->getManager();

		$settingsRepo = $em->getRepository('AppBundle:Settings');
		$settingsTva = $settingsRepo->findOneBy(array(
			'company' => null,
			'module' => 'COMPTA',
			'parametre' => 'TVA_ENTREE'
		));

		$newSettings = clone $settingsTva;
		$newSettings->setCompany($this->getUser()->getCompany());
		$newSettings->setValeur($type);
		$em->persist($newSettings);
		$em->flush();

		return $this->redirect($this->generateUrl('compta_activer_analytique_tva'));
	}

	/**
	 * @Route("/compta/activation/produits/{prev}", name="compta_activer_produits")
	 */
	public function activationProduitsAction($prev){

		$settingsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$arr_produits = $settingsRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'module' => 'CRM',
			'parametre' => 'TYPE_PRODUIT'
		));

		if(count($arr_produits) == 0){
			return $this->redirect($this->generateUrl('compta_activer_compte_tva_achats', array('prev' => $prev)));
		}

		$arr_choices = array();
		$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$arr_cc = $ccRepo->findAllByNum('7', $this->getUser()->getCompany());
		foreach ($arr_cc as $cc){
			$arr_choices[$cc->getId()] = $cc;
		}

		$formBuilder = $this->createFormBuilder();
		foreach($arr_produits as $produit){
			if($produit->getValeur()){
				$formBuilder->add($produit->getId(), 'choice', array(
					'label' => $produit->getValeur(),
					'required' => true,
					'choices' => $arr_choices
				));
			}
		}
		$formBuilder->add('submit', 'submit', array(
			'attr' => array('class' => 'btn btn-success'),
			'label' => 'Voilà !'
		));

		$form = $formBuilder->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			foreach($arr_produits as $produit){
				if($produit->getValeur()){
					$id = $form[$produit->getId()]->getData();
					$cc = $ccRepo->find($id);

					$produit->setCompteComptable($cc);
					$em->persist($produit);
				}
			}
			$em->flush();

			return $this->redirect($this->generateUrl('compta_activer_compte_tva_achats', array('prev' => $prev)));

		}

		return $this->render('compta/activation/compta_activation_produits.html.twig', array(
			'form' => $form->createView()
		));
	}


	/**
	 * @Route("/compta/check-rapprochements", name="compta_check_rapprochements")
	 */
	public function checkRapprochements(){


		$em = $this->getDoctrine()->getManager();
		$mouvementBancaireRepo = $em->getRepository('AppBundle:Compta\MouvementBancaire');
		$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');

		$arr_mouvementsBancaires = $mouvementBancaireRepo->findByYearAndCompany(2017, $this->getUser()->getCompany());

		$objPHPExcel = new PHPExcel();
		// header row
		$arr_header = array(
			'Date',
			'Libellé',
			'Montant',
			'Compte initial',
			'Compte destination'
		);
		$row = 1;
		$col = 'A';
		foreach($arr_header as $header){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $header);
				$col++;
		}

		foreach($arr_mouvementsBancaires as $mouvement){
			if( count($mouvement->getRapprochements()) == 0  ){
				continue;
			}

			$arr_lignesJournal = $journalBanqueRepo->findByMouvementBancaire($mouvement);
			$compteComptable = null;
			if(strpos($mouvement->getLibelle(), "Cheque" ) === false &&  strpos($mouvement->getLibelle(), "Ndf" ) === false ){

				foreach($arr_lignesJournal as $ligneJournal){
					if($ligneJournal->getCompteComptable()->getId() == $mouvement->getCompteBancaire()->getCompteComptable()->getId()){
						continue;
					}

					$compteComptable = $ligneJournal->getCompteComptable();

					foreach($mouvement->getRapprochements() as $rapprochement){
						if( $rapprochement->getAffectationDiverse() ){
							if($rapprochement->getAffectationDiverse()->getCompteComptable() != $compteComptable){

								$col = 'A';
								$row++;

								$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, PHPExcel_Shared_Date::PHPToExcel($ligneJournal->getDate()));
								$objPHPExcel->getActiveSheet()->getStyle($col.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
								$col++;

								$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligneJournal->getLibelle());
								$col++;

								$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $mouvement->getMontant());
								$col++;

								$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $rapprochement->getAffectationDiverse()->getCompteComptable()->getNum());
								$col++;

								$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $compteComptable->getNum());
								$col++;


								$affectationDiverse = $rapprochement->getAffectationDiverse();
					 			if( $affectationDiverse->getRecurrent() ){

					 				$newAffectationDiverse = new AffectationDiverse();
					 				$newAffectationDiverse->setNom("Correction");
					 				$newAffectationDiverse->setType($affectationDiverse->getType());
					 				$newAffectationDiverse->setCompteComptable($compteComptable);
					 				$newAffectationDiverse->setCompany($this->getUser()->getCompany());
					 				$newAffectationDiverse->setRecurrent(false);
									$em->persist($newAffectationDiverse);
					 				$rapprochement->setAffectationDiverse($newAffectationDiverse);
					 				$em->persist($rapprochement);
					 			} else {
					 				$affectationDiverse->setCompteComptable($compteComptable);
					 				$em->persist($affectationDiverse);
					 			}

					 			$em->flush();
								
					 		}
							
						}
					}



				}

			}
			
			
		}

		//set column width
		foreach(range('A','H') as $col) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}

		 $response = new Response();
		 $response->headers->set('Content-Type', 'application/vnd.ms-excel');
		 $response->headers->set('Content-Disposition', 'attachment;filename="journal_banque.xlsx"');
		 $response->headers->set('Cache-Control', 'max-age=0');
		 $response->sendHeaders();
		 $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		 $objWriter->save('php://output');
		 exit();



	}

	// /**
	//  * @Route("/compta/set-num-ecriture", name="compta_set_num_ecriture")
	//  */
	// public function setNumEcriture(){

	// 	$em = $this->getDoctrine()->getManager();
	// 	$fecService = $this->get('appbundle.compta_fec_service');
	// 	$numService = $this->get('appbundle.num_service');

	// 	$numEcriture = 0;
	// 	$arr_piecesNum = array();

	// 	$arr_lignes = $fecService->getFECData($this->getUser()->getCompany(), '2018');
	// 	foreach($arr_lignes as $ligne){
			
	// 			if($ligne->getCodeJournal() == 'VE' || $ligne->getCodeJournal() == 'AC'){
	// 				$pieceStr = $ligne->getCodeJournal().$ligne->getPiece();
	// 			} else {
	// 				$pieceStr = $ligne->getCodeJournal().$ligne->getLibelle().$ligne->getDate()->format('Ymd').$ligne->getMontant();
	// 			}
				
			
	// 			if(!array_key_exists($pieceStr, $arr_piecesNum)){
	// 				$numEcriture++;
	// 				$arr_piecesNum[$pieceStr] = $numEcriture;
	// 				$num = $numEcriture;
	// 			} else{
	// 				$num = $arr_piecesNum[$pieceStr];
	// 			}

	// 			$ligne->setNumEcriture($num);
	// 			$em->persist($ligne);
	// 	}

	// 	$em->flush();

	// 	$numEcriture++;
	// 	$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

	// 	return new Response();
	// }


}
