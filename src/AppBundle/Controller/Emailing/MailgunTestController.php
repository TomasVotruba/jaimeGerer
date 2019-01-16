<?php

namespace AppBundle\Controller\Emailing;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Mailgun\Mailgun;


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

		$key = 'key-0be179cdcb7a0ef5648920d7e744c1ad';
		$domain = 'sandbox4401bff394d34fec9b0f77bcfffd3ee6.mailgun.org';

		$mgClient = new \Mailgun\Mailgun($key);
		
		//Make the call to the client.
		$result = $mgClient->sendMessage($domain, array(
		    'from'    => 'gilquin@nicomak.eu',
		    'to'      => array('laura@web4change.com', 'slac@nicomak.eu'),
		    'subject' => 'Test API',
		    'text'    => 'Testing some Mailgun awesomness !',
		    'html'    => 'Testing some Mailgun awesomness ! <a href="www.nicomak.eu">Coucou le site web</a>',
		    'recipient-variables' => '{"bob@example.com": {"first":"Bob", "id":1}, "alice@example.com": {"first":"Alice", "id": 2}}',
		    'v:my-custom-data'   => "{'id' => 54}",
		    'o:testmode' => 'true'
		));

	 	return new Response();
	}

	/**
	 * @Route("/emailing/test/webhook", name="emailing_test_webhook")
	 */
	public function testWebhook(Request $request){
		
		echo('Testing webhook');

		$key = 'key-0be179cdcb7a0ef5648920d7e744c1ad';

		$reponse = new Response();

		//check the signature
		if( $this->verifiyWebhookCall($key, $request->request->get('token'), $request->request->get('timestamp'), $request->request->get('signature') ) === false ){
			$reponse->setStatusCode('401');
			return $reponse;
		}


		$event = $request->request->get('event');
		echo($event);

		try{
			$this->get('logger')->error($e->getMessage());
			$mail = \Swift_Message::newInstance()
				->setSubject('mailgun webhook')
				->setFrom('gilquin@nicomak.eu')
				->setTo('gilquin@nicomak.eu')
				->setBody('Event : '.$event, 'text/html')
			;
			$this->get('mailer')->send($mail);
		} catch(\Exception $e){
			echo($e->getMessage());
			$this->get('logger')->error($e->getMessage());
		}
		
		
		return new Response();
		
	}

	/**
	 * @Route("/emailing/test/events", name="emailing_test_events")
	 */
	public function testEvents(){

		$key = 'key-0be179cdcb7a0ef5648920d7e744c1ad';
		$domain = 'sandbox4401bff394d34fec9b0f77bcfffd3ee6.mailgun.org';

		$mgClient = new \Mailgun\Mailgun($key);
		$result = $mgClient->get("$domain/events");

		dump($result);

		return new Response();
	}

	/**
	 * Check request signature when receiving a call from a Mailgun webhook
	 **/
	private function verifiyWebhookCall($key, $token, $timestamp, $signature){
		
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
	    return hash_hmac('sha256', $timestamp.$token, $apiKey) === $signature;
	}
	

}
