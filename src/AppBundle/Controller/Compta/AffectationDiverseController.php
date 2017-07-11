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
	
}