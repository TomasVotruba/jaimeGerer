<?php

namespace AppBundle\Controller\Emailing;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Emailing\TemplateEmailing;

/**
 * Emailing\Template controller.
 *
 * @Route("/emailing/template")
 */
class TemplateController extends Controller
{
	/**
	 * @Route("/emailing/template/liste", name="emailing_template_emailing_liste")
	 */
	public function indexAction()
	{
		return $this->render('emailing/template/emailing_index.html.twig');
	}
	/**
	 * @Route("/emailing/template/statistique", name="emailing_statistiques")
	 */
	public function statsAction()
	{
		return $this->render('emailing/template/emailing_index.html.twig');
	}
	/**
	 * @Route("/emailing/template/contact", name="emailing_contact_liste")
	 */
	public function contactAction()
	{
		return $this->render('emailing/template/emailing_index.html.twig');
	}
}
