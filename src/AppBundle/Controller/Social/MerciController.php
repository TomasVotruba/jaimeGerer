<?php

namespace AppBundle\Controller\Social;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Social\TableauMerci;
use AppBundle\Entity\Social\Merci;

use AppBundle\Form\Social\TableauMerciType;
use AppBundle\Form\Social\MerciType;

class MerciController extends Controller
{

	/**
	 * @Route("/compta/merci/choisir-objectifs",
	 *   name="compta_merci_choisir_objectifs",
	 *  )
	 */
	public function choisirObjectifs(){

		$em = $this->getDoctrine()->getManager();
		$tableauMerci = new TableauMerci();

		$form = $this->createForm(
			new TableauMerciType(),
			$tableauMerci
		);

		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($tableauMerci);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/merci/social_merci_choisir_objectif.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/merci/ajouter/{type}",
	 *   name="compta_merci_ajouter",
	 *  )
	 */
	public function ajouter($type){

		$em = $this->getDoctrine()->getManager();
		$tableauMerciRepo = $em->getRepository('AppBundle:Social\TableauMerci');

		$tableauMerci = $tableauMerciRepo->findCurrent();

		$merci = new Merci();
		$merci->setType($type);
		$merci->setDate(new \DateTime(date('Y-m-d')));
		$merci->setTableauMerci($tableauMerci);

		if(strtolower($type) == "externe"){
			$merci->setTo($this->getUser());
		} else {
			$merci->setFromUser($this->getUser());
		}

		$form = $this->createForm(
			new MerciType($this->getUser()->getCompany()),
			$merci
		);

		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($merci);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/merci/social_merci_ajouter.html.twig', array(
			'form' => $form->createView(),
			'type' => $type
		));

	}
}
