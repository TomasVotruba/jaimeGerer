<?php

namespace AppBundle\Controller\TimeTracker;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\TimeTracker\Temps;
use AppBundle\Form\TimeTracker\TempsType;

class TimeTrackerController extends Controller
{

	/**
	 * @Route("/time-tracker", name="time_tracker_index")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:CRM\Opportunite');

		$temps = new Temps();
		$temps->setDate(new \DateTime(date('Y-m-d')));

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
