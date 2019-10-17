<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Compta\AffectationDiverse;
use AppBundle\Form\Compta\AffectationDiverseType;

class AffectationDiverseController extends Controller
{

	/**
	 * @Route("/compta/affectation-diverse/ajouter-modal/{type}", name="compta_affectation_diverse_ajouter_modal", options={"expose"=true})
	 */
	public function affectationDiverseAjouterModalAction($type=null)
	{
		$em = $this->getDoctrine()->getManager();
		$affectation = new AffectationDiverse();
		$affectation->setCompany($this->getUser()->getCompany());
		if($type){
			$affectation->setType($type);
		}
		
		$form = $this->createForm(
				new AffectationDiverseType(
						$this->getUser()->getCompany()->getId(), $type
				),
				$affectation
		);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($affectation);
			$em->flush();
			
			return new JsonResponse(array(
				'id' => $affectation->getId(),
				'nom' => $affectation->getNom(),
				'type' => $affectation->getType()
			));
			
		}
	
		return $this->render('compta/affectation_diverse/compta_affectation_diverse_ajouter_modal.html.twig', array(
				'form' => $form->createView(),
				'affectation' => $affectation
		));
	}

	/**
	 * @Route("/compta/affectation-diverse/liste", name="compta_affectation_diverse_liste")
	 */
	public function affectationDiverseListeAction()
	{
		$repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\AffectationDiverse');
		$arr_achats = $repo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'type' => 'ACHAT',
			'recurrent' => true
		), array(
			'nom' => 'ASC'
		));
		$arr_ventes = $repo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'type' => 'VENTE',
			'recurrent' => true
		), array(
			'nom' => 'ASC'
		));

		return $this->render('compta/affectation_diverse/compta_affectation_diverse_liste.html.twig', array(
				'arr_achats' => $arr_achats,
				'arr_ventes' => $arr_ventes
		));
	}

	/**
	 * @Route("/compta/affectation-diverse/supprimer/{id}", name="compta_affectation_diverse_supprimer")
	 */
	public function affectationDiverseSupprimerAction(AffectationDiverse $affectationDiverse)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			//en réalité on ne la supprime pas de la base de données car il peut y avoir des rapprochements bancaires liés à cette affectation. On met recurrent à false pour qu'elle ne soit plus proposée lors des rapprochements.
			$affectationDiverse->setRecurrent(false);
			$em->persist($affectationDiverse);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'success',
				'L\'affectation diverse a bien été supprimée.'
			);

			return $this->redirect($this->generateUrl(
				'compta_affectation_diverse_liste'
			));
		}

		return $this->render('compta/affectation_diverse/compta_affectation_diverse_supprimer.html.twig', array(
			'form' => $form->createView(),
			'affectationDiverse' => $affectationDiverse
		));
	}
	
	/**
	 * @Route("/compta/affectation-diverse/editer/{id}", name="compta_affectation_diverse_editer")
	 */
	public function affectationDiverseEditerAction(AffectationDiverse $affectationDiverse)
	{
		$em = $this->getDoctrine()->getManager();
		
		$form = $this->createForm(
			new AffectationDiverseType(
					$this->getUser()->getCompany()->getId(), $affectationDiverse->getType()
			),
			$affectationDiverse
		);

		$form->remove('recurrent');
		$form->remove('filtre_comptes');
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->persist($affectationDiverse);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'success',
				'L\'affectation diverse a bien été modifiée.'
			);
			
			return $this->redirect($this->generateUrl(
				'compta_affectation_diverse_liste'
			));
			
		}
	
		return $this->render('compta/affectation_diverse/compta_affectation_diverse_editer.html.twig', array(
			'form' => $form->createView(),
			'affectationDiverse' => $affectationDiverse
		));
	}
}