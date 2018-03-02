<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
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

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\CompteFilterType;
use AppBundle\Form\CRM\ContactFusionnerType;
use AppBundle\Form\CRM\ContactFusionnerEtape2Type;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\CRM\ContactImporterMappingType;

use AppBundle\Form\SettingsType;

use libphonenumber\PhoneNumberFormat;

use PHPExcel;
use PHPExcel_IOFactory;

class ContactController extends Controller
{
	/**
	 * @Route("/crm/contact/liste", name="crm_contact_liste", options={"expose"=true})
	 */
	public function contactListeAction()
	{
		return $this->render('crm/contact/crm_contact_liste.html.twig');
	}

	/**
	 * @Route("/crm/contact/liste/ajax", name="crm_contact_liste_ajax", options={"expose"=true})
	 */
	public function contactListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');

		$arr_search = $requestData->get('search');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);
		foreach( $list as $k=>$v )
		{
			$fusion = $repository->findBy(array('compte' => $v['compte_id'], 'isOnlyProspect' => false));
			$v['fusion'] = count($fusion) > 1 ? 1 : 0;
			$list[$k] = $v;
		}
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
	 * @Route("/crm/contact/liste/search/{search}", name="crm_contact_liste_search", options={"expose"=true})
	 */
	public function contactListeSearchAction($search)
	{
		return $this->render('crm/contact/crm_contact_liste.html.twig', array(
			'search' => $search
		));
	}


	/**
	 * @Route("/crm/contact/voir/{id}", name="crm_contact_voir", options={"expose"=true})
	 */
	public function contactVoirAction(Contact $contact)
	{
		$opportuniteRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
		$docPrixRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$impulsionRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Impulsion');
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');

		$arr_opportunites = $opportuniteRepository->findByContact($contact);
		$arr_devis = $docPrixRepository->findBy(array('contact' => $contact, 'type' => 'DEVIS'));
		$arr_factures = $docPrixRepository->findBy(array('contact' => $contact, 'type' => 'FACTURE'));
		$arr_factures_orga = $docPrixRepository->findBy(array('compte' => $contact->getCompte()->getId(), 'type' => 'FACTURE'));

		$impulsion = $impulsionRepository->findOneBy(array('contact' => $contact));

		$fusion = $contactRepository->findBy(array('compte' => $contact->getCompte()));

		return $this->render('crm/contact/crm_contact_voir.html.twig', array(
				'contact' => $contact,
				'arr_devis' => $arr_devis,
				'arr_opportunites' => $arr_opportunites,
				'arr_factures' => $arr_factures,
                'arr_factures_orga' => $arr_factures_orga,
				'impulsion' => $impulsion,
				'fusion'	=> count($fusion) > 1 ? true : false
		));
	}

	/**
	 * @Route("/crm/contact/ajouter", name="crm_contact_ajouter", options={"expose"=true})
	 * @Route("/crm/contact/ajouter_depuis_compte/{compte}", name="crm_contact_ajouter_depuis_compte")
	 */
	public function contactAjouterAction(Compte $compte = null)
	{
		$contact = new Contact();
		$contact->setUserGestion($this->getUser());

		$form = $this->createForm(
				new ContactType(
						$contact->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId(),
						$compte
				),
				$contact
		);

        $secteurActivite = null;
    	if( $compte ){
            ///////////////////////////////////chercher le SA dans le repo si compte not null////////////////////////////

            $settingsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
            $secteurActivite =  $compte->getSecteurActivite();

			$form->remove('compte-name');
			$form->remove('compte');
			$form->remove('secteur');
			$form->remove('adresse');
			$form->remove('codePostal');
			$form->remove('ville');
			$form->remove('region');
			$form->remove('pays');
            $form->add('compte_name', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'Organisation',
                'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off' ),
                'data' => $compte->getNom()
            ))
            ->add('compte', 'hidden', array(
                'required' => true,
                'attr' => array('class' => 'entity-compte'),
                'data' => $compte->getId()
            ))
            ->add('secteur', 'entity', array(
                'class'=>'AppBundle:Settings',
                'property' => 'Valeur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SECTEUR')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->getUser()->getcompany()->getId())
                        ->orderBy('s.valeur');
                },
                'required' => false,
                'multiple' => true,
                'label' => 'Secteur d\'activité',
                'empty_data'  => null,
                'mapped' => false,
                'data' => array($secteurActivite)
            ))
            ->add('adresse', 'text', array(
        		'required' => true,
            	'label' => 'Adresse',
            	'data'	=> $compte->getAdresse()
        	))
            ->add('codePostal', 'text', array(
        		'required' => true,
            	'label' => 'Code postal',
            	'data'	=> $compte->getCodePostal()
        	))
            ->add('ville', 'text', array(
        		'required' => true,
            	'label' => 'Ville',
            	'data'	=> $compte->getVille()
        	))
            ->add('region', 'text', array(
        		'required' => true,
            	'label' => 'Région',
            	'data'	=> $compte->getRegion()
        	))
            ->add('pays', 'text', array(
        		'required' => true,
            	'label' => 'Pays',
            	'data'	=> $compte->getPays()
        	));
		}

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$contact->setUserCreation($this->getUser());

			$data = $form->getData();
			$contact->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));

			$em->persist($contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contact/crm_contact_ajouter.html.twig', array(
		        'secteurActivite' => $secteurActivite,
				'form' => $form->createView(),
				'compte' => $compte
		));
	}

	/**
	 * @Route("/crm/contact/fusionner/{id}", name="crm_contact_fusionner", options={"expose"=true})
	 */
	public function contactFusionnerAction(Contact $contact)
	{
		$em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();
		$new_contact = new Contact();
		//~ $new_contact->setUserGestion($this->getUser());
		$form = $this->createForm(
				new ContactFusionnerType(
						$this->getUser()->getId(),
						$this->get('router'),
						$request->get('id')
				),
				$new_contact
		);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid() && 1==0) {

			$new_contact->setDateCreation(new \DateTime(date('Y-m-d')));
			$new_contact->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($new_contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contact/crm_contact_fusionner.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact,
				'step'	=> 'step1'
		));
	}

	/**
	 * @Route("/crm/contact/fusionner/etape2/{id}", name="crm_contact_fusionner_etape2", options={"expose"=true})
	 */
	public function contactFusionnerEtape2Action(Contact $contact)
	{
		$em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();
		//~ $contact = new Contact();
		//~ $contact->setUserGestion($this->getUser());
		$form = $this->createForm(
				new ContactFusionnerType(
						$this->getUser()->getId(),
						$this->get('router'),
						$request->get('id')
				),
				$contact
		);

		//~ $request = $this->getRequest();
		$form->handleRequest($request);
		//~ if ($form->isSubmitted() && $form->isValid()) {
			//~ exit;
			//~ $first_contact = new Contact();
			$posts = $request->request->get($form->getName());
			$repository = $em->getRepository('AppBundle:CRM\Contact');
			$first_contact = $repository->findOneById($request->get('id'));
			$second_contact = $repository->findOneById($posts['contact']);
			$form = $this->createForm(
					new ContactFusionnerEtape2Type(
							$first_contact,
							$second_contact,
							$this->get('router')
					),
					$contact
			);
			//~ $contact->setDateCreation(new \DateTime(date('Y-m-d')));
			//~ $contact->setUserCreation($this->getUser());
			//~ $em = $this->getDoctrine()->getManager();
			//~ $em->persist($contact);
			//~ $em->flush();

			return $this->render('crm/contact/crm_contact_fusionner_etape2.html.twig', array(
					'form' => $form->createView(),
					'contact' => $contact,
					'step'	=> 'step2',
					'first_contact' => $first_contact,
					'second_contact' => $second_contact
			));
		//~ }

		return $this->render('crm/contact/crm_contact_fusionner.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact,
				'step'	=> 'step2'
		));
	}

	/**
	 * @Route("/crm/contact/fusionner/execution/{id}", name="crm_contact_fusionner_execution", options={"expose"=true})
	 * @Method("POST")
	 */
	public function contactFusionnerExecutionAction(Contact $contact)
	{
		$em = $this->getDoctrine()->getManager();
		//~ $contact->setUserGestion($this->getUser());

		$request = $this->getRequest();
		$posts = array_values($request->request->all());

		$repository = $em->getRepository('AppBundle:CRM\Contact');
		$first_contact = $repository->findOneById($request->get('id'));
		$second_contact = $repository->findOneById($posts[0]['second_contact_id']);


		$form = $this->createForm(
				new ContactFusionnerEtape2Type(
						$first_contact,
						$second_contact,
						$this->get('router')
				),
				$contact
		);

		$form->handleRequest($request);

		//~ if ($form->isSubmitted() && $form->isValid()) {
			$champs = $em->getClassMetadata('AppBundle:CRM\Contact')->getFieldNames();
			$contact->setDateEdition(new \DateTime(date('Y-m-d')));
			$contact->setUserEdition($this->getUser());

			// Temoin pour vérifier qu'au moins une donnée du contact2 est choisi => màj
			$fusionner_contact = false;
			$NewSettings = array();
			$newEmail = $first_contact->getEmail();;
			foreach( $posts[0] as $k=>$v )
			{
				if( is_array($v) )
				{
					if( $k == 'services_interet' || $k == 'types' || $k == 'themes_interet' )
					{
						foreach( $v as $key=>$value )
						{
							$NewSettings[] = $value;
						}
					}
				}
				else if( substr($v, -1) == 2 )
				{
					$fusionner_contact = true;

					// information choisie est celle du contact 2, on controle le champ pour le setteur de la classe Contact
					$champ = substr($v, 0, -1);
					if( $champ == 'adresse' )
					{
						$contact->setAdresse($second_contact->getAdresse());
						$contact->setCodePostal($second_contact->getCodePostal());
						$contact->setVille($second_contact->getVille());
						$contact->setRegion($second_contact->getRegion());
						$contact->setPays($second_contact->getPays());
					}
					else if( $champ == 'userGestion' )
					{
						$contact->setUserGestion($second_contact->getUserGestion());
					}
					else if( $champ == 'reseau' )
					{
						$contact->setReseau($second_contact->getReseau());
					}
					else if( $champ == 'origine' )
					{
						$contact->setOrigine($second_contact->getOrigine());
					}
					else if( $champ == 'email' )
					{
						// L'email est unique, màj après suppression du contact
						$newEmail = $second_contact->getEmail();
					}
					else if( in_array($champ, $champs) )
					{
						// Transfert de prénom => transfert de civilité
						if( $champ == 'prenom' )
						{
							$contact->setCivilite($second_contact->getCivilite());
						}
						// Le champ existe dans la bdd, on màj
						$methodSet = 'set'.ucfirst($champ);
						$methodGet = 'get'.ucfirst($champ);
						eval("\$var = \$second_contact->$methodGet();");
						eval('$contact->$methodSet($var);');
					}
				}
			}
			//exit;
			$NewSettings = array_unique($NewSettings);
			$contact->removeSettings();
			$second_contact->removeSettings();
			$repositorySettings = $em->getRepository('AppBundle:Settings');
			$ContactNewSettings = $repositorySettings->findBy(
														array('id' =>  $NewSettings),
														array('id' => 'DESC')
													);
			foreach( $ContactNewSettings as $Setting )
			{
				$contact->addSetting($Setting);
			}

			if( $fusionner_contact )
			{
				$em->persist($contact);
				$em->flush();
			}

			// màj dans les tables : devis, factures, opportunités, impulsions
			// devis etfactures
			$repositoryDevis = $em->getRepository('AppBundle:CRM\DocumentPrix');
			$Contact2Devis = $repositoryDevis->findBy(
													array('contact' => $second_contact, 'type' => array('DEVIS', 'FACTURE') ),
													array('id' => 'DESC')
												);
			foreach( $Contact2Devis as $Devis )
			{
				$Devis->setContact($first_contact);
				$em->persist($Devis);
			}

			// opportunités
			$repositoryOpportunite = $em->getRepository('AppBundle:CRM\Opportunite');
			$Contact2Opportunite = $repositoryOpportunite->findBy(
													array('contact' => $second_contact),
													array('id' => 'DESC')
												);
			foreach( $Contact2Opportunite as $Opportunite )
			{
				$Opportunite->setContact($first_contact);
				$em->persist($Opportunite);
			}

			// impulsions
			$repositoryImpulsions = $em->getRepository('AppBundle:CRM\Impulsion');
			$Contact2Impulsion = $repositoryImpulsions->findBy(
													array('contact' => $second_contact),
													array('id' => 'DESC')
												);
			foreach( $Contact2Impulsion as $Impulsion )
			{
				$Impulsion->setContact($first_contact);
				$em->persist($Impulsion);
			}

			$em->flush();
			$em->remove($second_contact);
			$em->flush();
			$contact->setEmail($newEmail);
			$em->persist($contact);
			$em->flush();
			echo 1; exit;

			return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
			));
		//~ }

		return $this->render('crm/contact/crm_contact_fusionner_etape2.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact,
				'step'	=> 'step2',
				'first_contact' => $first_contact,
				'second_contact' => $second_contact
		));
	}

	/**
	 * @Route("/crm/contact/get-contacts-fusionner/{contact_id}", name="crm_contact_get_liste_fusionner", defaults={"contact_id" = null})
	 * @Route("/crm/contact/get-contacts-fusionner", name="crm_contact_get_liste_fusionner_default")
	 */
	public function contact_list_fusionnerAction($contact_id)
	{
		$request = $this->getRequest();
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		//~ if( is_null($compte_id) )
			//~ $list = $repository->findByCompany($this->getUser()->getCompany());
		//~ else
			//~ $list = $repository->findAll($this->getUser()->getCompany(), $compte_id);
		$contact = $repository->find($request->get('id'));
		$list = $repository->findAllExcept( array($contact->getId()), $this->getUser()->getCompany(), $contact->getCompte() );

		$res = array();
		if( count($list) > 0 )
		{
			foreach ($list as $contact) {
				$_res = array('id' => $contact->getId(), 'display' => $contact->getPrenom() ." ". $contact->getNom());
				$res[] = $_res;
			}
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/contact/editer/{id}", name="crm_contact_editer", options={"expose"=true})
	 */
	public function contactEditerAction(Contact $contact)
	{
		$_compte = $contact->getCompte();
		$contact->setCompte($_compte->getId());
		$form = $this->createForm(
				new ContactType(
						$contact->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId()
				),
				$contact
		);

		$form->get('compte_name')->setData($_compte->__toString());

		$em = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();
			$contact->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));

			$contact->setDateEdition(new \DateTime(date('Y-m-d')));
			$contact->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contact/crm_contact_editer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/contact/supprimer/{id}", name="crm_contact_supprimer", options={"expose"=true})
	 */
	public function contactSupprimerAction(Contact $contact)
	{
		$form = $this->createFormBuilder()->getForm();

        $em = $this->getDoctrine()->getManager();

        $arr_impulsions  = $em->getRepository('AppBundle:CRM\Impulsion')->findByContact($contact->getId());
        $arr_factures  = $em->getRepository('AppBundle:CRM\DocumentPrix')->findByContact($contact->getId());
        $arr_actions_commerciales  = $em->getRepository('AppBundle:CRM\Opportunite')->findByContact($contact->getId());

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

            foreach ($arr_impulsions as $impulsion){
                $em->remove($impulsion);
            }

            foreach($arr_factures as $facture){
            	$facture->setContact(null);
            	$em->persist($facture);
            }

            foreach($arr_actions_commerciales as $actionCommerciale){
            	$actionCommerciale->setContact(null);
            	$em->persist($actionCommerciale);
            }

			$em->remove($contact);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_contact_liste'
			));
		}

		return $this->render('crm/contact/crm_contact_supprimer.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact
		));
	}

	/**
	 * @Route("/crm/contact/ecrire/{id}", name="crm_contact_ecrire", options={"expose"=true})
	 */
	public function contactEcrireAction(Contact $contact)
	{
		$form = $this->createFormBuilder()->getForm();

		$form->add('objet', 'text', array(
			'label' => 'Objet',
			'required' => true,
		));

		$form->add('message', 'textarea', array(
				'label' => 'Message',
				'attr' => array('class' => 'tinymce'),
				'required' => true,
				'data' => ''
		));

		$form->add('submit', 'submit', array(
  		  'label' => 'Envoyer',
		  'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$objet = $form->get('objet')->getData();
			$message = $form->get('message')->getData();

			try{
				$mail = \Swift_Message::newInstance()
					->setSubject($objet)
					->setFrom($this->getUser()->getEmail())
					->setTo($contact->getEmail())
					->setBody($message, 'text/html')
				;
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'Le message a bien été envoyé.'
				);

				$priseContact = new PriseContact();
				$priseContact->setType('EMAIL');
				$priseContact->setDate(new \DateTime(date('Y-m-d')));
				$priseContact->setDescription("Envoi d'un message via la CRM");
				$priseContact->setContact($contact);
				$priseContact->setUser($this->getUser());
				$priseContact->setMessage($message);

				$em = $this->getDoctrine()->getManager();
				$em->persist($priseContact);
				$em->flush();

			} catch(\Exception $e){
    			$error =  $e->getMessage();
    			$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
    		}

			return $this->redirect($this->generateUrl(
					'crm_contact_voir',
					array('id' => $contact->getId())
			));
		}

		return $this->render('crm/contact/crm_contact_ecrire.html.twig', array(
				'form' => $form->createView(),
				'contact' => $contact
		));

	}

	/**
	 * @Route("/crm/contact/get-compte/{id}", name="crm_contact_get_compte", options={"expose"=true})
	 */
	public function contactGetCompte(Contact $contact)
	{
		$response = new JsonResponse();
		$response->setData(array(
				'compte' => $contact->getCompte()->getNom(),
				'compte-id' => $contact->getCompte()->getId(),
		));

		return $response;

	}

	/**
	 * @Route("/crm/contact/get-contacts/{compte_id}", name="crm_contact_get_liste", defaults={"compte_id" = null}, options={"expose"=true})
	 * @Route("/crm/contact/get-contacts", name="crm_contact_get_liste_default", options={"expose"=true})
	 */
	public function contact_listAction($compte_id)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		if( is_null($compte_id) )
			$list = $repository->findByCompany($this->getUser()->getCompany(), false);
		else
			$list = $repository->findByCompanyAndCompte($this->getUser()->getCompany(), $compte_id);

		$res = array();
		foreach ($list as $contact) {
			$_res = array('id' => $contact->getId(), 'display' => $contact->getPrenom() ." ". $contact->getNom(), 'compte' => $contact->getCompte()->getId());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/contact/get-contacts-impulsion", name="crm_contact_impulsion_get_liste", options={"expose"=true})
	 */
	public function contact_impulsion_listAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$list = $repository->findAllNoImpulsion($this->getUser()->getCompany()->getId());

		$res = array();
		foreach ($list as $contact) {
			$_res = array('id' => $contact['id'], 'display' => $contact['prenom'] ." ". $contact['nom'], 'compte' => $contact['compte']->getId());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/contact/importer/mapping", name="crm_contact_importer_mapping", options={"expose"=true})
	 */
	public function contactImporterMappingAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$files = $session->get('import_contacts_filename');

		$output = array();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
		$headers = array();
		$contactData = array();
		$enteteFichierImport = array();
		foreach( $files as $k=>$v )
		{
			// charger PHPEXCEL de choisir le reader adéquat
			$objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$v['nouveau_nom']);
			// chargement du fichier xls/xlsx ou csv
			$objPHPExcel = $objReader->load($path.'/'.$v['nouveau_nom']);


			$sheetData = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);
			// changer de delimiter au cas ou le csv n'est pas conforme
			if( get_class($objReader) == 'PHPExcel_Reader_CSV' && !$sheetData[1]['B'] )
			{
				$objReader->setDelimiter(';');
				$objPHPExcel = $objReader->load($path.'/'.$v['nouveau_nom']);
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);
			}
			// chargement du header
			$enteteFichierImport[$k] = $sheetData[1];
			$headers[$k] = array_filter($sheetData[1]);
			// suppression du header et passage des informations à $contactData
			unset($sheetData[1]);
			$contactData[$k] = $sheetData;
			// construction du header
			$col = 'A';
			foreach($headers[$k] as $key => $value){
				$s =  $value.' (col '.$col.')';
				$headers[$k][$key] = $s;
				$col++;
			}

		}

		$session->set('enteteFichierImport', $enteteFichierImport);
		$form_mapping = $this->createForm(new ContactImporterMappingType($headers, $files));
		$form_mapping->handleRequest($request);
		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$champs = $em->getClassMetadata('AppBundle:CRM\Contact')->getFieldNames();

			$repositoryCompte = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
			$repositoryContact = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
			$repositoryReseau = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

			$data = $form_mapping->getData();

			$arr_mapping = array();
			$fields = array();

			foreach( $data as $k=>$v )
			{
				$champ = substr($k, 0, -1);
				$index = substr($k, -1);
				$fields[$index][$champ] = $v;
			}
			$session->set('champs_mapping', $fields);

			$arr_err_contact = array();
			$arr_err_email = array();
			$arr_err_comptes = array();
			$arr_err_reseaux = array();
			$arr_err_serviceInteret = array();
			$arr_err_secteurActivite = array();
			$arr_err_themeInteret = array();
            $arr_err_origine = array();
			$data_err = array();
			$pattern = '/[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})/i';

			foreach( $contactData as $k=>$v )
			{
				foreach( $v as $key=>$value )
				{
					$err = false;
					preg_match($pattern,$value[$fields[$k]['email']],$matches);
					if( count($matches) <= 0 )
					{
                        if($value[$fields[$k]['email']]){
                            $value['err'] = 'L\'adresse '.$value[$fields[$k]['email']].' est incorrecte pour le contact '.$value[$fields[$k]['prenom']].' '.$value[$fields[$k]['nom']];
                        }
                        else{
//                            $value['err'] = 'L\'adresse mail est absente pour le contact '.$value[$fields[$k]['prenom']].' '.$value[$fields[$k]['nom']];
                            if(!$value[$fields[$k]['telephoneFixe']]){

                                if(!$telPort = $value[$fields[$k]['telephonePortable']]){

                                    $value['err'] = 'Vous n\'avez entré ni mail, ni numéro de télephone pour le contact '.$value[$fields[$k]['prenom']].' '.$value[$fields[$k]['nom']];

                                }

                            }

                        }

						$arr_err_email[$k][] = $value;
						$err = true;

					}
					if( isset($matches[0]) )
					{
						$emailContact = $repositoryContact->findByEmailAndCompany($matches[0],$this->getUser()->getCompany());

						if( count($emailContact) > 0 )
						{
							$value['err'] = 'L\'adresse mail '.$matches[0].' existe dans la crm';
							$value[$fields[$k]['email']] = $matches[0];
							$arr_err_contact[$k][] = $value;
							$err = true;
						}
					}

					$compte = $repositoryCompte->findOneBy(array(
							'nom' => $value[$fields[$k]['compte']],
							'company' => $this->getUser()->getCompany()
					));
					if( count($compte) <= 0 )
					{
						$value['err'] = 'Le compte '.$value[$fields[$k]['compte']].' n\'existe pas';
						$arr_err_comptes[$k][] = $value;
						$err = true;
					}


					$reseau = $repositoryReseau->findOneBy(array('module' => 'CRM', 'parametre' => 'RESEAU', 'valeur' =>$value[$fields[$k]['reseau']], 'company' => $this->getUser()->getCompany() ));
					if( count($reseau) <= 0 )
					{
						$value['err'] = 'Le réseau '.$value[$fields[$k]['reseau']].' n\'existe pas';
						$arr_err_reseaux[$k][] = $value;
						$err = true;
					}

                    $origine = $repositoryReseau->findOneBy(array('module' => 'CRM', 'parametre' => 'ORIGINE', 'valeur' =>$value[$fields[$k]['origine']], 'company' => $this->getUser()->getCompany() ));
                    if( count($origine) <= 0 )
                    {
                        $value['err'] = 'L\'origine '.$value[$fields[$k]['origine']].' n\'existe pas';
                        $arr_err_origine[$k][] = $value;
                        $err = true;
                    }
                    $serviceInteret = $repositoryReseau->findOneBy(array('module' => 'CRM', 'parametre' => 'SERVICE_INTERET', 'valeur' =>$value[$fields[$k]['serviceInteret']], 'company' => $this->getUser()->getCompany() ));
                    if( count($serviceInteret) <= 0 )
                    {
                        $value['err'] = 'Le service d\'interêt '.$value[$fields[$k]['serviceInteret']].' n\'existe pas';
                        $arr_err_serviceInteret[$k][] = $value;
                        $err = true;
                    }

                    $secteurActivite = $repositoryReseau->findOneBy(array('module' => 'CRM', 'parametre' => 'SECTEUR', 'valeur' =>$value[$fields[$k]['secteurActivite']], 'company' => $this->getUser()->getCompany() ));
                    if( count($secteurActivite) <= 0 )
                    {
                        $value['err'] = 'Le secteur d\'activite '.$value[$fields[$k]['secteurActivite']].' n\'existe pas';
                        $arr_err_secteurActivite[$k][] = $value;
                        $err = true;
                    }

                    $themeInteret = $repositoryReseau->findOneBy(array('module' => 'CRM', 'parametre' => 'THEME_INTERET', 'valeur' =>$value[$fields[$k]['themeInteret']], 'company' => $this->getUser()->getCompany() ));
                    if( count($themeInteret) <= 0 )
                    {
                        $value['err'] = 'Le thème d\'interêt '.$value[$fields[$k]['themeInteret']].' n\'existe pas';
                        $arr_err_themeInteret[$k][] = $value;
                        $err = true;
                    }

					if( $err )
					{
						$data_err[$k][] = $value;
						continue;
					}

					$contact = new Contact();
					$contact->setDateCreation(new \DateTime(date('Y-m-d')));
					$contact->setUserGestion($this->getUser());
					$contact->setUserCreation($this->getUser());
					$contact->setEmail($matches[0]);
                    $contact->setOrigine($origine);
                    $contact->addSetting($serviceInteret);
                    $contact->addSetting($secteurActivite);
                    $contact->addSetting($themeInteret);
					$contact->setReseau($reseau);
					$contact->setCompte($compte);


					foreach( $fields[$k] as $cle=>$valeur )
					{
	
						if( in_array($cle, $champs) && $cle != 'filepath' && $cle != 'email' )
						{
      						
                            if($cle == "carteVoeux"){
                            	if(strtolower($value[$valeur]) == "oui"){
                            		$contact->setCarteVoeux(true);
                            	} else {
                            		$contact->setCarteVoeux(false);
                            	}
                            } elseif($cle == "newsletter"){
                            	if(strtolower($value[$valeur]) == "oui"){
                            		$contact->setNewsletter(true);
                            	} else {
                            		$contact->setNewsletter(false);
                            	}
                            } else {
                            	$methodSet = 'set'.ucfirst($cle);
								eval('$contact->$methodSet($value[$valeur]);');
                            }
						}
						else if( $cle == 'filepath' )
						{
							$em->persist($contact);

							if($compte->getAdresse() == null){
								$compte->setAdresse($contact->getAdresse());
							}
							if($compte->getCodePostal() == null){
								$compte->setCodePostal($contact->getCodePostal());
							}
							if($compte->getVille() == null){
								$compte->setVille($contact->getVille());
							}
							if($compte->getRegion() == null){
								$compte->setRegion($contact->getRegion());
							}
							if($compte->getPays() == null){
								$compte->setPays($contact->getPays());
							}
							$em->persist($compte);
							$em->flush();

						}
					}
				}
			}
			if( count($arr_err_email) > 0 || count($arr_err_contact) > 0 || count($arr_err_comptes) > 0 || count($arr_err_reseaux) > 0 || count($arr_err_origine) > 0|| count($arr_err_serviceInteret) > 0|| count($arr_err_themeInteret) > 0 || count($arr_err_secteurActivite) > 0 )
			{
				$session->set('contact_erreurs', array('email' => $arr_err_email, 'contact' => $arr_err_contact, 'comptes' => $arr_err_comptes, 'reseaux' => $arr_err_reseaux, 'origine' => $arr_err_origine, 'serviceInteret' => $arr_err_serviceInteret,'secteurActivite' => $arr_err_secteurActivite, 'themeInteret' => $arr_err_themeInteret, 'data_err' => $data_err));

				//creation du formulaire de validation
				return $this->redirect($this->generateUrl('crm_importer_validation'));

			}
			else
			{
				echo 1;
			}

			exit;

		}

		return $this->render('crm/contact/crm_contact_importer_mapping.html.twig', array(
				'form' => $form_mapping->createView(),
				'index' => $k,
				'formz' => $form_mapping
		));

	}

	/**
	 * @Route("/crm/contact/importer/validation", name="crm_importer_validation", options={"expose"=true})
	 */
	public function contactImporterValidationAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$arr_err = $session->get('contact_erreurs');
		$fields = $session->get('champs_mapping');
		$formBuilder = $this->createFormBuilder();
		$err_comptes = array();
		$err_reseaux = array();
		$err_contacts = array();
		$err_origine = array();
		$err_serviceInteret = array();
		$err_secteurActivite = array();
		$err_themeInteret = array();
		$err_emails = array();
		$repositoryReseau = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$reseau = $repositoryReseau->findBy(array('module' => 'CRM', 'parametre' => 'RESEAU', 'company' => $this->getUser()->getCompany()), array('valeur' => 'ASC') );
		$origine = $repositoryReseau->findBy(array('module' => 'CRM', 'parametre' => 'ORIGINE', 'company' => $this->getUser()->getCompany()), array('valeur' => 'ASC') );
		$serviceInteret = $repositoryReseau->findBy(array('module' => 'CRM', 'parametre' => 'SERVICE_INTERET', 'company' => $this->getUser()->getCompany()), array('valeur' => 'ASC') );
		$secteurActivite = $repositoryReseau->findBy(array('module' => 'CRM', 'parametre' => 'SECTEUR', 'company' => $this->getUser()->getCompany()), array('valeur' => 'ASC') );
        $themeInteret = $repositoryReseau->findBy(array('module' => 'CRM', 'parametre' => 'THEME_INTERET', 'company' => $this->getUser()->getCompany()), array('valeur' => 'ASC') );


        foreach( $arr_err as $entite=>$erreurs )
		{
			$key = 0;
			foreach( $erreurs as $indexFile=>$file )
			{
				switch ($entite) {
                    case 'contact' :
                        foreach ($file as $k => $v) {
                            $err_contacts[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['email']]));
                            $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                'required' => true,
                                'mapped' => false,
                                'expanded' => true,
                                'choices' => array(
                                    'cancel' => 'Ignorer le contact',
                                    'new' => 'Modifier l\'adresse e-mail',
                                ),
                                'data' => 'cancel',

                            ))
                                ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'text', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'label' => 'E-mail',
                                ));
                            $key++;
                        }
                        break;
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    case 'origine' :
                        foreach ($file as $k => $v) {
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['origine']])), $err_origine)) {
                                $err_origine[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['origine']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer une nouvelle origine dans la CRM',
                                        'existing' => 'Utiliser une origine existant dans la CRM',
                                        //~ 'cancel' => 'Exporter le contact dans un fichier pour vérification',
                                    ),
                                    'data' => 'new',
                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'choice', array(
                                        'choices' => $origine,
                                        'label' => 'origine',
                                        'required' => true
                                    ));
                            }
                            $key++;
                        }
                        break;
                    case 'serviceInteret' :
                        foreach ($file as $k => $v) {
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['serviceInteret']])), $err_serviceInteret)) {
                                $err_serviceInteret[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['serviceInteret']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer un nouveau service d\'interêt dans la CRM',
                                        'existing' => 'Utiliser un réseau existant dans la CRM',
                                        //~ 'cancel' => 'Exporter le contact dans un fichier pour vérification',
                                    ),
                                    'data' => 'new',
                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'choice', array(
                                        'choices' => $serviceInteret,
                                        'label' => 'Service d\'interêt',
                                        'required' => true
                                    ));
                            }
                            $key++;
                        }
                        break;
                    case 'secteurActivite' :
                        foreach ($file as $k => $v) {
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['secteurActivite']])), $err_secteurActivite)) {
                                $err_secteurActivite[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['secteurActivite']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer un nouveau secteur d\'activite dans la CRM',
                                        'existing' => 'Utiliser un secteur d\'activite existant dans la CRM',
                                        //~ 'cancel' => 'Exporter le contact dans un fichier pour vérification',
                                    ),
                                    'data' => 'new',
                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'choice', array(
                                        'choices' => $secteurActivite,
                                        'label' => 'Secteur d\'activite',
                                        'required' => true
                                    ));
                            }
                            $key++;
                        }
                        break;
                    case 'themeInteret' :
                        foreach ($file as $k => $v) {
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['themeInteret']])), $err_themeInteret)) {
                                $err_themeInteret[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['themeInteret']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer un nouveau thème d\'interêt dans la CRM',
                                        'existing' => 'Utiliser un thème d\'interêt existant dans la CRM',
                                    ),
                                    'data' => 'new',
                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'choice', array(
                                        'choices' => $themeInteret,
                                        'label' => 'Theme d\'interet',
                                        'required' => true
                                    ));
                            }
                            $key++;
                        }
                        break;


                    case 'comptes' :
                        foreach ($file as $k => $v) {
                            //~ $CeCompte = $fields[$erreurs]['compte'];
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['compte']])), $err_comptes)) {
                                $err_comptes[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['compte']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer un nouveau compte dans la CRM',
                                        'existing' => 'Utiliser un compte existant dans la CRM',
                                        //~ 'cancel' => 'Exporter le contact dans un fichier pour vérification',
                                    ),
                                    'data' => 'new',

                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'text', array(
                                        'required' => false,
                                        'mapped' => false,
                                        'label' => "Compte",
                                        'attr' => array('class' => 'typeahead-compte'),
                                    ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-compte', 'hidden', array(
                                        'required' => false,
                                        'attr' => array('class' => 'entity-compte'),
                                    ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-label', 'hidden', array(
                                        'required' => false,
                                        'attr' => array('class' => 'entity-compte'),
                                        'data' => $key,
                                    ));
                            }
                            $key++;
                        }
                        break;
                    case 'reseaux' :
                        foreach ($file as $k => $v) {
                            if (!in_array(strtolower(trim($v[$fields[$indexFile]['reseau']])), $err_reseaux)) {
                                $err_reseaux[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['reseau']]));
                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
                                    'required' => true,
                                    'mapped' => false,
                                    'expanded' => true,
                                    'choices' => array(
                                        'new' => 'Créer un nouveau réseau dans la CRM',
                                        'existing' => 'Utiliser un réseau existant dans la CRM',
                                        //~ 'cancel' => 'Exporter le contact dans un fichier pour vérification',
                                    ),
                                    'data' => 'new',
                                ))
                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'choice', array(
                                        'choices' => $reseau,
                                        'label' => 'Réseau',
                                        'required' => true
                                    ));
                            }
                            $key++;
                        }
                        break;
					case 'email' :
						foreach( $file as $k=>$v )
						{
							if(array_key_exists('err', $v)){

	                            if(substr($v["err"],0, 4) === "Vous"){

	                                $err_emails[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['email']]));

	                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
	                                    'required' => true,
	                                    'mapped' => false,
	                                    'expanded' => true,
	                                    'choices' => array(
	                                        'cancel' => 'Ignorer le contact',
	                                        'new2' => 'Modifier l\'adresse e-mail / téléphone',
	                                    ),
	                                    'data' => 'cancel',

	                                ))
	                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'text', array(
	                                        'required' => false,
	                                        'mapped' => false,
	                                        'label' => 'E-mail',
	                                    ))
	                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name2', 'text', array(
	                                        'required' => false,
	                                        'mapped' => false,
	                                        'label' => 'Téléphone fixe',
	                                    ));
	                                $key++;
	                            } else {
	                                $err_emails[$entite . '-' . $indexFile . '-' . $key] = strtolower(trim($v[$fields[$indexFile]['email']]));

	                                $formBuilder->add($entite . '-' . $indexFile . '-' . $key . '-radio', 'choice', array(
	                                    'required' => true,
	                                    'mapped' => false,
	                                    'expanded' => true,
	                                    'choices' => array(
	                                        'cancel' => 'Ignorer le contact',
	                                        'new' => 'Modifier l\'adresse e-mail',
	                                    ),
	                                    'data' => 'cancel',

	                                ))
	                                    ->add($entite . '-' . $indexFile . '-' . $key . '-name', 'text', array(
	                                        'required' => true,
	                                        'mapped' => false,
	                                        'label' => 'E-mail',
	                                    ));
	                                $key++;
	                            }
	                        }
						}
						break;
				}
				$key++;
			}
		}
		$formBuilder->add('submit','submit', array(
				'label' => 'Suite',
				'attr' => array('class' => 'btn btn-success')
		));

		$form = $formBuilder->getForm();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();
			$ligneExport = array();
			$ContactAjoute = array();
			$ContactIgnore = array();

			$em = $this->getDoctrine()->getManager();

			$champs = $em->getClassMetadata('AppBundle:CRM\Contact')->getFieldNames();
			$repositoryCompte = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
			$repositoryContact = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');

			$settingsRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

			$settingsReseauModele = $settingsRepo->findOneBy(array(
				'module' => 'CRM',
				'parametre' => 'RESEAU',
				'company' => null
			));
            $settingsOrigineModele = $settingsRepo->findOneBy(array(
                'module' => 'CRM',
                'parametre' => 'ORIGINE',
                'company' => null
            ));
            $settingsServiceInteretModele = $settingsRepo->findOneBy(array(
                'module' => 'CRM',
                'parametre' => 'SERVICE_INTERET',
                'company' => null
            ));
            $settingsSecteurActiviteModele = $settingsRepo->findOneBy(array(
                'module' => 'CRM',
                'parametre' => 'SECTEUR',
                'company' => null
            ));
            $settingsThemeInteretModele = $settingsRepo->findOneBy(array(
                'module' => 'CRM',
                'parametre' => 'THEME_INTERET',
                'company' => null
            ));

            $origineAdded = array();
            $serviceInteretAdded = array();
            $secteurActiviteAdded =array();
            $themeInteretAdded = array();

			$compteAdded = array();
			$reseauAdded = array();


			foreach( $arr_err['data_err'] as $k=>$v )
			{
				foreach( $v as $index=>$enr )
				{
					$save = true;
					if( in_array(strtolower(trim($enr[$fields[$k]['email']])), $err_contacts) )
					{
						$contactKey = array_search(strtolower(trim($enr[$fields[$k]['email']])), $err_contacts);
						if( $form[$contactKey.'-radio']->getData() == 'cancel' )
						{
							$save = false;
							$ligneExport[$k][] = serialize($enr);
						}
						else if( $form[$contactKey.'-radio']->getData() == 'new' )
						{
							$enr[$fields[$k]['email']] = $form[$contactKey.'-name']->getData();
						}

					}
					else if( in_array(strtolower(trim($enr[$fields[$k]['email']])), $err_emails) )
					{
						$emailKey = array_search(strtolower(trim($enr[$fields[$k]['email']])), $err_emails);
						if( $form[$emailKey.'-radio']->getData() == 'cancel' )
						{
							$save = false;
							$ligneExport[$k][] = serialize($enr);
						}

						else if( $form[$emailKey.'-radio']->getData() == 'new' )
						{
							$enr[$fields[$k]['email']] = $form[$emailKey.'-name']->getData();
						}
						else if( $form[$emailKey.'-radio']->getData() == 'new2' )
                        {
                            $enr[$fields[$k]['email']] = $form[$emailKey.'-name']->getData();
                            $enr[$fields[$k]['telephoneFixe']] = $form[$emailKey.'-name2']->getData();
                        }
						unset($err_emails[$emailKey]);
					}
					if( in_array(strtolower(trim($enr[$fields[$k]['compte']])), $err_comptes) )
					{
						$compteKey = array_search(strtolower(trim($enr[$fields[$k]['compte']])), $err_comptes);
						if( $form[$compteKey.'-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['compte']]), $compteAdded) )
						{
							$compte = new Compte();
							$compte->setDateCreation(new \DateTime(date('Y-m-d')));
							$compte->setUserCreation($this->getUser());
							$compte->setUserGestion($this->getUser());
							$compte->setNom($enr[$fields[$k]['compte']]);
							$compte->setCompany($this->getUser()->getCompany());
							$em->persist($compte);
							$em->flush();
							$compteAdded[] = strtolower($enr[$fields[$k]['compte']]);
						}
						else if( $form[$compteKey.'-radio']->getData() == 'existing' )
						{
							$compte = $repositoryCompte->findOneBy(array(
								'nom' => $form[$compteKey.'-name']->getData(),
								'company' => $this->getUser()->getCompany()
								)
							);
							$enr[$fields[$k]['compte']] = $compte->getNom();
						}
					}
					if( in_array(strtolower(trim($enr[$fields[$k]['reseau']])), $err_reseaux) ) {
                        $reseauKey = array_search(strtolower(trim($enr[$fields[$k]['reseau']])), $err_reseaux);
                        if ($form[$reseauKey . '-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['reseau']]), $reseauAdded)) {
                            $reseau = new Settings();
                            $reseau->setParametre('RESEAU');
                            $reseau->setModule('CRM');
                            $reseau->setType('LISTE');
                            $reseau->setHelpText($settingsReseauModele->getHelpText());
                            $reseau->setCategorie($settingsReseauModele->getCategorie());
                            $reseau->setTitre($settingsReseauModele->getTitre());
                            $reseau->setValeur($enr[$fields[$k]['reseau']]);
                            $reseau->setCompany($this->getUser()->getCompany());
                            $reseau->setNoTVA(false);
                            $em->persist($reseau);
                            $em->flush();
                            $reseauAdded[] = strtolower($enr[$fields[$k]['reseau']]);
                        } else if ($form[$reseauKey . '-radio']->getData() == 'existing') {
                            $reseau = $repositoryReseau->findOneById($form[$reseauKey . '-name']->getData());
                            $enr[$fields[$k]['reseau']] = $reseau->getValeur();
                        }
                    }

                    if( in_array(strtolower(trim($enr[$fields[$k]['origine']])), $err_origine) )
                    {
                        $origineKey = array_search(strtolower(trim($enr[$fields[$k]['origine']])), $err_origine);
                        if( $form[$origineKey.'-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['origine']]), $origineAdded) )
                        {
                            $origine = new Settings();
                            $origine->setParametre('ORIGINE');
                            $origine->setModule('CRM');
                            $origine->setType('LISTE');
                            $origine->setHelpText($settingsOrigineModele->getHelpText());
                            $origine->setCategorie($settingsOrigineModele->getCategorie());
                            $origine->setTitre($settingsOrigineModele->getTitre());
                            $origine->setValeur($enr[$fields[$k]['origine']]);
                            $origine->setCompany($this->getUser()->getCompany());
                            $origine->setNoTVA(false);
                            $em->persist($origine);
                            $em->flush();
                            $origineAdded[] = strtolower($enr[$fields[$k]['origine']]);
                        }
                        else if( $form[$origineKey.'-radio']->getData() == 'existing' )
                        {
                            $origine = $repositoryReseau->findOneById($form[$origineKey.'-name']->getData());
                            if($origine){
                            	$enr[$fields[$k]['origine']] = $origine->getValeur();
                            }
                        }
                    }
                    if( in_array(strtolower(trim($enr[$fields[$k]['serviceInteret']])), $err_serviceInteret) )
                    {
                        $serviceInteretKey = array_search(strtolower(trim($enr[$fields[$k]['serviceInteret']])), $err_serviceInteret);
                        if( $form[$serviceInteretKey.'-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['serviceInteret']]), $serviceInteretAdded) )
                        {
                            $serviceInteret = new Settings();
                            $serviceInteret->setParametre('SERVICE_INTERET');
                            $serviceInteret->setModule('CRM');
                            $serviceInteret->setType('LISTE');
                            $serviceInteret->setHelpText($settingsServiceInteretModele->getHelpText());
                            $serviceInteret->setCategorie($settingsServiceInteretModele->getCategorie());
                            $serviceInteret->setTitre($settingsServiceInteretModele->getTitre());
                            $serviceInteret->setValeur($enr[$fields[$k]['serviceInteret']]);
                            $serviceInteret->setCompany($this->getUser()->getCompany());
                            $serviceInteret->setNoTVA(false);
                            $em->persist($serviceInteret);
                            $em->flush();
                            $serviceInteretAdded[] = strtolower($enr[$fields[$k]['serviceInteret']]);
                        }
                        else if( $form[$serviceInteretKey.'-radio']->getData() == 'existing' )
                        {
                            $serviceInteret = $repositoryReseau->findOneById($form[$serviceInteretKey.'-name']->getData());
                            $enr[$fields[$k]['serviceInteret']] = $serviceInteret->getValeur();
                        }
                    }
                    if( in_array(strtolower(trim($enr[$fields[$k]['secteurActivite']])), $err_secteurActivite) )
                    {
                        $secteurActiviteKey = array_search(strtolower(trim($enr[$fields[$k]['secteurActivite']])), $err_secteurActivite);
                        if( $form[$secteurActiviteKey.'-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['secteurActivite']]), $secteurActiviteAdded) )
                        {
                            $secteurActivite = new Settings();
                            $secteurActivite->setParametre('SECTEUR');
                            $secteurActivite->setModule('CRM');
                            $secteurActivite->setType('LISTE');
                            $secteurActivite->setHelpText($settingsSecteurActiviteModele->getHelpText());
                            $secteurActivite->setCategorie($settingsSecteurActiviteModele->getCategorie());
                            $secteurActivite->setTitre($settingsSecteurActiviteModele->getTitre());
                            $secteurActivite->setValeur($enr[$fields[$k]['secteurActivite']]);
                            $secteurActivite->setCompany($this->getUser()->getCompany());
                            $secteurActivite->setNoTVA(false);
                            $em->persist($secteurActivite);
                            $em->flush();
                            $secteurActiviteAdded[] = strtolower($enr[$fields[$k]['secteurActivite']]);
                        }
                        else if( $form[$secteurActiviteKey.'-radio']->getData() == 'existing' )
                        {
                            $secteurActivite = $repositoryReseau->findOneById($form[$secteurActiviteKey.'-name']->getData());
                            $enr[$fields[$k]['secteurActivite']] = $secteurActivite->getValeur();
                        }
                    }
                    if( in_array(strtolower(trim($enr[$fields[$k]['themeInteret']])), $err_themeInteret) )
                    {
                        $themeInteretKey = array_search(strtolower(trim($enr[$fields[$k]['themeInteret']])), $err_themeInteret);
                        if( $form[$themeInteretKey.'-radio']->getData() == 'new' && !in_array(strtolower($enr[$fields[$k]['themeInteret']]), $themeInteretAdded) )
                        {
                            $themeInteret = new Settings();
                            $themeInteret->setParametre('THEME_INTERET');
                            $themeInteret->setModule('CRM');
                            $themeInteret->setType('LISTE');
                            $themeInteret->setHelpText($settingsThemeInteretModele->getHelpText());
                            $themeInteret->setCategorie($settingsThemeInteretModele->getCategorie());
                            $themeInteret->setTitre($settingsThemeInteretModele->getTitre());
                            $themeInteret->setValeur($enr[$fields[$k]['themeInteret']]);
                            $themeInteret->setCompany($this->getUser()->getCompany());
                            $themeInteret->setNoTVA(false);
                            $em->persist($themeInteret);
                            $em->flush();
                            $themeInteretAdded[] = strtolower($enr[$fields[$k]['themeInteret']]);
                        }
                        else if( $form[$themeInteretKey.'-radio']->getData() == 'existing' )
                        {
                            $themeInteret = $repositoryReseau->findOneById($form[$themeInteretKey.'-name']->getData());
                            $enr[$fields[$k]['themeInteret']] = $themeInteret->getValeur();
                        }
                    }
     
					if( $save )
					{

						$contact = new Contact();
						$contact->setDateCreation(new \DateTime(date('Y-m-d')));
						$contact->setUserGestion($this->getUser());
						$contact->setUserCreation($this->getUser());

						$contact->setEmail($enr[$fields[$k]['email']]);
                        $contact->setTelephoneFixe($enr[$fields[$k]['telephoneFixe']]);

						$compte = $repositoryCompte->findOneBy(array(
								'nom' => $enr[$fields[$k]['compte']],
								'company' => $this->getUser()->getCompany()
						));
						$reseau = $repositoryReseau->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'RESEAU', 'valeur' =>$enr[$fields[$k]['reseau']]) );
						$origine = $repositoryReseau->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'ORIGINE', 'valeur' =>$enr[$fields[$k]['origine']]) );
						$serviceInteret = $repositoryReseau->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'SERVICE_INTERET', 'valeur' =>$enr[$fields[$k]['serviceInteret']]) );
						$themeInteret = $repositoryReseau->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'THEME_INTERET', 'valeur' =>$enr[$fields[$k]['themeInteret']]) );
						$secteurActivite = $repositoryReseau->findOneBy(array('company' => $this->getUser()->getCompany(), 'module' => 'CRM', 'parametre' => 'SECTEUR', 'valeur' =>$enr[$fields[$k]['secteurActivite']]) );

						$contact->setReseau($reseau);
						if($origine){
							$contact->setOrigine($origine);
						}
						$contact->addSetting($serviceInteret);
						$contact->addSetting($themeInteret);
						$contact->addSetting($secteurActivite);
						$contact->setCompte($compte);

						foreach( $fields[$k] as $cle=>$valeur )
						{
	
							if( in_array($cle, $champs) && $cle != 'filepath' && $cle != 'email' )
							{
								if($cle == "carteVoeux"){
	                            	if(strtolower($enr[$valeur]) == "oui"){
	                            		$contact->setCarteVoeux(true);
	                            	} else {
	                            		$contact->setCarteVoeux(false);
	                            	}
	                            } elseif($cle == "newsletter"){
	                            	if(strtolower($enr[$valeur]) == "oui"){
	                            		$contact->setNewsletter(true);
	                            	} else {
	                            		$contact->setNewsletter(false);
	                            	}
	                            } else {
	                            	$methodSet = 'set'.ucfirst($cle);
									eval('$contact->$methodSet($enr[$valeur]);');
	                            }
							}
							else if( $cle == 'filepath' )
							{

								$em->persist($contact);

								if($compte->getAdresse() == null){
									$compte->setAdresse($contact->getAdresse());
								}
								if($compte->getCodePostal() == null){
									$compte->setCodePostal($contact->getCodePostal());
								}
								if($compte->getVille() == null){
									$compte->setVille($contact->getVille());
								}
								if($compte->getRegion() == null){
									$compte->setRegion($contact->getRegion());
								}
								if($compte->getPays() == null){
									$compte->setPays($contact->getPays());
								}
								$em->persist($compte);
								$em->flush();
							}
						}
					}
				}
			}

			$filenames = $session->get('import_contacts_filename');
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
			foreach( $filenames as $k=>$v )
			{
				//suppression du fichier temporaire
				unlink($path.'/'.$v['nouveau_nom']);
			}
			echo 1;
			exit;
		}

		return $this->render('crm/contact/crm_contact_importer_mapping_validation.html.twig', array(
				'arr_err_comptes' => $arr_err['comptes'],
				'arr_err_contact' => $arr_err['contact'],
				'arr_err_reseaux' => $arr_err['reseaux'],
				'arr_err_email' => $arr_err['email'],
				'arr_err_origine' => $arr_err['origine'],
				'arr_err_serviceInteret' => $arr_err['serviceInteret'],
				'arr_err_themeInteret' => $arr_err['themeInteret'],
				'arr_err_secteurActivite' => $arr_err['secteurActivite'],
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/contact/importer/upload", name="crm_contact_importer_upload", options={"expose"=true})
	 */
	public function contactImporterUploadAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$arr_filenames = $session->get('import_contacts_filename');
		foreach( $request->files as $file )
		{
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$arr_filenames[] = array('nom_original' => $file->getClientOriginalName(),
									 'nouveau_nom'  => $filename);
			//~ $path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_depenses';
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
			$file->move($path, $filename);

		}
		$session->set('import_contacts_filename', $arr_filenames);
		exit;
	}

	/**
	 * @Route("/crm/contact/importer/{initialisation}", name="crm_contact_importer")
	 */
	public function contactImporterAction($initialisation = false)
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$session->set('import_contacts_filename', array());

		if($initialisation){
			return $this->render('crm/contact/crm_contact_importer_initialisation.html.twig', array(

			));
		}

		return $this->render('crm/contact/crm_contact_importer.html.twig', array(
				//~ 'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/contact/valider-fichier-import/upload", name="crm_valider_fichier_import_upload")
	 */
	public function validerFichierImportUploadAction()
	{
		$formBuilder = $this->createFormBuilder();
	 	$formBuilder->add('file', 'file', array(
					'label'	=> 'Fichier',
					'required' => true,
					'attr' => array('class' => 'file-upload')
				))
				->add('submit','submit', array(
					'label' => 'Suite',
					'attr' => array('class' => 'btn btn-success')
				));

		$form = $formBuilder->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			//recuperation des données du formulaire
			$data = $form->getData();
			$file = $data['file'];
			
			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-validation_import_contact-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
			$file->move($path, $filename);

			$session = $request->getSession();
			$session->set('validation_import_contact_filename', $filename);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('crm_valider_fichier_import_resultat'));
		}

		return $this->render('crm/contact/crm_contact_valider_fichier_import_upload.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/contact/valider-fichier-import/resultat", name="crm_valider_fichier_import_resultat")
	 */
	public function validerFichierImportResultatAction()
	{

		$request = $this->getRequest();
		$session = $request->getSession();
		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
		$contactRepo = $em->getRepository('AppBundle:CRM\Contact');

		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
		$filename = $session->get('validation_import_contact_filename');

		// charger PHPEXCEL de choisir le reader adéquat
		$objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$filename);
		// chargement du fichier xls/xlsx ou csv
		$objPHPExcel = $objReader->load($path.'/'.$filename);
		$arr_data = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);

		$arr_comptes = array(
			'existant' => array(),
			'non-existant' => array(),
		);

		$arr_contacts = array(
			'existant' => array(),
			'non-existant' => array(),
			'doublons' => array(),
			'all' => array()
		);


		//start the loop at 2 to skip the header row
		for($i=2; $i<count($arr_data)+1; $i++){

			$nom = trim($arr_data[$i]['A']);
			$prenom = trim($arr_data[$i]['B']);
			$orga = trim($arr_data[$i]['E']);
			$email = trim($arr_data[$i]['J']);

			if($nom == null && $orga == null){
				break;
			}

			if($email){
				if( array_key_exists($email, $arr_contacts['all']) ){
					$arr_contacts['doublons'][] =  $prenom.' '.$nom.' ('.$orga.')';
				} else {
					$arr_contacts['all'][$email] = $prenom.' '.$nom.' ('.$orga.')';
				}
			}

			$compte = $compteRepo->findOneBy(array(
				'nom' => $orga,
				'company' => $this->getUser()->getCompany()
			));

			if($compte != null){
				if( !in_array( $orga, $arr_comptes['existant']) ){
					$arr_comptes['existant'][] = $orga;
				}
				
				$contact = $contactRepo->findBy(array(
					'compte'=> $compte,
					'prenom' => $prenom,
					'nom' => $nom
				));

				if(!$contact && $email){
					$contact = $contactRepo->findByEmailAndCompany($email, $this->getUser()->getCompany());
				}

				if($contact){
					$arr_contacts['existant'][$contact[0]->getId()] = $prenom.' '.$nom.' ('.$orga.')';
				} else {
					$arr_contacts['non-existant'][] = $prenom.' '.$nom.' ('.$orga.')';
				}
	
			} else {
				if( !in_array($orga, $arr_comptes['non-existant']) ){
					$arr_comptes['non-existant'][] = $orga;
				}
				$contact = null;
				if($email){
					$contact = $contactRepo->findByEmailAndCompany($email, $this->getUser()->getCompany());
				}

				if($contact){
					$arr_contacts['existant'][$contact[0]->getId()] = $prenom.' '.$nom.' ('.$email.')';
				} else {
					$arr_contacts['non-existant'][] = $prenom.' '.$nom.' ('.$orga.')';
				}

			}
		}

		return $this->render('crm/contact/crm_contact_valider_fichier_import_resultat.html.twig', array(
			'arr_comptes' => $arr_comptes,
			'arr_contacts' => $arr_contacts
		));
	}

	/**
	 * @Route("/crm/contact/valider-fichier-import/export/{type}/{existant}", name="crm_valider_fichier_import_export")
	 */
	public function validerFichierImportExportAction($type, $existant)
	{
		$request = $this->getRequest();
		$session = $request->getSession();
		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
		$contactRepo = $em->getRepository('AppBundle:CRM\Contact');

		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
		$filename = $session->get('validation_import_contact_filename');

		// charger PHPEXCEL de choisir le reader adéquat
		$objReader = PHPExcel_IOFactory::createReaderForFile($path.'/'.$filename);
		// chargement du fichier xls/xlsx ou csv
		$objPHPExcel = $objReader->load($path.'/'.$filename);
		
		$arr_data = $objPHPExcel->getActiveSheet()->toArray(false,true,true,true);

		$arr_contacts = array();
		
		//loop backward to avoid removing an index during the loop
		for($i=count($arr_data); $i>1; $i--){
			$nom = trim($arr_data[$i]['A']);
			$prenom = trim($arr_data[$i]['B']);
			$orga = trim($arr_data[$i]['E']);
			$email = trim($arr_data[$i]['J']);

			$compte = $compteRepo->findOneBy(array(
				'nom' => $orga,
				'company' => $this->getUser()->getCompany()
			));

			if( in_array($email, $arr_contacts) ){
				if($type == "contact" && $existant != "doublons"){
					$objPHPExcel->getActiveSheet()->removeRow($i, 1);
				}
			} else {
				$arr_contacts[] = $email;
				if($type == "contact" && $existant == "doublons"){
					$objPHPExcel->getActiveSheet()->removeRow($i, 1);
				}
			}

			if($compte != null){
				if($type == "compte" && $existant == "non-existant"){
					$objPHPExcel->getActiveSheet()->removeRow($i, 1);
				}
				
				$contact = $contactRepo->findBy(array(
					'compte'=> $compte,
					'prenom' => $prenom,
					'nom' => $nom
				));

				if(!$contact){
					$contact = $contactRepo->findByEmailAndCompany($email, $this->getUser()->getCompany());
				}


				if($contact){
					if($type == "contact" && $existant == "non-existant"){
						$objPHPExcel->getActiveSheet()->removeRow($i, 1);
					}
				} else {
					if($type == "contact" && $existant == "existant"){
						$objPHPExcel->getActiveSheet()->removeRow($i, 1);
					}
				}
	
			} else {
				if($type == "compte" && $existant == "existant"){
					$objPHPExcel->getActiveSheet()->removeRow($i, 1);
				}

				$contact = $contactRepo->findByEmailAndCompany($email, $this->getUser()->getCompany());
				if($contact && $type == "contact" && $existant == "existant"){
					$objPHPExcel->getActiveSheet()->removeRow($i, 1);
				}

			}
		}

		$response = new Response();
		$response->headers->set('Content-Type', 'application/vnd.ms-excel');
		$response->headers->set('Content-Disposition', 'attachment;filename="contacts.xlsx"');
		$response->headers->set('Cache-Control', 'max-age=0');
		$response->sendHeaders();
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit();

	}


}
