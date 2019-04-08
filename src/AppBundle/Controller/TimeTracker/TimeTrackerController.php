<?php

namespace AppBundle\Controller\TimeTracker;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use AppBundle\Entity\TimeTracker\Temps;
use AppBundle\Form\TimeTracker\TempsType;

class TimeTrackerController extends Controller
{

	/**
	 * @Route("/time-tracker", name="time_tracker_index")
	 */
	public function indexAction()
	{
		if(!$this->getUser()->hasRole('ROLE_TIMETRACKER')){
			throw new AccessDeniedException;
		}
		
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:CRM\Opportunite');

		$temps = new Temps();
		$temps->setDate(new \DateTime(date('Y-m-d')));
		$temps->setUser($this->getUser());

		$form = $this->createForm(
			new TempsType(), 
			$temps
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['projet_entity']->getData()){
				$actionCommerciale = $repo->findOneById($form['projet_entity']->getData());
				if($actionCommerciale){
					$temps->setActionCommerciale($actionCommerciale);
				}
			}

			$em->persist($temps);
			$em->flush();

			$this->get('session')->getFlashBag()->add(
				'success',
				$temps->getDureeAsString(). ' ajoutées à '.$temps->getActionCommerciale()->getNom()
			);

			return $this->redirect($this->generateUrl(
				'time_tracker_index'
			));
		}
		
		$arr_projets = $repo->findWonNotClosed($this->getUser()->getCompany());

		return $this->render('time_tracker/time_tracker_index.html.twig', array(
			'form' => $form->createView(),
			'arr_projets' => $arr_projets
		));
	}


}
