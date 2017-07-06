<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Compta\CompteComptable;
use AppBundle\Form\Compta\CompteComptableType;
use AppBundle\Entity\Compta\MouvementBancaire;
use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Form\Compta\OperationDiverseType;

class CompteAttenteController extends Controller
{
	/**
	 * @Route("/compta/compte-attente/voir", name="compta_compte_attente_voir")
	 */
	public function compteAttenteVoirAction(){

		//recuperation du compte 471
		$em = $this->getDoctrine()->getManager();
		$ccRepository = $em->getRepository('AppBundle:Compta\CompteComptable');
		$compteAttente = $ccRepository->findOneBy(array(
				'num' => '471',
				'company' => $this->getUser()->getCompany()
		));

		//lignes du journal de ventes pour le compte 471
		$repoJournalVente = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
		$arr_journal_vente = $repoJournalVente->findCompteAttenteACorriger($compteAttente, $this->getUser()->getCompany());

		//lignes du journal d'achats pour le compte 471
		$repoJournalAchat = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalAchat');
		$arr_journal_achat = $repoJournalAchat->findCompteAttenteACorriger($compteAttente, $this->getUser()->getCompany());

		//lignes du journal de banque pour le compte 471
		$repoJournalBanque = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
		$arr_journal_banque = $repoJournalBanque->findByCompteForCompany($compteAttente, $this->getUser()->getCompany());

		// //lignes des opérations diverses
		// $repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		// $arr_operation_diverse = $repoOperationDiverse->findByCompteForCompany($compteAttente, $this->getUser()->getCompany());

		//regroupement dans 1 seul array
		$arr_lignes = array_merge($arr_journal_vente, $arr_journal_achat, $arr_journal_banque);
		$arr_lignes_inverses = array();

		foreach($arr_journal_vente as $ligne){
			$ligneInverse = $repoJournalVente->findInverse($ligne);
			$arr_lignes_inverses[$ligne->getId()] = $ligneInverse;
		}

		foreach($arr_journal_achat as $ligne){
			$ligneInverse = $repoJournalAchat->findInverse($ligne);
			$arr_lignes_inverses[$ligne->getId()] = $ligneInverse;
		}

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

		return $this->render('compta/compte_attente/compta_compte_attente_voir.html.twig', array(
				'compteAttente' => $compteAttente,
				'arr_lignes' => $arr_lignes,
				'total_debit' => $total_debit,
				'total_credit' => $total_credit,
				'arr_lignes_inverses' => $arr_lignes_inverses
		));
	}

	/**
	 * @Route("/compta/compte-attente/corriger/{id}/{codeJournal}", name="compta_compte_attente_corriger")
	 */
	public function compteAttenteCorrigerAction($id, $codeJournal){

		//creation du formulaire
		$od =  new OperationDiverse();
		$form = $this->createForm(new OperationDiverseType($this->getUser()->getCompany()), $od);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$compteChoisi = $od->getCompteComptable();

			//recuperation du compte 471
			$em = $this->getDoctrine()->getManager();
			$ccRepository = $em->getRepository('AppBundle:Compta\CompteComptable');
			$compteAttente = $ccRepository->findOneBy(array(
					'num' => '471',
					'company' => $this->getUser()->getCompany()
			));

			//recuperation de la ligne à corriger
			if($codeJournal == 'VE'){
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalVente');
			} elseif($codeJournal == 'AC'){
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalAchat');
			} elseif($codeJournal != 'OD') {
				$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\JournalBanque');
			}

			$ligne = $repo->find($id);

			//ecriture d'une ligne Opération Diverse au compte choisi
			$od->setDate(new \DateTime(date('Y-m-d')));

			$libelle = "";
			if($codeJournal == 'VE'){
				if($ligne->getFacture() != null){
					$libelle = 'Facture '.$ligne->getFacture()->getNum();
					$od->setFacture($ligne->getFacture());
				} elseif($ligne->getAvoir() != null){
					$libelle = 'Avoir '.$ligne->getAvoir()->getNum();
					$od->setAvoir($ligne->getAvoir());
				}
			} elseif($codeJournal == 'AC'){
				if($ligne->getDepense() != null){
					$libelle = 'Dépense '.$ligne->getDepense()->getNum();
					$od->setDepense($ligne->getDepense());
				} elseif($ligne->getAvoir() != null){
					$libelle = 'Avoir '.$ligne->getAvoir()->getNum();
					$od->setAvoir($ligne->getAvoir());
				}
			} elseif($codeJournal != 'OD') {
				$libelle = $ligne->getNom();
			}
			$od->setLibelle($libelle.' - correction depuis compte 471');
			$od->setCodeJournal('OD');
			if($ligne->getDebit() != null){
				$od->setCredit(null);
				$od->setDebit($ligne->getDebit());
			} else {
				$od->setDebit(null);
				$od->setCredit($ligne->getCredit());
			}
			$em->persist($od);


			//ecriture d'une ligne Opération Diverse au compte 471
			$od = new OperationDiverse();
			$od->setDate(new \DateTime(date('Y-m-d')));
			$od->setLibelle($libelle.' - correction pour compte '.$compteChoisi->getNum());
			$od->setCodeJournal('OD');
			//inverser le debit et le credit
			if($ligne->getDebit() != null){
				$od->setDebit(null);
				$od->setCredit($ligne->getDebit());
			} else {
				$od->setCredit(null);
				$od->setDebit($ligne->getCredit());
			}
			$od->setCompteComptable($compteAttente);
			$em->persist($od);

			$em->flush();

			return $this->redirect(($this->generateUrl('compta_compte_attente_voir')));
		}

		return $this->render('compta/compte_attente/compta_compte_attente_corriger_modal.html.twig', array(
				'form' => $form->createView(),
				'id' => $id,
				'codeJournal' => $codeJournal
		));

	}

}
