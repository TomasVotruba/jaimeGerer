<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\CRM\DocumentPrix;
use AppBundle\Entity\Compta\Avoir;
use AppBundle\Entity\Compta\JournalVente;
use AppBundle\Entity\Compta\CompteComptable;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

class JournalVentesController extends Controller
{
	/**
	 * @Route("/compta/journal-ventes",
	 *   name="compta_journal_ventes_index"
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

		return $this->render('compta/journal_ventes/compta_journal_ventes_index.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/journal-ventes/voir/{year}",
	 *   name="compta_journal_ventes_voir_annee",
	 *   options={"expose"=true}
	 * )
	 */
	public function voirAction($year)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
		$arr_journalVente = $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		$arr_totaux = array(
			'debit' => 0,
			'credit' => 0
		);

		foreach($arr_journalVente as $ligne){
			$arr_totaux['debit']+=$ligne->getDebit();
			$arr_totaux['credit']+=$ligne->getCredit();
		}

		return $this->render('compta/journal_ventes/compta_journal_ventes_voir.html.twig', array(
			'arr_journalVente' => $arr_journalVente,
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/journal-ventes/ajouter/facture/{id}", name="compta_journal_ventes_ajouter_facture")
	 */
	public function journalVentesAjouterFactureAction(DocumentPrix $facture){

		$em = $this->getDoctrine()->getManager();
		$ccRepository = $em->getRepository('AppBundle:Compta\CompteComptable');

		$compteAttente = $ccRepository->findOneBy(array(
			'num' => '471',
			'company' => $this->getUser()->getCompany()
		));

		$totaux = $facture->getTotaux();

		//credit au compte 70xxxxxx : prix HT ou prix net
		$ligne = new JournalVente();
		$ligne->setFacture($facture);
		$ligne->setCodeJournal('VE');
		$ligne->setDebit(null);
		$ligne->setCredit($totaux['HT']);
		$ligne->setAnalytique($facture->getAnalytique());

		$produits = $facture->getProduits();
		$type = "";
		foreach($produits as $produit){
			$type = $produit->getType();
		}

		if($type == null){
			$compteComptable = $compteAttente;
		} else {
			$compteComptable = $type->getCompteComptable();
			if($compteComptable == null){
				$compteComptable = $compteAttente;
			}
		}
		$ligne->setCompteComptable($compteComptable);
		$em->persist($ligne);

		//credit au compte 445xxxxx : TVA
		if($facture->getTaxePercent() != 0){
			$tva = $facture->getTaxePercent()*100;
			$tva.='%';

			$ligne = new JournalVente();
			$ligne->setFacture($facture);
			$ligne->setCodeJournal('VE');
			$ligne->setDebit(null);
			$ligne->setCredit($totaux['HT']*$facture->getTaxePercent());
			$ligne->setAnalytique($facture->getAnalytique());

			$settingsRepository = $em->getRepository('AppBundle:Settings');
			$settings_tva = $settingsRepository->findOneBy(array(
					'company' => $this->getUser()->getCompany()->getId(),
					'parametre' => 'TVA',
					'module' => 'CRM',
					'valeur' => $tva
			));

			if($settings_tva == null){
				$compteComptable = $compteAttente;
			} else {
				$compteComptable = $settings_tva->getCompteComptable();
				if($compteComptable == null){
					$compteComptable = $compteAttente;
				}
			}
			$ligne->setCompteComptable($compteComptable);
			$em->persist($ligne);
		}

		//debit au compte 411xxxxx : TTC
		$ligne = new JournalVente();
		$ligne->setFacture($facture);
		$ligne->setCodeJournal('VE');
		$ligne->setDebit($totaux['TTC']);
		$ligne->setCredit(null);
		$ligne->setAnalytique($facture->getAnalytique());
		$compteComptable = $facture->getCompte()->getCompteComptableClient();
		if($compteComptable == null){
			$compteComptable = $compteAttente;
		}
		$ligne->setCompteComptable($compteComptable);
		$em->persist($ligne);


		$em->flush();
		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}

	/**
	 * @Route("/compta/journal-ventes/ajouter/avoir/{id}", name="compta_journal_ventes_ajouter_avoir")
	 */
	public function journalVentesAjouterAvoirAction(Avoir $avoir){
		//AVOIR CLIENT

		$em = $this->getDoctrine()->getManager();
		$ccRepository = $em->getRepository('AppBundle:Compta\CompteComptable');

		$compteAttente = $ccRepository->findOneBy(array(
				'num' => '471',
				'company' => $this->getUser()->getCompany()
		));

		$tva = $avoir->getFacture()->getTaxePercent()*100;
		$tva.='%';
		$settingsRepository = $em->getRepository('AppBundle:Settings');
		$settings_tva = $settingsRepository->findOneBy(array(
				'company' => $this->getUser()->getCompany()->getId(),
				'parametre' => 'TVA',
				'module' => 'CRM',
				'valeur' => $tva
		));

		if($settings_tva == null){
			$compteTVA = $compteAttente;
		} else {
			$compteTVA = $settings_tva->getCompteComptable();
			if($compteTVA == null){
				$compteTVA = $compteAttente;
			}
		}

		$totaux = $avoir->getTotaux();

		//credit au compte 411xxxx (compte client): TTC
		$ligne = new JournalVente();
		$ligne->setAvoir($avoir);
		$ligne->setCodeJournal('VE');
		$ligne->setDebit(null);
		$ligne->setCredit($totaux['TTC']);
		$ligne->setAnalytique($avoir->getFacture()->getAnalytique());
		$compteComptable = $avoir->getFacture()->getCompte()->getCompteComptableClient();
		if($compteComptable == null){
			$compteComptable = $compteAttente;
		}
		$ligne->setCompteComptable($compteComptable);
		$em->persist($ligne);

		//pour chaque ligne : debit au compte 70xxxx du montant HT
		foreach($avoir->getLignes() as $ligneAvoir){

			$ligne = new JournalVente();
			$ligne->setAvoir($avoir);
			$ligne->setCodeJournal('VE');
			$ligne->setDebit($ligneAvoir->getMontant());
			$ligne->setCredit(null);
			$ligne->setAnalytique($avoir->getFacture()->getAnalytique());
			$ligne->setCompteComptable($ligneAvoir->getCompteComptable());
			$em->persist($ligne);

			//si TVA : debit au compte 445xxxxx
			if($ligneAvoir->getTaxe() != null && $ligneAvoir->getTaxe() != 0){
				$ligne = new JournalVente();
				$ligne->setAvoir($avoir);
				$ligne->setCodeJournal('VE');
				$ligne->setDebit($ligneAvoir->getTaxe());
				$ligne->setCredit(null);
				$ligne->setAnalytique($avoir->getFacture()->getAnalytique());
				$ligne->setCompteComptable($compteTVA);
				$em->persist($ligne);
			}

		}

		$em->flush();
		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}

	/**
	 * @Route("/compta/journal-ventes/exporter/{year}",
	 *   name="compta_journal_ventes_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalVentesExporterAction($year){
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
		$arr_journal = $repo->findJournalEntier($this->getUser()->getCompany(), $year);

		$arr_totaux = array(
			'debit' => 0,
			'credit' => 0
		);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->setTitle('Journal Ventes '.$year);

		// header row
		$arr_header = array(
			'Code journal',
			'Date',
			'Compte',
			'Compte auxiliaire',
			'Libellé',
			'Débit',
			'Crédit',
			'Analytique'
		);
		$row = 1;
		$col = 'A';
		foreach($arr_header as $header){
				$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $header);
				$col++;
		}

		foreach($arr_journal as $ligne){
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
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getLibelle());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getDebit());
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCredit());
			$col++;
			$settingsAnalytique = $ligne->getAnalytique();
			if(!$settingsAnalytique){
				$analytique = "";
			} else {
				$analytique = $settingsAnalytique->getValeur();
			}
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $analytique);
		}

		//set column width
		foreach(range('A','H') as $col) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
		}

		 $response = new Response();
		 $response->headers->set('Content-Type', 'application/vnd.ms-excel');
		 $response->headers->set('Content-Disposition', 'attachment;filename="journal_ventes.xlsx"');
		 $response->headers->set('Cache-Control', 'max-age=0');
		 $response->sendHeaders();
		 $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		 $objWriter->save('php://output');
		 exit();

	}

	/**
	 * @Route("/compta/journal-ventes/reinitialiser", name="compta_journal_ventes_reinitialiser")
	 */
	public function journalVentesReinitialiser(){

		$em = $this->getDoctrine()->getManager();
		$journalVentesRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$factureRepo = $em->getRepository('AppBundle:CRM\DocumentPrix');
		$avoirRepo = $em->getRepository('AppBundle:Compta\Avoir');

		$journalVentesService = $this->container->get('appbundle.compta_journal_ventes_controller');
		$compteComptableService = $this->get('appbundle.compta_compte_comptable_controller');

		$arr_journal = $journalVentesRepo->findJournalEntier($this->getUser()->getCompany());
		foreach($arr_journal as $ligne){
			$em->remove($ligne);
		}
		$em->flush();

		$arr_factures = $factureRepo->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
		foreach($arr_factures as $facture){
			$compte = $facture->getCompte();
			if($compte->getCompteComptableClient() == null || $compte->getClient() == false){

				$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());
				$em->persist($compteComptable);

				$compte->setClient(true);
				$compte->setCompteComptableClient($compteComptable);
				$em->persist($compte);
			}

			//ecrire dans le journal des ventes
			$journalVentesService->journalVentesAjouterFactureAction($facture);

		}

		$arr_avoirs = $avoirRepo->findForCompany('CLIENT', $this->getUser()->getCompany());
		foreach($arr_avoirs as $avoir){
			$compte = $avoir->getFacture()->getCompte();
			if($compte->getCompteComptableClient() == null || $compte->getClient() == false){

				$compteComptable = $compteComptableService->createCompteComptableForCompte('411', $compte->getNom());
				$em->persist($compteComptable);

				$compte->setClient(true);
				$compte->setCompteComptableClient($compteComptable);
				$em->persist($compte);
			}

			//ecrire dans le journal des ventes
			$journalVentesService->journalVentesAjouterAvoirAction($avoir);

		}

		$em->flush();
		return new Response();

	}
}
