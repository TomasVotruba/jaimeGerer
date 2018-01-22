<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Form\Compta\OperationDiverseType;

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
	 * @Route("/compta/journal-od/exporter/{year}",
	 *   name="compta_journal_od_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalODExporterAction($year){

	}


}