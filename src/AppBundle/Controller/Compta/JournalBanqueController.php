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
		$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$journalAchatsRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');

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
					$lettrage = $lettrageService->findNextNum($rapprochementBancaire->getDepense()->getCompte()->getCompteComptableFournisseur());
					$ligne->setLettrage($lettrage);
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$ligne->setModePaiement($rapprochementBancaire->getDepense()->getModePaiement());
					$em->persist($ligne);

					$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
						'depense' => $rapprochementBancaire->getDepense(),
						'compteComptable' => $rapprochementBancaire->getDepense()->getCompte()->getCompteComptableFournisseur()
					));
					$ligneJournalAchats->setLettrage($lettrage);
					$em->persist($ligneJournalAchats);

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
					$lettrage = $lettrageService->findNextNum($rapprochementBancaire->getFacture()->getCompte()->getCompteComptableClient());
					$ligne->setLettrage($lettrage);
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

					$ligneJournalVente = $journalVenteRepo->findOneBy(array(
						'facture' => $rapprochementBancaire->getFacture(),
						'compteComptable' => $rapprochementBancaire->getFacture()->getCompte()->getCompteComptableClient()
					));
					$ligneJournalVente->setLettrage($lettrage);
					$em->persist($ligneJournalVente);

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
					$lettrage = $lettrageService->findNextNum($rapprochementBancaire->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur());
					$ligne->setLettrage($lettrage);
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setLettrage($ligneJournalAchats->getLettrage());
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

					$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
						'avoir' => $rapprochementBancaire->getAvoir(),
						'compteComptable' => $rapprochementBancaire->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur()
					));
					$ligneJournalAchats->setLettrage($lettrage);
					$em->persist($ligneJournalAchats);

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
					$lettrage = $lettrageService->findNextNum($rapprochementBancaire->getAvoir()->getFacture()->getCompte()->getCompteComptableClient());
					$ligne->setLettrage($lettrage);
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$em->persist($ligne);

					$ligneJournalVente = $journalVenteRepo->findOneBy(array(
						'avoir' => $rapprochementBancaire->getAvoir(),
						'compteComptable' => $rapprochementBancaire->getAvoir()->getFacture()->getCompte()->getCompteComptableClient()
					));
					$ligneJournalVente->setLettrage($lettrage);
					$em->persist($ligneJournalVente);

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
								$lettrage = $lettrageService->findNextNum($piece->getFacture()->getCompte()->getCompteComptableClient());
								$ligne->setLettrage($lettrage);
								$ligne->setFacture($piece->getFacture());
								$ligne->setNom('Paiement facture '.$piece->getFacture()->getNum());

								$ligneJournalVente = $journalVenteRepo->findOneBy(array(
									'facture' => $piece->getFacture(),
									'compteComptable' => $piece->getFacture()->getCompte()->getCompteComptableClient()
								));
								$ligneJournalVente->setLettrage($lettrage);
								$em->persist($ligneJournalVente);

							} else if($piece->getAvoir() != null){
								$ligne->setCredit($piece->getAvoir()->getTotalTTC());
								$ligne->setAnalytique($piece->getAvoir()->getDepense()->getAnalytique());
								$ligne->setCompteComptable($piece->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur());
								$lettrage = $lettrageService->findNextNum($piece->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur());
								$ligne->setLettrage($lettrage);
								$ligne->setNom('Avoir '.$piece->getAvoir()->getNum());
								$ligne->setAvoir($piece->getAvoir());

								$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
									'avoir' => $piece->getAvoir(),
									'compteComptable' => $piece->getAvoir()->getDepense()->getCompte()->getCompteComptableFournisseur()
								));
								$ligneJournalAchats->setLettrage($lettrage);
								$em->persist($ligneJournalAchats);
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
						$lettrage = $lettrageService->findNextNum($rapprochementBancaire->getNoteFrais()->getCompteComptable());
						$ligne->setLettrage($lettrage);
						$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
						$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
						$ligne->setModePaiement($depense->getModePaiement());
						$em->persist($ligne);

						$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
							'depense' => $depense,
							'compteComptable' => $rapprochementBancaire->getNoteFrais()->getCompteComptable()
						));
						$ligneJournalAchats->setLettrage($lettrage);
						$em->persist($ligneJournalAchats);
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
	 * @Route("/compta/journal-banque/ajouter-plusieurs-pieces-meme-compte", name="compta_journal_banque_ajouter_plusieurs_pieces_meme_compte")
	 */
	public function journalBanqueAjouterPlusieursPiecesMemeCompteAction($mouvementBancaire, $arr_pieces){

		$em = $this->getDoctrine()->getManager();
		$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');

		$analytique = '';
		$modePaiement = '';
		foreach($arr_pieces as $arr_piece){
			foreach($arr_piece as $type => $piece){
				$analytique.= $piece->getTotalTTC();
				$analytique.= '€ ';
				$analytique.= $piece->getAnalytique()->getValeur();
				$analytique.= ', ';

				if($type == "DEPENSES"){
					$modePaiement.= $piece->getTotalTTC();
					$modePaiement.= '€ ';
					$modePaiement.= $piece->getModePaiement();
					$modePaiement.= ', ';
				}
		
			}
		}

		try{
			switch($type){

				case 'FACTURES':

					//credit au compte  411xxxx (compte du client)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($mouvementBancaire);
					$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($mouvementBancaire->getMontant());
					$ligne->setAnalytique(null);
					$ligne->setStringAnalytique($analytique);
					$ligne->setCompteComptable($piece->getCompte()->getCompteComptableClient());
					$lettrage = $lettrageService->findNextNum($piece->getCompte()->getCompteComptableClient());
					$ligne->setLettrage($lettrage);
					$ligne->setNom($mouvementBancaire->getLibelle());
					$ligne->setDate($mouvementBancaire->getDate());
					$em->persist($ligne);

					//debit au compte 512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($mouvementBancaire);
					$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
					$ligne->setDebit($mouvementBancaire->getMontant());
					$ligne->setCredit(null);
					$ligne->setAnalytique(null);
					$ligne->setStringAnalytique($analytique);
					$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($mouvementBancaire->getLibelle());
					$ligne->setDate($mouvementBancaire->getDate());
					$em->persist($ligne);

					foreach($arr_pieces as $arr_piece){
						foreach($arr_piece as $type => $piece){
							$ligneJournalVente = $journalVenteRepo->findOneBy(array(
								'facture' => $piece,
								'compteComptable' => $piece->getCompte()->getCompteComptableClient()
							));
							$ligneJournalVente->setLettrage($lettrage);
							$em->persist($ligneJournalVente);
						}
					}

					break;

				case 'DEPENSES':

					//credit au compte  512xxxx (selon banque)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($mouvementBancaire);
					$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
					$ligne->setDebit(null);
					$ligne->setCredit($mouvementBancaire->getMontant());
					$ligne->setAnalytique(null);
					$ligne->setStringAnalytique($analytique);
					$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($mouvementBancaire->getLibelle());
					$ligne->setDate($mouvementBancaire->getDate());
					$ligne->setModePaiement($modePaiement);
					$em->persist($ligne);

					//debit au compte 401xxxx (compte du fournisseur)
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($mouvementBancaire);
					$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
					$ligne->setDebit(-$mouvementBancaire->getMontant());
					$ligne->setCredit(null);
					$ligne->setAnalytique(null);
					$ligne->setStringAnalytique($analytique);
					$ligne->setCompteComptable($piece->getCompte()->getCompteComptableFournisseur());
					$lettrage = $lettrageService->findNextNum($piece->getCompte()->getCompteComptableFournisseur());
					$ligne->setLettrage($lettrage);
					$ligne->setNom($mouvementBancaire->getLibelle());
					$ligne->setDate($mouvementBancaire->getDate());
					$ligne->setModePaiement($modePaiement);
					$em->persist($ligne);

					foreach($arr_pieces as $arr_piece){
						foreach($arr_piece as $type => $piece){
							$ligneJournalAchat = $journalAchatRepo->findOneBy(array(
								'depense' => $piece,
								'compteComptable' => $piece->getCompte()->getCompteComptableFournisseur()
							));
							$ligneJournalAchat->setLettrage($lettrage);
							$em->persist($ligneJournalAchat);
						}
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

	/**
	 * @Route("/compta/lettrage2017",
	 *   name="compta_lettrage_2017"
	 * )
	 */
	public function lettrage2017(){

		$em = $this->getDoctrine()->getManager();
		$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');
		$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$rapprochementRepo = $em->getRepository('AppBundle:Compta\Rapprochement');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');

		$arr_rapprochements = $rapprochementRepo->findForCompanyByYear($this->getUser()->getCompany(), 2017);

		foreach($arr_rapprochements as $rapprochement){

			/*
			if($rapprochement->getFacture()){

				$facture = $rapprochement->getFacture();

				if($facture->getDateCreation()->format('Y') != 2017){
					continue;
				}

				$cc = $facture->getCompte()->getCompteComptableClient();
				$lettrage = $lettrageService->findNextNum($cc);

				$ligneBanque = $journalBanqueRepo->findOneBy(array(
					'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
					'compteComptable' => $cc
				));

				$ligneVente = $journalVenteRepo->findOneBy(array(
					'facture' => $facture,
					'compteComptable' => $cc
				));

				if($ligneVente && $ligneBanque){
					$ligneVente->setLettrage($lettrage);
					$em->persist($ligneVente);
					$ligneBanque->setLettrage($lettrage);
					$em->persist($ligneBanque);
					$em->flush();

				} 
				
			} 
			*/

			/*
			if ($rapprochement->getAvoir()){
				$avoir = $rapprochement->getAvoir();
				if($avoir->getDateCreation()->format('Y') != 2017){
					continue;
				}

				if($avoir->getDepense()){

					if($avoir->getDateCreation()->format('Y') != 2017){
						continue;
					}

					$cc = $avoir->getDepense();->getCompte()->getCompteComptableFournisseur();
					$lettrage = $lettrageService->findNextNum($cc);

					$ligneBanque = $journalBanqueRepo->findOneBy(array(
						'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
						'compteComptable' => $cc
					));

					$ligneAchat = $journalAchatRepo->findOneBy(array(
						'avoir' => $avoir,
						'compteComptable' => $cc
					));

					if($ligneAchat && $ligneBanque){
						$ligneAchat->setLettrage($lettrage);
						$em->persist($ligneAchat);
						$ligneBanque->setLettrage($lettrage);
						$em->persist($ligneBanque);
						$em->flush();
					} 
				} else if ($avoir->getFacture()){
					
					$cc = $avoir->getFacture()->getCompte()->getCompteComptableClient();
					$lettrage = $lettrageService->findNextNum($cc);

					$ligneBanque = $journalBanqueRepo->findOneBy(array(
						'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
						'compteComptable' => $cc
					));

					$ligneVente = $journalVenteRepo->findOneBy(array(
						'avoir' => $avoir,
						'compteComptable' => $cc
					));

					if($ligneVente && $ligneBanque){
						$ligneVente->setLettrage($lettrage);
						$em->persist($ligneVente);
						$ligneBanque->setLettrage($lettrage);
						$em->persist($ligneBanque);
						$em->flush();

					} 
				}
				
			}
			*/
		}

		return new Response();

	}


}
