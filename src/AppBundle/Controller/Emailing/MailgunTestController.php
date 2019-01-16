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
	 * @Route("/emailing/test/smtp", name="emailing_test_smtp")
	 */ 
	public function testSMTP()
	{

		try{
			$message = \Swift_Message::newInstance()
	            ->setSubject("Hello (SMTP)")
	            ->setFrom('gilquin@nicomak.eu')
	            ->setTo('gilquin@nicomak.eu')
	            ->setBcc(array('laura@web4change.com' => 'Laura Web4Change', 'slac@nicomak.eu' => 'Laura SLAC'))
	            ->setBody('YO <a href="www.nicomak.eu">Das website !!</a>', 'text/html');

	        $this->get('mailer')->send($message);


		} catch(\Exception $e){
			throw $e;
		}
		

	    return new Response();

	}

	/**
	 * @Route("/emailing/test/api", name="emailing_test_api")
	 */ 
	public function testAPI(){

		
		$em = $this->getDoctrine()->getManager();
		//$campagne = $em->getRepository('AppBundle:Emailing\Campagne')->find(14);
		
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
		$rapport = $rapportRepo->find(442);
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

		//Send via MailGun API
		$key = $this->container->getParameter('mailgun_api_key');
		$domain = $this->container->getParameter('mailgun_domain');
		$mgClient = new \Mailgun\Mailgun($key);
		$id = $campagne->getId();
		$companyId = $this->getUser()->getCompany()->getId();

		$result = $mgClient->sendMessage($domain, array(
		    'from'    => $campagne->getNomExpediteur().' <'.$campagne->getEmailExpediteur().'>',
		    'to'      => $campagne->getDestinataires(),
		    'subject' => $campagne->getObjet(),
		   // 'text' => $campagne->getObjet(),
		    'html'    => $campagne->getHtml(),
		    'recipient-variables' => '{}',
		    'v:my-custom-data'   => "{'campagne-id' => $id, 'company-id' => $companyId }",
		    'o:testmode ' => 'true'
		));

		dump($result);


	 	return new Response();
	}

	/**
	 * @Route("/emailing/test/webhook", name="emailing_test_webhook")
	 */
	public function testWebhook(){
		
		$response = new Response();
		$request = $this->getRequest();

		$content = json_decode($request->getContent(), true);

		$signature = $content['signature'];

		//check the signature
		if( $this->verifiyWebhookCall($signature['token'], $signature['timestamp'], $signature['signature'] ) === false ){
			$response->setStatusCode('401');
			return $response;
		}

		$eventData = $content['event-data'];

		$contactEmail = $eventData['recipient'];
		$campagneId = $eventData['my-custom-data']['campagne-id'];
		$companyId = $eventData['my-custom-data']['company-id'];
		$timestamp = $eventData['timestamp'];

		if(!$contactEmail || !$campagneId || !$companyId){
			return $response();
		}

		$em = $this->getDoctrine()->getManager();
		$contactRepository = $em->getRepository('AppBundle:CRM\Contact');
		$campagneContactRepository = $em->getRepository('AppBundle:Emailing\CampagneContact');

		$contact = $contactRepository->findByEmailAndCompany($contactEmail, $companyId);

		if($contact){

			$campagneContact = $campagneContactRepository->findOneBy(array(
				'contact' => $contact,
				'campagne' => $campagneId
			));

			if($campagneContact){

				switch($event){

					case 'delivered':
						$campagneContact->setDelivered(true);
						$campagneContact->setDeliveredDate(new \DateTime(date('Y-m-d', $timestamp)));

				}
				
				$em->persist($campagneContact);
				$em->flush();
			}

		}
	
		return $response;
		
	}

	/**
	 * @Route("/emailing/test/events", name="emailing_test_events")
	 */
	public function testEvents(){

		$key = $this->container->getParameter('mailgun_api_key');
		$domain = $this->container->getParameter('mailgun_domain');

		$mgClient = new \Mailgun\Mailgun($key);
		$result = $mgClient->get("$domain/events");

		dump($result);

		return new Response();
	}

	/**
	 * Check request signature when receiving a call from a Mailgun webhook
	 **/
	private function verifiyWebhookCall($token, $timestamp, $signature){
		
		$key = $this->container->getParameter('mailgun_api_key');
	 	/*
	 		From Mailgun docs : https://documentation.mailgun.com/en/latest/user_manual.html#webhooks
			To verify the webhook is originating from Mailgun you need to:
				- Concatenate timestamp and token values.
				- Encode the resulting string with the HMAC algorithm (using your API Key as a key and SHA256 digest mode).
				- Compare the resulting hexdigest to the signature.
				- Optionally, you can check if the timestamp is not too far from the current time.
			
		*/

	 	//check if the timestamp is fresh
	    if (abs(time() - $timestamp) > 15) {
	        return false;
	    }

	    //check signature
	    return hash_hmac('sha256', $timestamp.$token, $key) === $signature;
	}
	

}
