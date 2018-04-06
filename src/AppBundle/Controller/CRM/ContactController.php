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
use Symfony\Component\Form\CallbackTransformer;

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
	public function contactVoir(Contact $contact)
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
	 * @Route("/crm/contact/import/upload", name="crm_contact_import_upload")
	 */
	public function importUpload()
	{
		$formBuilder = $this->createFormBuilder();
	 	$formBuilder->add('fichier_import', 'file', array(
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
			$file = $data['fichier_import'];
			
			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-validation_import_contact-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
			$file->move($path, $filename);

			$session = $request->getSession();
			$session->set('validation_import_contact_filename', $filename);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('crm_contact_import_validation'));
		}

		return $this->render('crm/contact/crm_contact_import_upload.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/contact/import/validation", name="crm_contact_import_validation")
	 */
	public function contactImportValidation()
	{

		$contactService = $this->get('appbundle.crm_contact_service');
		$arr_results = $contactService->checkContactImportFile($this->getUser()->getCompany());

		$formBuilder = $this->createFormBuilder();
		$formBuilder
			->add('update', 'checkbox', array(
				'label' => 'Mettre à jour les comptes et contacts déjà dans J\'aime le Commercial',
				'required' => false,
			))
			->add('valider', 'submit', array(
				'label' => 'Importer',
				'attr' => array('class' => 'btn btn-success')
			));
		$form = $formBuilder->getForm();

		return $this->render('crm/contact/crm_contact_import_validation.html.twig', array(
			'arr_comptes' => $arr_results['comptes'],
			'arr_contacts' => $arr_results['contacts'],
			'numHomonymes' => $arr_results['numHomonymes'],
			'arr_erreurs' => $arr_results['erreurs'],
			'form' => $form->createView()
		));
	}



	/**
	 * @Route("/crm/contact/import/importer", name="crm_contact_import_importer")
	 */
	public function contactImportImporter(){

		$request = $this->getRequest();
		$session = $request->getSession();
		$contactService = $this->get('appbundle.crm_contact_service');

		$data = $request->request->all();

		//recuperer la checkbox update dans le form
		$arr_form = $data['form'];
		$update = false;
		if(array_key_exists('update', $arr_form)){
			$update = true;
		}

		//importer
		$arr_results = $contactService->importFile($this->getUser(), $update);

		//supprimer fichier import
        $path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/contact_import';
		$filename = $session->get('validation_import_contact_filename');
		unlink($path.DIRECTORY_SEPARATOR.$filename);

		return $this->render('crm/contact/crm_contact_import_resultat.html.twig', array(
			'arr_comptes' => $arr_results['comptes'],
			'arr_contacts' => $arr_results['contacts']
		));
	}

	/**
	 * @Route("/crm/contact/valider-fichier-import/export/{type}/{existant}", name="crm_valider_fichier_import_export")
	 */
	public function validerFichierImportExport($type, $existant)
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
