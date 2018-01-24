<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LettrageController extends Controller
{
	
	/**
	 * @Route("/compta/lignes-non-lettrees", name="compta_lignes_non_lettrees")
	 */
	public function comptesNonLettresAction(){

		$arr_years = array();
		$currentYear = date('Y');
		for($year = 2016; $year <= $currentYear; $year++ ){
			$arr_years[$year] = $year;
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

		return $this->render('compta/lettrage/compta_lignes_non_lettrees.html.twig', array(
			'form' => $form->createView()
		));
	
	}

	/**
	 * @Route("/compta/lignes-non-lettrees-voir-annee/{year}",
	 *  name="compta_lignes_non_lettrees_voir_annee",
	 *  options={"expose"=true}
	 * )
	 */
	public function comptesNonLettresVoirAnneesAction($year){

		$lettrageService = $this->get('appbundle.compta_lettrage_service');

		$start = new \DateTime($year.'-01-01');
    	$end = new \DateTime($year.'-12-31');

		$repoJournalVente = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
		$arr_journal_vente = $repoJournalVente->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoJournalAchat = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalAchat');
		$arr_journal_achat = $repoJournalAchat->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoJournalBanque = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
		$arr_journal_banque = $repoJournalBanque->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		$repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_operation_diverse = $repoOperationDiverse->findNonLettreesByCompanyAndYear($this->getUser()->getCompany(), $year);

		//regroupement dans 1 seul array
		$arr_lignes = array_merge($arr_journal_vente, $arr_journal_achat, $arr_journal_banque, $arr_operation_diverse);

		$arr_non_lettrees = array();
		foreach($arr_lignes as $ligne){
			
			if(!array_key_exists($ligne->getCompteComptable()->getNum(), $arr_non_lettrees)){
				$arr_non_lettrees[$ligne->getCompteComptable()->getNum()] = array(
					'compteComptable' => $ligne->getCompteComptable(),
					'lettre' => $lettrageService->findNextNum($ligne->getCompteComptable(), $year),
					'lignes' => array()
				);
			}

			$arr_non_lettrees[$ligne->getCompteComptable()->getNum()]['lignes'][] = $ligne;

		}

		ksort($arr_non_lettrees);

		return $this->render('compta/lettrage/compta_lignes_non_lettrees_annee.html.twig', array(
			'arr_non_lettrees' => $arr_non_lettrees,
		));
	
	}

	/**
	 * @Route("/compta/lettrage-divers", name="compta_lettrage_divers")
	 */
	public function lettrageDiversAction(){
		$em = $this->getDoctrine()->getManager();
		$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
		$rapprochementRepo = $em->getRepository('AppBundle:Compta\Rapprochement');
		$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');

		$cc = $ccRepo->find(8003);

		$arr_rapprochements = $rapprochementRepo->findForCompanyByYear($this->getUser()->getCompany(), 2017);

		foreach($arr_rapprochements as $rapprochement){
			if($rapprochement->getDepense() == null){
				continue;
			}
			$depense = $rapprochement->getDepense();

			$ligneAchat = $journalAchatRepo->findOneBy(array(
				'depense' => $depense,
				'compteComptable' => $cc
			));

			$ligneBanque = $journalBanqueRepo->findOneBy(array(
				'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
				'compteComptable' => $cc
			));

			if($ligneAchat && $ligneBanque){

				//if($ligneAchat->getLettrage() == null && $ligneBanque->getLettrage() == null){

					$lettrage = $lettrageService->findNextNum($cc, 2017);
					return 0;
					$ligneAchat->setLettrage($lettrage);
					$em->persist($ligneAchat);
					$ligneBanque->setLettrage($lettrage);
					$em->persist($ligneBanque);
					$em->flush();

					
				//}
				
			} 

		}	

		return new Response();

	}

}
