<?php

namespace AppBundle\Controller\CRM;


//~ use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\CRM\ContactWebForm;

use Swift_Attachment;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\Rapport;
use AppBundle\Entity\CRM\PriseContact;
use AppBundle\Entity\CRM\Impulsion;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\CompteFilterType;
use AppBundle\Form\CRM\ContactFusionnerType;
use AppBundle\Form\CRM\ContactFusionnerEtape2Type;
use AppBundle\Form\CRM\ContactWebFormType;
use AppBundle\Form\SettingsType;

use libphonenumber\PhoneNumberFormat;

class ContactWebFormController extends Controller
{

	/**
	 * @Route("/crm/contactwebForm/liste", name="crm_contactwebform_liste")
	 */
	public function contactwebformListeAction()
	{
		return $this->render('crm/contactwebForm/crm_contactwebform_liste.html.twig');
	}

	/**
	 * @Route("/crm/contactwebForm/liste/ajax", name="crm_contactwebform_liste_ajax")
	 */
	public function contactwebformListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ContactWebForm');

		$arr_search = $requestData->get('search');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany()),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value']),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/contactwebForm/ajouter", name="crm_contactwebform_ajouter")
	 */
	public function contactwebformAjouterAction()
	{
		$contact = new ContactWebForm();
		$contact->setUserGestion($this->getUser());
		$contact->setCompany($this->getUser()->getCompany());
		$form = $this->createForm(
				new ContactWebFormType(
						$contact->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId(),
						$this->getRequest()
				),
				$contact
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$contact->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_ajouter.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true
		));
	}


	/**
	 * @Route("/crm/contactwebForm/editer/{id}", name="crm_contactwebform_editer")
	 */
	public function contactwebformEditerAction(ContactWebForm $contactwebform)
	{
		$form = $this->createForm(
				new ContactWebFormType(
						$contactwebform->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId(),
						$this->getRequest()
				),
				$contactwebform
		);

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$contactwebform->setDateEdition(new \DateTime(date('Y-m-d')));
			$contactwebform->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($contactwebform);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_voir',
					array('id' => $contactwebform->getId())
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_editer.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true
		));
	}


	/**
	 * @Route("/crm/contactwebForm/supprimer/{id}", name="crm_contactwebform_supprimer")
	 */
	public function contactwebformSupprimerAction(ContactWebForm $contactwebform)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($contactwebform);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contactwebform_liste'
			));
		}

		return $this->render('crm/contactwebForm/crm_contactwebform_supprimer.html.twig', array(
				'form' => $form->createView(),
				'contactwebform' => $contactwebform
		));
	}

	/**
	 * @Route("/crm/contactwebForm/voir/{id}", name="crm_contactwebform_voir")
	 */
	public function contactwebformVoirAction(ContactWebForm $contactwebform)
	{
		//~ var_dump($contactwebform); exit;
		return $this->render('crm/contactwebForm/crm_contactwebform_voir.html.twig', array(
				'contactwebform' => $contactwebform,
		));
	}

	/**
	 * @Route("/crm/contactwebForm/addContactWeb", name="crm_contactwebform_addcontactweb")
	 */
	public function addContactWebAction()
	{

		//autoriser le cross domain
    if(isset($_SERVER['HTTP_ORIGIN'])){
      switch ($_SERVER['HTTP_ORIGIN']) {
        case 'https://www.jaime-gerer.com':
          header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
          header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
          header('Access-Control-Max-Age: 1000');
          header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
          break;
      }
    }

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\ContactWebForm');
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$request = $this->getRequest();
		$posts = array_values($request->request->all());
		$ContactWebForm = $repository->find($posts[0]['id']);
		$em = $this->getDoctrine()->getManager();
		$champs = $em->getClassMetadata('AppBundle:CRM\Contact')->getFieldNames();

		$exists = false;
		if( isset($posts[0]['email']) && ($ContactWebForm->getEmail() != '' || $posts[0]['email'] != '') ){
			$contact = $contactRepository->findOneByEmail($posts[0]['email']);
			if($contact != null){
				$exists = true;
				$compte = $contact->getCompte();
			}
		}

		if(!$exists) {
			$compte = new Compte();
			$compte->setDateCreation(new \DateTime(date('Y-m-d')));
			$compte->setUserCreation($ContactWebForm->getUserCreation());
			$contact = new Contact();
			$contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$contact->setUserCreation($ContactWebForm->getUserCreation());
		}

		// creation du compte
		if( $ContactWebForm->getNomCompte() != '' && $posts[0]['nomCompte'] != '' ) $compte->setNom($posts[0]['nomCompte']);
		if( $ContactWebForm->getUrl() != '' && $posts[0]['url'] != '' ) $compte->setUrl($posts[0]['url']);
		$compte->setUserGestion($ContactWebForm->getUserGestion());
		$compte->setCompany($ContactWebForm->getUserGestion()->getCompany());
		$em->persist($compte);
		$em->flush();

		// creation du contact
		$contact->setCompte($compte);
		if( $ContactWebForm->getPrenomContact() != '' && $posts[0]['prenomContact'] != '' ) $contact->setPrenom($posts[0]['prenomContact']);
		if( $ContactWebForm->getNomContact() != '' && $posts[0]['nomContact'] != '' ) $contact->setNom($posts[0]['nomContact']);
		if( $ContactWebForm->getAdresse() != '' && $posts[0]['adresse'] != '' ) $contact->setAdresse($posts[0]['adresse']);
		if( $ContactWebForm->getCodePostal() != '' && $posts[0]['codePostal'] != '' ) $contact->setCodePostal($posts[0]['codePostal']);
		if( $ContactWebForm->getVille() != '' && $posts[0]['ville'] != '' ) $contact->setVille($posts[0]['ville']);
		if( $ContactWebForm->getRegion() != '' && $posts[0]['region'] != '' ) $contact->setRegion($posts[0]['region']);
		if( $ContactWebForm->getPays() != '' && $posts[0]['pays'] != '' ) $contact->setPays($posts[0]['pays']);
		if( $ContactWebForm->getTelephoneFixe() != '' && $posts[0]['telephoneFixe'] != '' ) $contact->setTelephoneFixe($posts[0]['telephoneFixe']);
		if( $ContactWebForm->getTelephonePortable() != '' && $posts[0]['telephonePortable'] != '' ) $contact->setTelephonePortable($posts[0]['telephonePortable']);
		if( $ContactWebForm->getEmail() != '' && $posts[0]['email'] != '' ) $contact->setEmail($posts[0]['email']);
		if( $ContactWebForm->getFax() != '' && $posts[0]['fax'] != '' ) $contact->setFax($posts[0]['fax']);
		if( $ContactWebForm->getReseau() != '' ) $contact->setReseau($ContactWebForm->getReseau());
		if( $ContactWebForm->getOrigine() != '' ) $contact->setOrigine($ContactWebForm->getOrigine());
		if( $ContactWebForm->getCarteVoeux() != '' ) $contact->setCarteVoeux($ContactWebForm->getCarteVoeux());
		if( $ContactWebForm->getNewsletter() != '' ) $contact->setNewsletter($ContactWebForm->getNewsletter());

		// ajout de setting
		foreach( $ContactWebForm->getSettings() as $settings )
		{
			$contact->addSetting($settings);
		}

		$contact->setUserGestion($ContactWebForm->getUserGestion());
		$em->persist($contact);
		$em->flush();

		// ajout impulsion
		if( $ContactWebForm->getGestionnaireSuivi() != '' && $ContactWebForm->getDelaiUnit() != '' && $ContactWebForm->getDelaiNum() != '')
		{
			$impulsion = new Impulsion();
			$impulsion->setUser($ContactWebForm->getGestionnaireSuivi());
			$impulsion->setContact($contact);
			$impulsion->setDelaiNum($ContactWebForm->getDelaiNum());
			$impulsion->setDelaiUnit($ContactWebForm->getDelaiUnit());
			$impulsion->setDateCreation(new \DateTime(date('Y-m-d')));
			$em->persist($impulsion);
			$em->flush();
		}

		// envoi email
		if( $ContactWebForm->getEnvoyerEmail() && $posts[0]['email'] != '' )
		{
			//echo $ContactWebForm->getObjetEmail() . ' ----- '. $ContactWebForm->getUserGestion()->getEmail() .'=>'. $ContactWebForm->getExpediteur() . $posts[0]['email'] . $ContactWebForm->getCorpsEmail() . $ContactWebForm->getUserGestion()->getEmail();
			//exit;
			$mail = \Swift_Message::newInstance()
				->setSubject($ContactWebForm->getObjetEmail())
				->setFrom(array($ContactWebForm->getUserGestion()->getEmail() => $ContactWebForm->getExpediteur()))
				->setTo($posts[0]['email'])
				->setBody($ContactWebForm->getCorpsEmail(), 'text/html')
			;
			if( $ContactWebForm->getCopieEmail() ) $mail->addCc($ContactWebForm->getUserGestion()->getEmail());
			$this->get('mailer')->send($mail);
		}
		return $this->redirect($ContactWebForm->getReturnUrl());
	}


}
