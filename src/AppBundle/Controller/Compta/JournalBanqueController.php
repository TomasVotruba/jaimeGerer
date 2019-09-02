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

		try{
			switch($type){

				case 'AFFECTATION-DIVERSE-ACHAT':
					$result = $this->ajouterAffectationDiverseAchat($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAffectationDiverse());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;

				case 'AFFECTATION-DIVERSE-VENTE':
					$result = $this->ajouterAffectationDiverseVente($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAffectationDiverse());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;

				case 'DEPENSE':
					$result = $this->ajouterDepense($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getDepense());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;

				case 'FACTURE':
					$result = $this->ajouterFacture($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getFacture());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;

				case 'AVOIR-FOURNISSEUR':

					$result = $this->ajouterAvoirFournisseur($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAvoir());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;

				case 'AVOIR-CLIENT':

					$result = $this->ajouterAvoirClient($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAvoir());
					if('OK' !== $result){
						$response = new Response();
						$response->setStatusCode(500);
						return $response;
					}

					break;


				case 'REMISE-CHEQUES':
					//credit au compte  411xxxx (compte du client) pour chaque facture
					foreach($rapprochementBancaire->getRemiseCheque()->getCheques() as $cheque){
						foreach($cheque->getPieces() as $piece){
							
							if($piece->getFacture() != null){
								
								$result = $this->ajouterFacture($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getFacture(), false, 'CHEQUE');
								if('OK' !== $result){
									$response = new Response();
									$response->setStatusCode(500);
									return $response;
								}

							} else if($piece->getAvoir() != null){
								
								$result = $this->ajouterAvoirFournisseur($rapprochementBancaire->getMouvementBancaire(), $piece->getAvoir(), false, 'CHEQUE');
								if('OK' !== $result){
									$response = new Response();
									$response->setStatusCode(500);
									return $response;
								}

							} else if($piece->getOperationDiverse() != null){
								
								$result = $this->ajouterOD($rapprochementBancaire->getMouvementBancaire(), $piece->getOperationDiverse(), false, 'CHEQUE');
								if('OK' !== $result){
									$response = new Response();
									$response->setStatusCode(500);
									return $response;
								}

							}
						}
					}

					//debit au compte 512xxxx (selon banque) pour le montant total de la remise de chèque
					$ligne = new JournalBanque();
					$ligne->setMouvementBancaire($rapprochementBancaire->getMouvementBancaire());
					$ligne->setCodeJournal($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getNom());
					$ligne->setDebit($rapprochementBancaire->getRemiseCheque()->getTotalTTC());
					$ligne->setCredit(null);
					$ligne->setCompteComptable($rapprochementBancaire->getMouvementBancaire()->getCompteBancaire()->getCompteComptable());
					$ligne->setNom($rapprochementBancaire->getMouvementBancaire()->getLibelle());
					$ligne->setDate($rapprochementBancaire->getMouvementBancaire()->getDate());
					$ligne->setModePaiement('CHEQUE');
					$ligne->setNumEcriture($numEcriture);
					$em->persist($ligne);

					break;

				case 'NOTE-FRAIS':
					foreach($rapprochementBancaire->getNoteFrais()->getDepenses() as $depense){

						$result = $this->ajouterDepense($rapprochementBancaire->getMouvementBancaire(), $depense, true, $rapprochementBancaire->getNoteFrais());
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}
	
					}
					break;
				}

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
	 * @Route("/compta/journal-banque/ajouter-plusieurs-pieces", name="compta_journal_banque_ajouter_plusieurs_pieces")
	 */
	public function journalBanqueAjouterPlusieursPiecesAction($arr_mouvements, $arr_pieces){

		$em = $this->getDoctrine()->getManager();
		$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');
		$numService = $this->get('appbundle.num_service');

		$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

		$arr_montantsBanque = array();

		foreach($arr_mouvements as $mouvementBancaire){

			if( !array_key_exists($mouvementBancaire->getCompteBancaire()->getId(), $arr_montantsBanque) ){
				$arr_montantsBanque[$mouvementBancaire->getCompteBancaire()->getId()] = $mouvementBancaire->getMontant();
			} else {
				$arr_montantsBanque[$mouvementBancaire->getCompteBancaire()->getId()]+= $mouvementBancaire->getMontant();
			}
			
			//écriture au compte 512xxxx (selon banque)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setAnalytique(null);
			$ligne->setStringAnalytique(null); //TODO
			$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			
			// crédit au 512 si le montant est négatif
			if($mouvementBancaire->getMontant() < 0){
				$ligne->setDebit(null);
				$ligne->setCredit($mouvementBancaire->getMontant());
			} else {
				//debit au 512 si le montant est positif
				$ligne->setDebit($mouvementBancaire->getMontant());
				$ligne->setCredit(null);
			}
			$em->persist($ligne);
		}

		foreach($arr_pieces as $arr_piece){
			foreach($arr_piece as $type => $piece){

				switch($type){

					case 'AFFECTATIONS-DIVERSES-ACHAT':
						$result = $this->ajouterAffectationDiverseAchat($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAffectationDiverse(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;

					case 'AFFECTATIONS-DIVERSES-VENTE':
						$result = $this->ajouterAffectationDiverseVente($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAffectationDiverse(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;

					case 'DEPENSES':
						$result = $this->ajouterDepense($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getDepense(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;

					case 'FACTURES':
						$result = $this->ajouterFacture($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getFacture(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;

					case 'AVOIRS-FOURNISSEUR':

						$result = $this->ajouterAvoirFournisseur($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAvoir(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;

					case 'AVOIRS-CLIENT':

						$result = $this->ajouterAvoirClient($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getAvoir(), false);
						if('OK' !== $result){
							$response = new Response();
							$response->setStatusCode(500);
							return $response;
						}

						break;


					case 'REMISES-CHEQUES':
						//credit au compte  411xxxx (compte du client) pour chaque facture
						foreach($rapprochementBancaire->getRemiseCheque()->getCheques() as $cheque){
							foreach($cheque->getPieces() as $piece){
								
								if($piece->getFacture() != null){
									
									$result = $this->ajouterFacture($rapprochementBancaire->getMouvementBancaire(), $rapprochementBancaire->getFacture(), false, 'CHEQUE');
									if('OK' !== $result){
										$response = new Response();
										$response->setStatusCode(500);
										return $response;
									}

								} else if($piece->getAvoir() != null){
									
									$result = $this->ajouterAvoirFournisseur($rapprochementBancaire->getMouvementBancaire(), $piece->getAvoir(), false, 'CHEQUE');
									if('OK' !== $result){
										$response = new Response();
										$response->setStatusCode(500);
										return $response;
									}

								} else if($piece->getOperationDiverse() != null){
									
									$result = $this->ajouterOD($rapprochementBancaire->getMouvementBancaire(), $piece->getOperationDiverse(), false, 'CHEQUE');
									if('OK' !== $result){
										$response = new Response();
										$response->setStatusCode(500);
										return $response;
									}

								}
							}
						}

						break;

					case 'NOTES-FRAIS':
						foreach($rapprochementBancaire->getNoteFrais()->getDepenses() as $depense){

							$result = $this->ajouterDepense($rapprochementBancaire->getMouvementBancaire(), $depense, false);
							if('OK' !== $result){
								$response = new Response();
								$response->setStatusCode(500);
								return $response;
							}
				
						}
						break;
				} //end switch

			} //end foreach($arr_piece as $type => $piece){

		} // end foreach($arr_pieces as $arr_piece){

		$response = new Response();
		$response->setStatusCode(200);
		return $response;

	}


	/**
	 * @Route("/compta/journal-banque/ajouter-plusieurs-pieces-meme-compte", name="compta_journal_banque_ajouter_plusieurs_pieces_meme_compte")
	 */
	public function journalBanqueAjouterPlusieursPiecesMemeCompteAction($arr_mouvements, $arr_pieces){

		$em = $this->getDoctrine()->getManager();
		$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
		$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
		$lettrageService = $this->get('appbundle.compta_lettrage_service');
		$numService = $this->get('appbundle.num_service');

		$arr_annees = array();
		$arr_types = array();

		$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

		//on créé une chaine de caractère qui décrit les postes analytiques par montant
		$arr_analytiques = array();
		$analytique = '';

		//on créé une chaine de caractère qui décrit les modes de paiement par montant
		$modePaiement = '';

		//on écrit le libellé à partir du montant de chaque poste analytique
		foreach($arr_pieces as $arr_piece){
			foreach($arr_piece as $type => $piece){

				if($type != 'AFFECTATIONS-DIVERSES-VENTE' && $type != 'AFFECTATIONS-DIVERSES-ACHAT'){
					
					$montant= $piece->getTotalTTC();
					if($montant < 0){
						$montant = -$montant;
					}
					if( array_key_exists($type, $arr_types) ){
						$arr_types[$type]+= $montant;
					} else {
						$arr_types[$type] = $montant;
					}
					
					if($type == "DEPENSES"){


						$analytique.= $piece->getTotalTTC();
						$analytique.= '€ ';
						$analytique.= $piece->getAnalytique()->getValeur();
						$analytique.= ', ';

						$modePaiement.= $piece->getTotalTTC();
						$modePaiement.= '€ ';
						$modePaiement.= $piece->getModePaiement();
						$modePaiement.= ', ';

						if(!in_array($piece->getDate()->format('Y'), $arr_annees)){
							$arr_annees[] = $piece->getDate()->format('Y');
						}
					}

					if($type == "FACTURES"){

						$analytique.= $piece->getTotalTTC();
						$analytique.= '€ ';
						$analytique.= $piece->getAnalytique()->getValeur();
						$analytique.= ', ';

						if(!in_array($piece->getDateCreation()->format('Y'), $arr_annees)){
							$arr_annees[] = $piece->getDateCreation()->format('Y');
						}
					}

					if($type == "NOTES-FRAIS"){

						if(!in_array($piece->getDateCreation()->format('Y'), $arr_annees)){
							$arr_annees[] = $piece->getDateCreation()->format('Y');
						}

						foreach($piece->getDepenses() as $depense){
							if( array_key_exists($depense->getAnalytique()->getValeur(), $arr_analytiques) ){
								$arr_analytiques[$depense->getAnalytique()->getValeur()]+= $depense->getTotalTTC();
							} else {
								$arr_analytiques[$depense->getAnalytique()->getValeur()]= $depense->getTotalTTC();
							}
						}

					}

					if($type == "AVOIRS-FOURNISSEUR"){

						$analytique.= $piece->getTotalTTC();
						$analytique.= '€ ';
						$analytique.= $piece->getDepense()->getAnalytique()->getValeur();
						$analytique.= ', ';

						if(!in_array($piece->getDateCreation()->format('Y'), $arr_annees)){
							$arr_annees[] = $piece->getDateCreation()->format('Y');
						}
					}

					if($type == "AVOIRS-CLIENT"){

						$analytique.= $piece->getTotalTTC();
						$analytique.= '€ ';
						$analytique.= $piece->getFacture()->getAnalytique()->getValeur();
						$analytique.= ', ';

						if(!in_array($piece->getDateCreation()->format('Y'), $arr_annees)){
							$arr_annees[] = $piece->getDateCreation()->format('Y');
						}
					}
				}

			}

		}

		if($type == "NOTES-FRAIS"){
			foreach($arr_analytiques as $analytiqueNDF => $montant){
				$analytique.= $montant;
				$analytique.= '€ ';
				$analytique.= $analytiqueNDF;
				$analytique.= ', ';

			}
		}

		//on cherche les différentes années des mouvements, pour le lettrage (par exemple 2017-2018 A)
		foreach($arr_mouvements as $mouvement){
			if(!in_array($mouvement->getDate()->format('Y'), $arr_annees)){
				$arr_annees[] = $mouvement->getDate()->format('Y');
			}
		}

		if($type != 'AFFECTATIONS-DIVERSES-VENTE' && $type != 'AFFECTATIONS-DIVERSES-ACHAT'){
			//si on a plusieurs types de pièces (par exemple depense+avoir), on utilise celle qui a le plus gros montant
			$maxs = array_keys($arr_types, max($arr_types));
			$type = $maxs[0];
			foreach($arr_pieces as $arr_piece){
				if( array_key_exists( $type, $arr_piece ) ){
					$piece = $arr_piece[$type];
				}
			}
		}

		//écrire les lignes du journal de banque
		try{
			switch($type){

				case 'FACTURES':
					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}
					$lettre = $lettrageService->findNextNum($piece->getCompte()->getCompteComptableClient());		
					$lettrage = $prefixe.$lettre;

					foreach($arr_mouvements as $mouvementBancaire){

						//credit au compte  411xxxx (compte du client)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit($mouvementBancaire->getMontant());
						$ligne->setAnalytique(null);
						$ligne->setStringAnalytique($analytique);
						$ligne->setCompteComptable($piece->getCompte()->getCompteComptableClient());
						$ligne->setLettrage($lettrage);
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
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
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}

					break;

				case 'REMISES-CHEQUES':
					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}

					break;

				case 'DEPENSES':

					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}
					$lettre = $lettrageService->findNextNum($piece->getCompte()->getCompteComptableFournisseur());	
					$lettrage = $prefixe.$lettre;

					foreach($arr_mouvements as $mouvementBancaire){

						//credit au compte  512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit(-$mouvementBancaire->getMontant());
						$ligne->setAnalytique(null);
						$ligne->setStringAnalytique($analytique);
						$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setModePaiement($modePaiement);
						$ligne->setNumEcriture($numEcriture);
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
						$ligne->setLettrage($lettrage);
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setModePaiement($modePaiement);
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}
	
					break;

				case 'NOTES-FRAIS':
		
					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}
					$lettre = $lettrageService->findNextNum($piece->getCompteComptable());	
					$lettrage = $prefixe.$lettre;

					foreach($arr_mouvements as $mouvementBancaire){
			
						//credit au compte  512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit(-$mouvementBancaire->getMontant());
						$ligne->setAnalytique(null);
						$ligne->setStringAnalytique($analytique);
						$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setModePaiement(null);
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

						//debit au compte 421xxxx (compte NDF du salarié)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(-$mouvementBancaire->getMontant());
						$ligne->setCredit(null);
						$ligne->setAnalytique(null);
						$ligne->setStringAnalytique($analytique);
						$ligne->setCompteComptable($piece->getCompteComptable());
						$ligne->setLettrage($lettrage);
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setModePaiement($modePaiement);
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}

					break;

				case 'AVOIRS-FOURNISSEUR':

					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}
					$lettre = $lettrageService->findNextNum($piece->getDepense()->getCompte()->getCompteComptableFournisseur());	
					$lettrage = $prefixe.$lettre;

					foreach($arr_mouvements as $mouvementBancaire){
						//credit au compte  401xxxx (compte du fournisseur)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit($mouvementBancaire->getMontant());
						$ligne->setAnalytique(null);
						$ligne->setStringAnalytique($analytique);
						$ligne->setCompteComptable($piece->getDepense()->getCompte()->getCompteComptableFournisseur());
						$ligne->setLettrage($lettrage);
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

						//debit au compte 512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit($mouvementBancaire->getMontant());
						$ligne->setCredit(null);
						$ligne->setAnalytique($piece->getDepense()->getAnalytique());
						$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

					}

					break;

				case 'AVOIRS-CLIENT':

					$prefixe = '';
					if(count($arr_annees) > 1){
						foreach($arr_annees as $annee){
							$prefixe.= $annee;
							$prefixe.=' ';
						}
					}
					$lettre = $lettrageService->findNextNum($piece->getFacture()->getCompte()->getCompteComptableClient());	
					$lettrage = $prefixe.$lettre;

					foreach($arr_mouvements as $mouvementBancaire){

						//credit au compte  512xxxxx (selon banque)
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
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

						//debit au compte 411xxxx (compte du client)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit($mouvementBancaire->getMontant());
						$ligne->setCredit(null);
						$ligne->setAnalytique($piece->getFacture()->getAnalytique());
						$ligne->setCompteComptable($piece->getFacture()->getCompte()->getCompteComptableClient());
						$ligne->setLettrage($lettrage);
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}
					break;

				case 'AFFECTATIONS-DIVERSES-VENTE':

					foreach($arr_mouvements as $mouvementBancaire){
						//credit au compte xxxxxx (selon le compte rattaché à l'affectation)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit($mouvementBancaire->getMontant());
						$ligne->setAnalytique(null);
						$ligne->setCompteComptable($piece->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

						//debit au compte 512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit($mouvementBancaire->getMontant());
						$ligne->setCredit(null);
						$ligne->setAnalytique(null);
						$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}
					break;

				case 'AFFECTATIONS-DIVERSES-ACHAT':

					foreach($arr_mouvements as $mouvementBancaire){
						//credit au compte 512xxxx (selon banque)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(null);
						$ligne->setCredit(-($mouvementBancaire->getMontant()));
						$ligne->setAnalytique(null);
						$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);

						//debit au compte xxxxxx (selon le compte rattaché à l'affectation)
						$ligne = new JournalBanque();
						$ligne->setMouvementBancaire($mouvementBancaire);
						$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
						$ligne->setDebit(-($mouvementBancaire->getMontant()));
						$ligne->setCredit(null);
						$ligne->setAnalytique(null);
						$ligne->setCompteComptable($piece->getCompteComptable());
						$ligne->setNom($mouvementBancaire->getLibelle());
						$ligne->setDate($mouvementBancaire->getDate());
						$ligne->setNumEcriture($numEcriture);
						$em->persist($ligne);
					}
					break;
			}

			//lettrage des lignes existantes du journal de vente ou d'achat
			foreach($arr_pieces as $arr_piece){
				foreach($arr_piece as $type => $piece){

					switch($type){

						case 'FACTURES':
							$ligneJournalVente = $journalVenteRepo->findOneBy(array(
								'facture' => $piece,
								'compteComptable' => $piece->getCompte()->getCompteComptableClient()
							));
							$ligneJournalVente->setLettrage($lettrage);
							$em->persist($ligneJournalVente);
							break;

						case 'DEPENSES':
							$ligneJournalAchat = $journalAchatRepo->findOneBy(array(
								'depense' => $piece,
								'compteComptable' => $piece->getCompte()->getCompteComptableFournisseur()
							));
							$ligneJournalAchat->setLettrage($lettrage);
							$em->persist($ligneJournalAchat);
							break;

						case 'NOTES-FRAIS':
							foreach($piece->getDepenses() as $depense){
								$ligneJournalAchats = $journalAchatRepo->findOneBy(array(
									'depense' => $depense,
									'compteComptable' => $piece->getCompteComptable()
								));
								$ligneJournalAchats->setLettrage($lettrage);
								$em->persist($ligneJournalAchats);
							}
							break;

						case 'AVOIRS-FOURNISSEUR':
							$ligneJournalAchats = $journalAchatRepo->findOneBy(array(
								'avoir' => $piece,
								'compteComptable' => $piece->getDepense()->getCompte()->getCompteComptableFournisseur()
							));
							$ligneJournalAchats->setLettrage($lettrage);
							$em->persist($ligneJournalAchats);
							break;

						case 'AVOIRS-CLIENT':
							$ligneJournalVente = $journalVenteRepo->findOneBy(array(
								'avoir' => $piece,
								'compteComptable' => $piece->getFacture()->getCompte()->getCompteComptableClient()
							));
							$ligneJournalVente->setLettrage($lettrage);
							$em->persist($ligneJournalVente);
							break;

					}

				}
			}
			
			$em->flush();

			//num pour le FEC
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			$response = new Response();
			$response->setStatusCode(500);
			return $response;
		}

		$response = new Response();
		$response->setStatusCode(200);
		return $response;

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
			'Analytique',
			'Commentaire'
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
			$libelle =  preg_replace('/[\r\n]+/',' - ', $ligne->getLibelle());
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $libelle);
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
			$col++;
			$objPHPExcel->getActiveSheet ()->setCellValue ($col.$row, $ligne->getCommentaire());
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

	private function ajouterFacture($mouvementBancaire, $facture, $ecrireLigneBanque = true, $modePaiement = null){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//récupération du prochain numéro de lettrage pour le compte comptable client
			$lettrageService = $this->get('appbundle.compta_lettrage_service');
			$lettrage = $lettrageService->findNextNum($facture->getCompte()->getCompteComptableClient());

			//credit au compte  411xxxx (compte du client)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit(null);
			$ligne->setCredit($facture->getTotalTTC());
			$ligne->setAnalytique($facture->getAnalytique());
			$ligne->setCompteComptable($facture->getCompte()->getCompteComptableClient());
			$ligne->setLettrage($lettrage);
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			if(null !== $modePaiement){
				$ligne->setModePaiement($modePaiement);
			}
			$em->persist($ligne);

			if(true === $ecrireLigneBanque){
				//debit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit($facture->getTotalTTC());
				$ligne->setCredit(null);
				$ligne->setAnalytique($facture->getAnalytique());
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setNumEcriture($numEcriture);
				if(null !== $modePaiement){
					$ligne->setModePaiement($modePaiement);
				}
				$em->persist($ligne);
			}
			

			//lettrage de la ligne de la facture dans le journal de vente
			$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
			$ligneJournalVente = $journalVenteRepo->findOneBy(array(
				'facture' => $facture,
				'compteComptable' => $facture->getCompte()->getCompteComptableClient()
			));
			$ligneJournalVente->setLettrage($lettrage);
			$em->persist($ligneJournalVente);

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterDepense($mouvementBancaire, $depense, $ecrireLigneBanque = true, $noteFrais = null){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//récupération du prochain numéro de lettrage pour le compte comptable client
			$lettrageService = $this->get('appbundle.compta_lettrage_service');

			if($noteFrais){
				$lettrage = $lettrageService->findNextNum($noteFrais->getCompteComptable());
			} else {
				$lettrage = $lettrageService->findNextNum($depense->getCompte()->getCompteComptableFournisseur());
			}
			

			//debit au compte 401xxxx (compte du fournisseur)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit($depense->getTotalTTC());
			$ligne->setCredit(null);
			$ligne->setAnalytique($depense->getAnalytique());
			if($noteFrais){
				$ligne->setCompteComptable($noteFrais->getCompteComptable());
			} else {
				$ligne->setCompteComptable($depense->getCompte()->getCompteComptableFournisseur());
			}
			

			$ligne->setLettrage($lettrage);
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setModePaiement($depense->getModePaiement());
			$ligne->setNumEcriture($numEcriture);
			$em->persist($ligne);

			if(true === $ecrireLigneBanque){
				//crédit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit(null);
				$ligne->setCredit($depense->getTotalTTC());
				$ligne->setAnalytique($depense->getAnalytique());
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setModePaiement($depense->getModePaiement());
				$ligne->setNumEcriture($numEcriture);
				$em->persist($ligne);
			}

			//lettrage de la ligne de la dépense dans le journal d'achat
			$journalAchatsRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
			if($noteFrais){
				$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
					'depense' => $depense,
					'compteComptable' => $noteFrais->getCompteComptable()
				));
			} else {
				$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
					'depense' => $depense,
					'compteComptable' => $depense->getCompte()->getCompteComptableFournisseur()
				));
			}
			
			$ligneJournalAchats->setLettrage($lettrage);
			$em->persist($ligneJournalAchats);

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterAvoirFournisseur($mouvementBancaire, $avoir, $ecrireLigneBanque = true, $modePaiement = null){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//récupération du prochain numéro de lettrage pour le compte comptable client
			$lettrageService = $this->get('appbundle.compta_lettrage_service');
			$lettrage = $lettrageService->findNextNum($avoir->getDepense()->getCompte()->getCompteComptableFournisseur());

			//credit au compte  401xxxx (compte du fournisseur)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit(null);
			$ligne->setCredit($avoir->getTotalTTC());
			$ligne->setAnalytique($avoir->getDepense()->getAnalytique());
			$ligne->setCompteComptable($avoir->getDepense()->getCompte()->getCompteComptableFournisseur());
			$ligne->setLettrage($lettrage);
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			if(null !== $modePaiement){
				$ligne->setModePaiement($modePaiement);
			}
			$em->persist($ligne);

			if(true === $ecrireLigneBanque){
				//débit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit($avoir->getTotalTTC());
				$ligne->setCredit(null);
				$ligne->setAnalytique($avoir->getDepense()->getAnalytique());
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setNumEcriture($numEcriture);
				if(null !== $modePaiement){
					$ligne->setModePaiement($modePaiement);
				}
				$em->persist($ligne);
			}

			//lettrage de la ligne de l'avoir dans le journal d'achat
			$journalAchatsRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
			$ligneJournalAchats = $journalAchatsRepo->findOneBy(array(
				'avoir' => $avoir,
				'compteComptable' => $avoir->getDepense()->getCompte()->getCompteComptableFournisseur()
			));
			$ligneJournalAchats->setLettrage($lettrage);
			$em->persist($ligneJournalAchats);

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterAvoirClient($mouvementBancaire, $avoir, $ecrireLigneBanque = true){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//récupération du prochain numéro de lettrage pour le compte comptable client
			$lettrageService = $this->get('appbundle.compta_lettrage_service');
			$lettrage = $lettrageService->findNextNum($avoir->getFacture()->getCompte()->getCompteComptableClient());

			//debit au compte  401xxxx (compte du client)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit($rapprochementBancaire->getAvoir()->getTotalTTC());
			$ligne->setCredit(null);
			$ligne->setAnalytique($avoir->getFacture()->getAnalytique());
			$ligne->setCompteComptable($avoir->getFacture()->getCompte()->getCompteComptableClient());
			$ligne->setLettrage($lettrage);
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			$em->persist($ligne);

			if(true === $ecrireLigneBanque){
				//crédit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit(null);
				$ligne->setCredit($avoir->getTotalTTC());
				$ligne->setAnalytique($avoir->getFacture()->getAnalytique());
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setNumEcriture($numEcriture);
				$em->persist($ligne);
			}

			//lettrage de la ligne de l'avoir dans le journal de vente
			$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
			$ligneJournalVente = $journalVenteRepo->findOneBy(array(
				'avoir' => $avoir,
				'compteComptable' => $avoir->getFacture()->getCompte()->getCompteComptableClient()
			));
			$ligneJournalVente->setLettrage($lettrage);
			$em->persist($ligneJournalVente);

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterOD($mouvementBancaire, $od, $ecrireLigneBanque = true, $modePaiement = null){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//récupération du prochain numéro de lettrage pour le compte comptable client
			$lettrageService = $this->get('appbundle.compta_lettrage_service');
			$lettrage = $lettrageService->findNextNum($od->getCompteComptable());

			//debit au compte de l'OD
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit(null);
			$ligne->setCredit($od->getDebit());
			$ligne->setCompteComptable($od->getCompteComptable());
			$ligne->setLettrage($lettrage);
			$ligne->setNom($od->getLibelle());
			if(null !== $modePaiement){
				$ligne->setModePaiement($modePaiement);
			}
			$em->persist($ligne);

			//lettrage de l'OD
			$od->setLettrage($lettrage);
			$em->persist($od);

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterAffectationDiverseVente($mouvementBancaire, $affectationDiverse, $ecrireLigneBanque = true){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//crédit au compte xxxxxx (selon le compte rattaché à l'affectation)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit(null);
			$ligne->setCredit($mouvementBancaire->getMontant());
			$ligne->setAnalytique(null);
			$ligne->setCompteComptable($affectationDiverse->getCompteComptable());
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			$ligne->setCommentaire($affectationDiverse->getNom());
			$em->persist($ligne);


			if(true === $ecrireLigneBanque){
				//débit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit($mouvementBancaire->getMontant());
				$ligne->setCredit(null);
				$ligne->setAnalytique(null);
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setNumEcriture($numEcriture);
				$ligne->setCommentaire($affectationDiverse->getNom());
				$em->persist($ligne);
			}

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	private function ajouterAffectationDiverseAchat($mouvementBancaire, $affectationDiverse, $ecrireLigneBanque = true){

		$em = $this->getDoctrine()->getManager();

		try{
			//récupération du numéro d'écriture pour le FEC
			$numService = $this->get('appbundle.num_service');
			$numEcriture = $numService->getNumEcriture($this->getUser()->getCompany());

			//débit au compte xxxxxx (selon le compte rattaché à l'affectation)
			$ligne = new JournalBanque();
			$ligne->setMouvementBancaire($mouvementBancaire);
			$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
			$ligne->setDebit($mouvementBancaire->getMontant());
			$ligne->setCredit(null);
			$ligne->setAnalytique(null);
			$ligne->setCompteComptable($affectationDiverse->getCompteComptable());
			$ligne->setNom($mouvementBancaire->getLibelle());
			$ligne->setDate($mouvementBancaire->getDate());
			$ligne->setNumEcriture($numEcriture);
			$ligne->setCommentaire($affectationDiverse->getNom());
			$em->persist($ligne);


			if(true === $ecrireLigneBanque){
				//crédit au compte 512xxxx (compte de la banque)
				$ligne = new JournalBanque();
				$ligne->setMouvementBancaire($mouvementBancaire);
				$ligne->setCodeJournal($mouvementBancaire->getCompteBancaire()->getNom());
				$ligne->setDebit(null);
				$ligne->setCredit($mouvementBancaire->getMontant());
				$ligne->setAnalytique(null);
				$ligne->setCompteComptable($mouvementBancaire->getCompteBancaire()->getCompteComptable());
				$ligne->setNom($mouvementBancaire->getLibelle());
				$ligne->setDate($mouvementBancaire->getDate());
				$ligne->setNumEcriture($numEcriture);
				$ligne->setCommentaire($affectationDiverse->getNom());
				$em->persist($ligne);
			}

			$em->flush();

			//incrémentation du numéro d'écriture 
			$numEcriture++;
			$numService->updateNumEcriture($this->getUser()->getCompany(), $numEcriture);

		} catch (\Exception $e){
			return $e->getMessage();
		}
		
		return 'OK';
	}

	// /**
	//  * @Route("/compta/journal-banque/reinitialiser", name="compta_journal_banque_reinitialiser")
	//  */
	// public function journalBanqueReinitialiser(){

	// 	$em = $this->getDoctrine()->getManager();
	// 	$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');
	// 	$rapprochementRepo = $em->getRepository('AppBundle:Compta\Rapprochement');
	// 	$compteBancaireRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
	// 	$journalBanqueService = $this->container->get('appbundle.compta_journal_banque_controller');

	// 	$arr_comptesBancaires = $compteBancaireRepo->findByCompany($this->getUser()->getCompany());
	// 	foreach($arr_comptesBancaires as $compteBancaire){
	// 		$arr_journal = $journalBanqueRepo->findJournalEntier($this->getUser()->getCompany(), $compteBancaire);
	// 		foreach($arr_journal as $ligne){
	// 			$em->remove($ligne);
	// 		}
	// 	}
	// 	$em->flush();

	// 	$arr_rapprochements = $rapprochementRepo->findForCompany($this->getUser()->getCompany());
	// 	foreach($arr_rapprochements as $rapprochement){

	// 		$type = "";
	// 		if($rapprochement->getFacture()){
	// 			$type = "FACTURE";
	// 		} else if($rapprochement->getDepense()){
	// 			$type = "DEPENSE";
	// 		} else if($rapprochement->getAvoir()){
	// 			if($rapprochement->getAvoir()->getType() == 'CLIENT'){
	// 				$type = "AVOIR-CLIENT";
	// 			} else {
	// 				$type = "AVOIR-FOURNISSEUR";
	// 			}
	// 		} else if($rapprochement->getAccompte()){
	// 			$type = "ACCOMPTE";
	// 		} else if($rapprochement->getRemiseCheque()){
	// 			$type = "REMISE-CHEQUES";
	// 		} else if($rapprochement->getAffectationDiverse()){
	// 			if($rapprochement->getAffectationDiverse()->getType() == 'VENTE'){
	// 				$type = "AFFECTATION-DIVERSE-VENTE";
	// 			} else {
	// 				$type = "AFFECTATION-DIVERSE-ACHAT";
	// 			}
	// 		} else if($rapprochement->getNoteFrais()){
	// 			$type = "NOTE-FRAIS";
	// 		}

	// 		if($type != ""){
	// 			//ecrire dans le journal de banque
	// 			$journalBanqueService->journalBanqueAjouterAction($type, $rapprochement);
	// 		}

	// 	}

	// 	return new Response;

	// }

	// /**
	//  * @Route("/compta/lettrage2017",
	//  *   name="compta_lettrage_2017"
	//  * )
	//  */
	// public function lettrage2017(){

	// 	$em = $this->getDoctrine()->getManager();
	// 	$journalBanqueRepo = $em->getRepository('AppBundle:Compta\JournalBanque');
	// 	$journalVenteRepo = $em->getRepository('AppBundle:Compta\JournalVente');
	// 	$journalAchatRepo = $em->getRepository('AppBundle:Compta\JournalAchat');
	// 	$rapprochementRepo = $em->getRepository('AppBundle:Compta\Rapprochement');
	// 	$lettrageService = $this->get('appbundle.compta_lettrage_service');

	// 	$arr_rapprochements = $rapprochementRepo->findForCompanyByYear($this->getUser()->getCompany(), 2017);

	// 	foreach($arr_rapprochements as $rapprochement){

	// 		/*
	// 		if($rapprochement->getFacture()){

	// 			$facture = $rapprochement->getFacture();

	// 			if($facture->getDateCreation()->format('Y') != 2017){
	// 				continue;
	// 			}

	// 			$cc = $facture->getCompte()->getCompteComptableClient();
	// 			$lettrage = $lettrageService->findNextNum($cc);

	// 			$ligneBanque = $journalBanqueRepo->findOneBy(array(
	// 				'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
	// 				'compteComptable' => $cc
	// 			));

	// 			$ligneVente = $journalVenteRepo->findOneBy(array(
	// 				'facture' => $facture,
	// 				'compteComptable' => $cc
	// 			));

	// 			if($ligneVente && $ligneBanque){
	// 				$ligneVente->setLettrage($lettrage);
	// 				$em->persist($ligneVente);
	// 				$ligneBanque->setLettrage($lettrage);
	// 				$em->persist($ligneBanque);
	// 				$em->flush();

	// 			} 
				
	// 		} 
	// 		*/

			
	// 		if ($rapprochement->getAvoir()){
	// 			$avoir = $rapprochement->getAvoir();
	// 			if($avoir->getDateCreation()->format('Y') != 2017){
	// 				continue;
	// 			}

	// 			if($avoir->getDepense()){

	// 				if($avoir->getDateCreation()->format('Y') != 2017){
	// 					continue;
	// 				}

	// 				$cc = $avoir->getDepense();->getCompte()->getCompteComptableFournisseur();
	// 				$lettrage = $lettrageService->findNextNum($cc);

	// 				$ligneBanque = $journalBanqueRepo->findOneBy(array(
	// 					'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
	// 					'compteComptable' => $cc
	// 				));

	// 				$ligneAchat = $journalAchatRepo->findOneBy(array(
	// 					'avoir' => $avoir,
	// 					'compteComptable' => $cc
	// 				));

	// 				if($ligneAchat && $ligneBanque){
	// 					$ligneAchat->setLettrage($lettrage);
	// 					$em->persist($ligneAchat);
	// 					$ligneBanque->setLettrage($lettrage);
	// 					$em->persist($ligneBanque);
	// 					$em->flush();
	// 				} 
	// 			} else if ($avoir->getFacture()){
					
	// 				$cc = $avoir->getFacture()->getCompte()->getCompteComptableClient();
	// 				$lettrage = $lettrageService->findNextNum($cc);

	// 				$ligneBanque = $journalBanqueRepo->findOneBy(array(
	// 					'mouvementBancaire' => $rapprochement->getMouvementBancaire(),
	// 					'compteComptable' => $cc
	// 				));

	// 				$ligneVente = $journalVenteRepo->findOneBy(array(
	// 					'avoir' => $avoir,
	// 					'compteComptable' => $cc
	// 				));

	// 				if($ligneVente && $ligneBanque){
	// 					$ligneVente->setLettrage($lettrage);
	// 					$em->persist($ligneVente);
	// 					$ligneBanque->setLettrage($lettrage);
	// 					$em->persist($ligneBanque);
	// 					$em->flush();

	// 				} 
	// 			}
				
	// 		}
			
	// 	}

	// 	return new Response();

	// }


}
