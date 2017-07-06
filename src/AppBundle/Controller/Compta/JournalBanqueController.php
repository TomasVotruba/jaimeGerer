<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Compta\Rapprochement;
use AppBundle\Entity\Compta\JournalBanque;
use AppBundle\Entity\Compta\CompteBancaire;

use AppBundle\Form\Compta\JournalBanqueCorrectionType;

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;

class JournalBanqueController extends Controller
{
	/**
	 * @Route("/compta/journal-banque",
	 *   name="compta_journal_banque_index"
	 * )
	 */
	public function indexAction()
	{
		/*creation du dropdown pour choisir le compte bancaire*/
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$arr_comptesBancaires = $repo->findByCompany($this->getUser()->getCompany());

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
		$formBuilder->add('comptes', 'entity', array(
				'required' => true,
				'class' => 'AppBundle:Compta\CompteBancaire',
				'label' => 'Compte bancaire',
				'choices' => $arr_comptesBancaires,
				'attr' => array('class' => 'compte-select')
		))
							->add('years', 'choice', array(
				'required' => true,
				'label' => 'Année',
				'choices' => $arr_years,
				'attr' => array('class' => 'year-select'),
				'data' => $currentYear
		));

		return $this->render('compta/journal_banque/compta_journal_banque_index.html.twig', array(
			'form' => $formBuilder->getForm()->createView()
		));
	}

	/**
	 * @Route("/compta/journal-banque/voir/{id}/{year}",
	 *   name="compta_journal_banque_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalBanqueVoirAction(CompteBancaire $compteBancaire, $year)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
		$arr_journalBanque = $repo->findJournalEntier($this->getUser()->getCompany(), $compteBancaire, $year);

		$arr_totaux = array(
				'debit' => 0,
				'credit' => 0
		);

		foreach($arr_journalBanque as $ligne){
			$arr_totaux['debit']+=$ligne->getDebit();
			$arr_totaux['credit']+=$ligne->getCredit();
		}

		return $this->render('compta/journal_banque/compta_journal_banque_voir.html.twig', array(
				'arr_journalBanque' => $arr_journalBanque,
				'arr_totaux' => $arr_totaux
		));

	}

	/**
	 * @Route("/compta/journal-banque/ajouter/{type}/{id}", name="compta_journal_banque_ajouter")
	 */
	public function journalBanqueAjouterAction($type, Rapprochement $rapprochementBancaire){

		$em = $this->getDoctrine()->getManager();
		try{
			switch($type){

				case 'AFFECTATION-DIVERSE-ACHAT':
					//credit au compte 512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit(-($rapprochementBancaire->getMouvementBancaire()->getMontant()));
					$ligne->setAnalytique(null);
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					//debit au compte xxxxxx (selon le compte rattaché à l'affectation)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(-($rapprochementBancaire->getMouvementBancaire()->getMontant()));
					$ligne->setCredit(null);
					$ligne->setAnalytique(null);
					$ligne->setCompteComptable($rapprochementBancaire->getAffectationDiverse()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					break;

				case 'AFFECTATION-DIVERSE-VENTE':
					//credit au compte xxxxxx (selon le compte rattaché à l'affectation)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($rapprochementBancaire->getMouvementBancaire()->getMontant());
					$ligne->setAnalytique(null);
					$ligne->setCompteComptable($rapprochementBancaire->getAffectationDiverse()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);


					//debit au compte 512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getMouvementBancaire()->getMontant());
					$ligne->setCredit(null);
					$ligne->setAnalytique(null);
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					break;

				case 'DEPENSE':
					//credit au compte  512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($rapprochementBancaire->getDepense()->getTotalTTC());
					$ligne->setAnalytique($rapprochementBancaire->getDepense()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$ligne->setModePaiement($rapprochementBancaire->getDepense()->getModePaiement());
					$em->persist($ligne);

					//debit au compte 401xxxx (compte du fournisseur)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getDepense()->getTotalTTC());
					$ligne->setCredit(null);
					$ligne->setAnalytique($rapprochementBancaire->getDepense()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getDepense()->getCompte()->getCompteComptableFournisseur());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$ligne->setModePaiement($rapprochementBancaire->getDepense()->getModePaiement());
					$em->persist($ligne);

					break;

				case 'FACTURE':
					//credit au compte  411xxxx (compte du client)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($rapprochementBancaire->getFacture()->getTotalTTC());
					$ligne->setAnalytique($rapprochementBancaire->getFacture()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getFacture()->getCompte()->getCompteComptableClient());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					//debit au compte 512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getFacture()->getTotalTTC());
					$ligne->setCredit(null);
					$ligne->setAnalytique($rapprochementBancaire->getFacture()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					break;

				case 'AVOIR-FOURNISSEUR':
					//credit au compte  401xxxx (compte du fournisseur)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($rapprochementBancaire->getAvoir()->getTotalTTC());
					$ligne->setAnalytique($rapprochementBancaire->getAvoir()->getDepense()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					//debit au compte 512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getAvoir()->getTotalTTC());
					$ligne->setCredit(null);
					$ligne->setAnalytique($rapprochementBancaire->getAvoir()->getDepense()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					break;

				case 'AVOIR-CLIENT':
					//credit au compte  512xxxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($rapprochementBancaire->getAvoir()->getTotalTTC());
					$ligne->setAnalytique($rapprochementBancaire->getAvoir()->getFacture()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					//debit au compte 411xxxx (compte du client)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getAvoir()->getTotalTTC());
					$ligne->setCredit(null);
					$ligne->setAnalytique($rapprochementBancaire->getAvoir()->getFacture()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getAvoir()->getFacture()->getCompte()->getCompteComptableClient());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					break;

				case 'REMISE-CHEQUES':
					//credit au compte  411xxxx (compte du client) pour chaque facture
					foreach($rapprochementBancaire->getRemiseCheque()->getCheques() as $cheque){
						foreach($cheque->getPieces() as $piece){
							$ligne = new JournalBanque();
							$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
							$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
							$ligne->setDebit(null);
							if($piece->getFacture() != null){
								$ligne->setCredit($piece->getFacture()->getTotalTTC());
								$ligne->setAnalytique($piece->getFacture()->getAnalytique());
								$ligne->setCompteComptable($piece->getFacture()->getCompte()->getCompteComptableClient());
								$ligne->setFacture($piece->getFacture());
								$ligne->setNom('Paiement facture '.$piece->getFacture()->getNum());
							} else if($piece->getAvoir() != null){
								$ligne->setCredit($piece->getAvoir()->getTotalTTC());
								$ligne->setAnalytique($piece->getAvoir()->getDepense()->getAnalytique());
								$ligne->setCompteComptable($piece->getAvoir()->getDepense()->getCompte()->getCompteComptableClient());
								$ligne->setNom('Avoir '.$piece->getAvoir()->getNum());
								$ligne->setAvoir($piece->getAvoir());
							}
							$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
							$ligne->setModePaiement('CHEQUE');
								$em->persist($ligne);
						}
					}

					//debit au compte 512xxxx (selon banque) pour le montant total de la remise de chèque
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getRemiseCheque()->getTotalTTC());
					$ligne->setCredit(null);
					//$ligne->setAnalytique($rapprochementBancaire->getRemiseCheque()->getAnalytique());
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$ligne->setModePaiement('CHEQUE');
					$em->persist($ligne);

					break;

				case 'NOTE-FRAIS':
					foreach($rapprochementBancaire->getNoteFrais()->getDepenses() as $depense){
						//credit au compte  512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
						$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit($depense->getTotalTTC());
						$ligne->setAnalytique($depense->getAnalytique());
						$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
						$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
						$ligne->setModePaiement($depense->getModePaiement());
						$em->persist($ligne);

						//debit au compte 401xxxx (compte du fournisseur)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
						$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
						$ligne->setDebit($depense->getTotalTTC());
						$ligne->setCredit(null);
						$ligne->setAnalytique($depense->getAnalytique());
						$ligne->setCompteComptable($rapprochementBancaire->getNoteFrais()->getCompteComptable());
						$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
						$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
						$ligne->setModePaiement($depense->getModePaiement());
						$em->persist($ligne);
					}
					break;
				}
				$em->flush();

		} catch (\Exception $e){
			throw $e;
			$response = new Response();
			$response->setStatusCode(500);
			return $response;
		}

		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}



	/**
	 * @Route("/compta/journal-banque/reinitialiser", name="compta_journal_banque_reinitialiser")
	 */
	public function journalBanqueReinitialiser(){

		$em = $this->getDoctrine()->getManager();
		$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');
		$rapprochementRepo = $em->getRepository('AppBundle:Compta\Rapprochement');
		$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$journalBanqueService = $this->container->get('appbundle.compta_journal_banque_controller');

		$arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());
		foreach($arr_comptesBancaires as $compteBancaire){
			$arr_journal = $journalBanqueRepo->findJournalEntier($this->getUser()->getCompany(), $compteBancaire);
			foreach($arr_journal as $ligne){
				$em->remove($ligne);
			}
		}
		$em->flush();

		$arr_rapprochements = $rapprochementRepo->findForCompany($this->getUser()->getCompany());
		foreach($arr_rapprochements as $rapprochement){

			$type = "";
			if($rapprochement->getFacture()){
				$type = "FACTURE";
			} else if($rapprochement->getDepense()){
				$type = "DEPENSE";
			} else if($rapprochement->getAvoir()){
				if($rapprochement->getAvoir()->getType() == 'CLIENT'){
					$type = "AVOIR-CLIENT";
				} else {
					$type = "AVOIR-FOURNISSEUR";
				}
			} else if($rapprochement->getAccompte()){
				$type = "ACCOMPTE";
			} else if($rapprochement->getRemiseCheque()){
				$type = "REMISE-CHEQUES";
			} else if($rapprochement->getAffectationDiverse()){
				if($rapprochement->getAffectationDiverse()->getType() == 'VENTE'){
					$type = "AFFECTATION-DIVERSE-VENTE";
				} else {
					$type = "AFFECTATION-DIVERSE-ACHAT";
				}
			} else if($rapprochement->getNoteFrais()){
				$type = "NOTE-FRAIS";
			}

			if($type != ""){
				//ecrire dans le journal de banque
				$journalBanqueService->journalBanqueAjouterAction($type, $rapprochement);
			}

		}

		return new Response;

	}

	/**
	 * @Route("/compta/journal-banque/exporter/{id}/{year}",
	 *   name="compta_journal_banque_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function journalBanqueExporterAction(CompteBancaire $compteBancaire, $year){
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
		$arr_journalBanque = $repo->findJournalEntier($this->getUser()->getCompany(), $compteBancaire, $year);

		$arr_totaux = array(
			'debit' => 0,
			'credit' => 0
		);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->setTitle('Journal Banque '.$year);

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

		foreach($arr_journalBanque as $ligne){
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
		 $response->headers->set('Content-Disposition', 'attachment;filename="journal_banque.xlsx"');
		 $response->headers->set('Cache-Control', 'max-age=0');
		 $response->sendHeaders();
		 $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		 $objWriter->save('php://output');
		 exit();

	}


}
