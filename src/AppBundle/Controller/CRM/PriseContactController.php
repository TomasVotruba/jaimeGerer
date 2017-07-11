<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\CRM\PriseContact;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Form\CRM\PriseContactType;

use libphonenumber\PhoneNumberFormat;

class PriseContactController extends Controller
{
	/**
	 * @Route("/crm/prise_contact/ajouter/{id}/{screen}", name="crm_prise_contact_ajouter")
	 */
	public function priseContactAjouterAction(Contact $contact, $screen)
	{
		$priseContact = new PriseContact();
		$form = $this->createForm(new PriseContactType(), $priseContact);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {

			$priseContact->setUser($this->getUser());
			$priseContact->setContact($contact);
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($priseContact);
			$em->flush();
	
			if($screen == 'impulsion'){
			
				return $this->redirect($this->generateUrl(
						'crm_impulsion_liste'
				));
			} else{
				return $this->redirect($this->generateUrl(
						'crm_contact_voir',
						array('id' => $contact->getId())
				). '#prises_contact');
			}

		}
	
		return $this->render('crm/priseContact/crm_prise_contact_ajouter.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact,
				'screen' => $screen
		));
	}

}