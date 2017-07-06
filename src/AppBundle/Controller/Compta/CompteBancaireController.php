<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Compta\CompteBancaire;
use AppBundle\Entity\Compta\SoldeCompteBancaire;

use AppBundle\Form\Compta\CompteBancaireType;

class CompteBancaireController extends Controller
{
	/**
	 * @Route("/compta/compte-bancaire", name="compta_compte_bancaire_liste")
	 */
	public function compteBancaireListeAction()
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteBancaire');
		$arr_comptesBancaires = $repo->findByCompany($this->getUser()->getCompany());
		
		/*pour afficher le solde du compte bancaire*/
		$arr_soldes = array();
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		foreach($arr_comptesBancaires as $compteBancaire){
			$solde = $soldeRepo->findLatest($compteBancaire);
			$arr_soldes[$compteBancaire->getId()] = $solde->getMontant();
		}
		
		return $this->render('compta/compte_bancaire/compta_compte_bancaire_liste.html.twig', array(
			'arr_comptesBancaires' => $arr_comptesBancaires,
			'arr_soldes' => $arr_soldes
		));
	}
	
	/**
	 * @Route("/compta/compte-bancaire/ajouter/{initialisation}", name="compta_compte_bancaire_ajouter")
	 */
	public function compteBancaireAjouterAction($initialisation = false)
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$compteComptable = $repo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'num' => 512
		));

		$compteBancaire = new CompteBancaire();
		$compteBancaire->setCompany($this->getUser()->getCompany());
		$compteBancaire->setCompteComptable($compteComptable);
		$form = $this->createForm(
				new CompteBancaireType(
						$this->getUser()->getCompany()->getId()
				),
				$compteBancaire
		);
		
		$request = $this->getRequest();
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
		
			$em = $this->getDoctrine()->getManager();
			$em->persist($compteBancaire);
			
			$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
			$annee = date('Y');
			
			$newSolde = new SoldeCompteBancaire();
			$newSolde->setCompteBancaire($compteBancaire);
			$newSolde->setDate(new \DateTime(date('Y-m-d')));
			$newSolde->setMontant($form['solde']->getData());
			$em->persist($newSolde);
				
			$soldeDebutAnnee = $soldeRepo->findOneBy(array(
					'compteBancaire' => $compteBancaire,
					'date' => \DateTime::createFromFormat('Y-m-d', $annee.'-01-01')
			));
			
			
			if($soldeDebutAnnee == null){
				$soldeDebutAnnee = new SoldeCompteBancaire();
				$soldeDebutAnnee->setCompteBancaire($compteBancaire);
				$soldeDebutAnnee->setDate(\DateTime::createFromFormat('Y-m-d', $annee.'-01-01'));
				$soldeDebutAnnee->setMontant($form['soldeDebutAnnee']->getData());
			} else {
				$soldeDebutAnnee->setMontant($form['soldeDebutAnnee']->getData());
			}
			$em->persist($soldeDebutAnnee);
			
			$em->flush();

			if($initialisation){
				return $this->redirect($this->generateUrl('compta_activer'));
			}
			
			return $this->redirect($this->generateUrl(
					'compta_compte_bancaire_liste'
			));
		
		}
		
		if($initialisation){
			return $this->render('compta/compte_bancaire/compta_compte_bancaire_ajouter_initialisation.html.twig', array(
					'form' => $form->createView(),
					'compteComptable' => $compteComptable
			));
		}
		
		return $this->render('compta/compte_bancaire/compta_compte_bancaire_ajouter.html.twig', array(
				'form' => $form->createView(),
				'compteComptable' => $compteComptable
		));
	}
	
	/**
	 * @Route("/compta/compte-bancaire/editer/{id}", name="compta_compte_bancaire_editer")
	 */
	public function compteBancaireEditerAction(CompteBancaire $compteBancaire)
	{
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		$solde = $soldeRepo->findLatest($compteBancaire);
		$annee = date('Y');
		$soldeDebutAnnee = $soldeRepo->findOneBy(array(
				'compteBancaire' => $compteBancaire,
				'date' => \DateTime::createFromFormat('Y-m-d', $annee.'-01-01')
		));
		
		if($soldeDebutAnnee == null){
			$soldeDebutAnneeMontant = 0;
		} else {
			$soldeDebutAnneeMontant = $soldeDebutAnnee->getMontant();
		}
		
		$form = $this->createForm(
				new CompteBancaireType(
						$this->getUser()->getCompany()->getId(),
						$solde->getMontant(),
						$soldeDebutAnneeMontant
				),
				$compteBancaire
		);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($compteBancaire);
			
			$newSolde = new SoldeCompteBancaire();
			$newSolde->setCompteBancaire($compteBancaire);
			$newSolde->setDate(new \DateTime(date('Y-m-d')));
			$newSolde->setMontant($form['solde']->getData());
			$em->persist($newSolde);
			
			if($soldeDebutAnnee == null){
				$soldeDebutAnnee = new SoldeCompteBancaire();
				$soldeDebutAnnee->setCompteBancaire($compteBancaire);
				$soldeDebutAnnee->setDate(\DateTime::createFromFormat('Y-m-d', $annee.'-01-01'));
				$soldeDebutAnnee->setMontant($form['soldeDebutAnnee']->getData());
			} else {
				$soldeDebutAnnee->setMontant($form['soldeDebutAnnee']->getData());
			}
			$em->persist($soldeDebutAnnee);
			
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'compta_compte_bancaire_liste'
			));
	
		}
		
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
		$compteComptable = $repo->findOneBy(array(
				'company' => $this->getUser()->getCompany(),
				'num' => 512
		));
		
	
		return $this->render('compta/compte_bancaire/compta_compte_bancaire_editer.html.twig', array(
				'form' => $form->createView(),
				'compteBancaire' => $compteBancaire,
				'compteComptable' => $compteComptable
		));
	}
	
	/**
	 * @Route("/compta/compte-bancaire/supprimer/{id}", name="compta_compte_bancaire_supprimer")
	 */
	public function compteBancaireSupprimerAction(CompteBancaire $compteBancaire)
	{
		$form = $this->createFormBuilder()->getForm();
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->remove($compteBancaire);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'compta_compte_bancaire_liste'
			));
	
		}
	
		return $this->render('compta/compte_bancaire/compta_compte_bancaire_supprimer.html.twig', array(
				'form' => $form->createView(),
				'compteBancaire' => $compteBancaire
		));
	}
	
	/**
	 * @Route("/compta/compte-bancaire/voir/{id}", name="compta_compte_bancaire_voir")
	 */
	public function compteBancaireVoirAction(CompteBancaire $compteBancaire)
	{
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		$solde = $soldeRepo->findLatest($compteBancaire);
		
		return $this->render('compta/compte_bancaire/compta_compte_bancaire_voir.html.twig', array(
				'compteBancaire' => $compteBancaire,
				'solde' => $solde
		));
	}
	
	/**
	 * @Route("/compta/compte-bancaire/solde/{id}", name="compta_compte_bancaire_solde", options={"expose"=true})
	 */
	public function compteBancaireSoldeAction(CompteBancaire $compteBancaire)
	{
		$soldeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\SoldeCompteBancaire');
		$solde = $soldeRepo->findLatest($compteBancaire);
		
		
		return new JsonResponse(array('solde' => $solde->getMontant()));
	}

}