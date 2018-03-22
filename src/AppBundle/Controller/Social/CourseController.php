<?php

namespace AppBundle\Controller\Social;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Social\Course;

use AppBundle\Form\Social\CourseType;


class CourseController extends Controller
{


	/**
	 * @Route("/compta/course/ajouter",
	 *   name="compta_course_ajouter",
	 *  )
	 */
	public function ajouter(){

		$em = $this->getDoctrine()->getManager();

		$course = new Course();
		$course->setUser($this->getUser());

		$form = $this->createForm(
			new CourseType(),
			$course
		);

		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($course);
			$em->flush();

			return $this->redirect($this->generateUrl('social_index'));
		}

		return $this->render('social/course/social_course_ajouter.html.twig', array(
			'form' => $form->createView()
		));

	}

	/**
	 * @Route("/compta/course/supprimer",
	 *   name="compta_course_supprimer",
	 *   options={"expose"=true}
	 *  )
	 */
	public function supprimer(){

		$em = $this->getDoctrine()->getManager();
		$courseRepo = $em->getRepository('AppBundle:Social\Course');

		$arr_courses = $this->getRequest()->request->get('arr_courses');
		foreach($arr_courses as $courseId){
			$course = $courseRepo->find($courseId);
			$em->remove($course);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('social_index'));
	}
}
