<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Form\Compta\OperationDiverseType;
use AppBundle\Form\Compta\OperationDiverseCreationType;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;


class OperationDiverseController extends Controller
{
	/**
	 * @Route("/compta/operation-diverse/liste", name="compta_operation_diverse_liste")
	 */
	public function operationDiverseListeAction(){
	
		//lignes des opérations diverses
		$repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_lignes = $repoOperationDiverse->findForCompany($this->getUser()->getCompany());
		
		return $this->render('compta/operation_diverse/compta_operation_diverse_liste.html.twig', array(
				'arr_lignes' => $arr_lignes,
		));
	}

	/**
	 * @Route("/compta/journal-od",
	 *   name="compta_journal_od_index"
	 * )
	 */
	public function indexAction()
	{
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

		return $this->render('compta/operation_diverse/compta_journal_od_index.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/journal-od/voir/{year}",
	 *   name="compta_journal_od_voir_annee",
	 *   options={"expose"=true}
	 * )
	 */
	public function voirAction($year)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_journalOD= $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		$arr_totaux = array(
	 		'debit' => 0,
	 		'credit' => 0
		);

		foreach($arr_journalOD as $ligne){
		 	$arr_totaux['debit']+=$ligne->getDebit();
		 	$arr_totaux['credit']+=$ligne->getCredit();
		}

		return $this->render('compta/operation_diverse/compta_journal_od_voir.html.twig', array(
			'arr_journalOD' => $arr_journalOD,
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/od/ajouter",
	 *   name="compta_od_ajouter",
	 *   options={"expose"=true}
	 * )
	 */
	public function ajouterAction()
	{
		$em = $this->getDoctrine()->getManager();
		$numService = $this->get('appbundle.num_service');
		$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

		$od = new OperationDiverse();
		$od->setCodeJournal('OD');
		$form = $this->createForm(
			new OperationDiverseCreationType(
				$this->getUser()->getCompany()->getId()
			),
			$od
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			
			
			$debit = $form->get('debit')->getData();
			$ccDebit = $form->get('compteComptableDebit')->getData();
			$od->setDebit($debit);
			$od->setCompteComptable($ccDebit);
			$od->setCredit(null);
			$od->setNumEcriture($numEcriture);
			$em->persist($od);

			$odInverse = clone($od);
			$credit = $form->get('credit')->getData();
			$ccCredit = $form->get('compteComptableCredit')->getData();
			$odInverse->setDebit(null);
			$odInverse->setCompteComptable($ccCredit);
			$odInverse->setCredit($credit);
			$odInverse->setNumEcriture($numEcriture);
			$em->persist($odInverse);
			
			$em->flush();

			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

			return $this->redirect($this->generateUrl(
					'compta_journal_od_index'
			));
		}

		return $this->render('compta/operation_diverse/compta_od_ajouter.html.twig', array(
			'form' => $form->createView(),
		));
	}


	/**
	 * @Route("/compta/journal-od/exporter/{year}",
	 *   name="compta_journal_od_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalODExporterAction($year){

		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_journalOD= $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		$arr_totaux = array(
		 	'debit' => 0,
		 	'credit' => 0
		);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->setTitle('Journal Achats '.$year);

		// header row
		$arr_header = array(
			'Code journal',
			'Date',
			'Compte',
			'Compte auxiliaire',
			'Pièce',
			'Tiers',
			'Débit',
			'Crédit',
			'Commentaire'
		);
		$row = 1;
		$col = 'A';
		foreach($arr_header as $header){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $header);
				$col++;
		}

	  	foreach($arr_journalOD as $ligne){
			$col = 'A';
			$row++;

			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCodeJournal());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, PHPExcel_Shared_Date::PHPToExcel( $ligne->getDate()) );
			$objPHPExcel->getActiveSheet()->getStyle($col.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, substr($ligne->getCompteComptable()->getNum(),0,3));
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCompteComptable()->getNum());
			$col++;
			if($ligne->getFacture()){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getFacture()->getNum());
			} else if($ligne->getDepense()){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDepense()->getNum());
			} else if ($ligne->getAvoir()){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getAvoir()->getNum());
			}
			$col++;
			if($ligne->getFacture()){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getFacture()->getCompte());
			} else if($ligne->getDepense()){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDepense()->getCompte());
			} else if ($ligne->getAvoir()){
				if($ligne->getAvoir()->getType() == "CLIENT"){
					$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getAvoir()->getFacture()->getCompte());
				} else {
					$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getAvoir()->getDepense()->getCompte());
				}
			}
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDebit());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCredit());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCommentaire());
	  	}

		//set column width
		foreach(range('A','H') as $col) {
    		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}

		$response = new Response();

		$response->headers->set('Content-Type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', 'attachment;filename="journal_od.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');
		$response->sendHeaders();
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();
	}


}