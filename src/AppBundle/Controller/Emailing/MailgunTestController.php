<?php

namespace AppBundle\Controller\Emailing;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Mailgun\Mailgun;

use AppBundle\Entity\Emailing\Campagne;
use AppBundle\Entity\Emailing\CampagneContact;


class MailgunTestController extends Controller
{

	/**
	 * @Route("/emailing/test/api", name="emailing_test_api")
	 */ 
	public function testAPI(){

		$em = $this->getDoctrine()->getManager();

		//set campaign details
		$campagne = new Campagne();
		$campagne->setNom('Dev - Newsletter');
		$campagne->setObjet('Newsletter Nicomak');
		$campagne->setNomExpediteur('Nicomak');
		$campagne->setEmailExpediteur('contact@nicomak.eu');
		$campagne->setDateCreation(new \DateTime(date('Y-m-d')));
		$campagne->setUserCreation($this->getUser());
		$campagne->setDateEnvoi(new \DateTime(date('Y-m-d')));

		//set HTML
		$path = $this->get('kernel')->getRootDir().'/../web/files/emailing/';
		$html = file_get_contents($path.'newsletter.html');
		$campagne->setHtml($html);

		$em->persist($campagne);

		//add contacts
		$rapportRepo = $em->getRepository('AppBundle:CRM\Rapport');
		$rapport = $rapportRepo->find(435);
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$arr_filters = $filterRepo->findByRapport($rapport);
		$contactRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$arr_contacts = $contactRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany(), true);

		foreach($arr_contacts as $contact){
			$campagneContact = new CampagneContact();
			$campagneContact->setContact($contact);
			$campagne->addCampagneContact($campagneContact);
		}

		$em->persist($campagne);
		$em->flush();

		$mailgunService = $this->get('appbundle.mailgun');
		$mailgunService->sendViaAPI($campagne);

	 	return new Response();
	}

	/**
	 * @Route("/emailing/test/webhook", name="emailing_test_webhook")
	 */
	public function testWebhook(){
		
		$response = new Response();
		$mailgunService = $this->get('appbundle.mailgun');

		$request = $this->getRequest();
		$content = json_decode($request->getContent(), true);

		//check the signature
		$signature = $content['signature'];
		if ( $mailgunService->checkWebhookSignature($signature['token'], $signature['timestamp'], $signature['signature'] ) === false ) {
       		$response->setStatusCode('401');
			return $response;
		}

		$eventData = $content['event-data'];
		if( $mailgunService->saveWebhookEvent($eventData) === false ){
			$response->setStatusCode('500');
			return $response;
		}

		return $response;
		
	}

}
