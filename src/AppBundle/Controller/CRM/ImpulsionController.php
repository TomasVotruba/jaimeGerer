<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\CRM\Impulsion;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;

use AppBundle\Form\CRM\ImpulsionType;

class ImpulsionController extends Controller
{
	/**
	 * @Route("/crm/impulsion/liste", name="crm_impulsion_liste")
	 */
	public function impulsionListeAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Impulsion');
		$list = $repository->findByUser($this->getUser());
		return $this->render('crm/impulsion/crm_impulsion_liste.html.twig', array(
				'list' => $list
		));
	}
	
	/**
	 * @Route("/crm/impulsion/voir/{id}", name="crm_impulsion_voir")
	 */
	public function impulsionVoirAction(Impulsion $impulsion)
	{
		return $this->render('crm/impulsion/crm_impulsion_voir.html.twig', array(
				'impulsion' => $impulsion
		));
	}
	
	/**
	 * @Route("/crm/impulsion/ajouter/{contact}", name="crm_impulsion_ajouter")
	 */
	public function impulsionAjouterAction(Contact $contact = null)
	{
		$em = $this->getDoctrine()->getManager();
		$impulsion = new Impulsion();
		$impulsion->setUser($this->getUser());

		if($contact){
			$impulsion->setContact($contact->getId());
		}
		
		$form = $this->createForm(new ImpulsionType(
						$impulsion->getUser()->getId(),
						$this->getUser()->getCompany()->getId()
				), $impulsion);
		
		if($contact){
			$form->get('contact_name')->setData($contact->__toString());
		}
		
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid() ) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));
			
			$impulsion->setUser($this->getUser());
			$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));
			
			$em->persist($impulsion);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'crm_impulsion_voir',
					array('id' => $impulsion->getId())
			));
		}
		
		return $this->render('crm/impulsion/crm_impulsion_ajouter.html.twig', array(
				'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/crm/impulsion/editer/{id}", name="crm_impulsion_editer")
	 */
	public function impulsionEditerAction(Impulsion $impulsion)
	{
		$_contact = $impulsion->getContact();
		$impulsion->setContact($_contact->getId());
		
		$form = $this->createForm(new ImpulsionType(
						$impulsion->getUser()->getId(),
						$this->getUser()->getCompany()->getId()
				), $impulsion);
		
		
		$form->get('contact_name')->setData($_contact->__toString());
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$data = $form->getData();
			$impulsion->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));

			$em->persist($impulsion);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'crm_impulsion_liste'
			));
		}
	
		return $this->render('crm/impulsion/crm_impulsion_editer.html.twig', array(
				'form' => $form->createView()
		));
	}
	
	/**
	 * @Route("/crm/impulsion/supprimer/{id}", name="crm_impulsion_supprimer")
	 */
	public function impulsionSupprimerAction(Impulsion $impulsion)
	{
		$form = $this->createFormBuilder()->getForm();
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$em = $this->getDoctrine()->getManager();
			$em->remove($impulsion);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'crm_impulsion_liste'
			));
		}
	
		return $this->render('crm/impulsion/crm_impulsion_supprimer.html.twig', array(
				'form' => $form->createView(),
				'impulsion' => $impulsion
		));
	}
	
	/**
	 * @Route("/crm/impulsion/get-impulsions", name="crm_impulsion_get_liste")
	 */
	public function impulsion_listAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Impulsion');
		$list = $repository->findAll();
		
		$res = array();
		foreach ($list as $impulsion) {
			$_res = array('id' => $impulsion->getId(), 'display' => $impulsion->getContact()->getPrenom() ." ". $impulsion->getContact()->getNom());
			$res[] = $_res;
		}
	
		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	private function _sortTempsRestant($a, $b){
		if ($a->getTempsRestant() == $b->getTempsRestant()) {
        	return 0;
    	}
    	return ($a->getTempsRestant() < $b->getTempsRestant()) ? -1 : 1;
	}

}
