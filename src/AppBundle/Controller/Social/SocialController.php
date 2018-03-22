<?php

namespace AppBundle\Controller\Social;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;



class SocialController extends Controller
{

	/**
	 * @Route("/social", name="social_index")
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getManager();
		$tableauMerciRepo = $em->getRepository('AppBundle:Social\TableauMerci');
		$courseRepo = $em->getRepository('AppBundle:Social\Course');

		$tableauMerci = $tableauMerciRepo->findCurrent();
		$arr_courses = $courseRepo->findAll();

		return $this->render('social/social_index.html.twig', array(
			'tableauMerci' => $tableauMerci,
			'arr_courses' => $arr_courses
		));
	}
}
