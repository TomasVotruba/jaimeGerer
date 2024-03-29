<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityRepository;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\CRM\PriseContact;

use AppBundle\Entity\CRM\ContactRepository;

use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\CRM\ContactFusionnerType;

use AppBundle\Service\CRM\ContactService;

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
	public function contactListeSearchAction($search = '')
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

		$impulsions = $impulsionRepository->findBy(
			array(
				'contact' => $contact,
				'priseContact' => null
			),
			array(
				'date' => 'ASC'
			)
		);

		$fusion = $contactRepository->findBy(array('compte' => $contact->getCompte()));

		return $this->render('crm/contact/crm_contact_voir.html.twig', array(
			'contact' => $contact,
			'arr_devis' => $arr_devis,
			'arr_opportunites' => $arr_opportunites,
			'arr_factures' => $arr_factures,
            'arr_factures_orga' => $arr_factures_orga,
			'impulsions' => $impulsions,
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

			if( $contact->getEmail() && $contact->getCompte()->getCompany()->getZeroBounceApiKey() ){
				$contactService = $this->get('appbundle.crm_contact_service');
				$contactService->verifierBounce($contact);
			}

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
	 * @Route("/crm/contact/fusionner/{id}/rechercher/modal", name="crm_contact_fusionner_rechercher_modal", options={"expose"=true})
	 */
	public function contactFusionnerRechercherModalAction(Contact $contact)
	{

		return $this->render('crm/contact/crm_contact_fusionner_rechercher_modal.html.twig', array(
				'contact' 	=> $contact,
		));
	}    
    
    /**
	 * @Route("/crm/contact/fusionner/{id}/rechercher", name="crm_contact_fusionner_rechercher", options={"expose"=true})
	 */
	public function contactFusionnerRechercherAjaxAction(Contact $contact, Request $request)
	{
        $contacts = [];
        if($request->get('search')){
            /* @var $contactRepository ContactRepository */
            $contactRepository = $this->getDoctrine()->getRepository(Contact::class);
            $contacts = $contactRepository->findForMerge($this->getUser()->getCompany(), $contact, $request->get('search'));
        }
        
        return $this->render('crm/contact/crm_contact_fusionner_rechercher_resultats.html.twig', array(
           'contacts' => $contacts, 
        ));
	}
    
	/**
     * @Route("/crm/contact/fusionner/recapitulatif/modal", name="crm_contact_fusionner_recapitulatif_modal", options={"expose"=true})
     */
    public function contactFusionnerRecapitulatifAction(Request $request)
    {
        if ((null !== $idContactA = $request->get('idContactA')) && (null !== $idContactB = $request->get('idContactB')) && (null !== $mode = $request->get('mode')) && in_array($mode, [ContactService::MERGE_MODE_DOUBLON, ContactService::MERGE_MODE_EVOLUTION])) {
            /* @var $contactRepository ContactRepository */
            $contactRepository = $this->getDoctrine()->getRepository(Contact::class);
            $contactA = $contactRepository->find($idContactA);
            $contactB = $contactRepository->find($idContactB);
            // @TODO Quoi faire si un contact n'a pas de compte ? (vu avec Laura, ce n'est pas normal qu'il y ait des contacts sans comptes en base, en attendant que ce soit fixé je laisse le teste sur getCompte()
            if ($contactA && $contactB && $contactA->getCompte() && $contactB->getCompte() && $contactA->getCompte()->getCompany() === $this->getUser()->getCompany() && $contactB->getCompte()->getCompany() === $this->getUser()->getCompany()) {
                /* @var $contactService ContactService */
                $contactService = $this->get('appbundle.crm_contact_service');
                if ($contactService->canContactsBeMerged($contactA, $contactB, $mode)) {
                    $contactFusionnerForm = $this->createForm(new ContactFusionnerType($contactA, $contactB), $contactA, []);
                    $contactFusionnerForm->handleRequest($request);
                    if ($contactFusionnerForm->isSubmitted() && $contactFusionnerForm->isValid()) {

                        if ($contactService->mergeContacts($contactA, $contactB)) {

                            return new JsonResponse(['success' => true], Response::HTTP_OK);
                        } else {

                            return new JsonResponse(['success' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                }

                return $this->render('crm/contact/crm_contact_fusionner_recap_modal.html.twig', [
                        'contactFusionnerForm' => isset($contactFusionnerForm) ? $contactFusionnerForm->createView() : null,
                        'mode' => $mode,
                        'contactA' => $contactA,
                        'contactB' => $contactB,
                ]);
            }
        }

        throw new NotFoundHttpException();
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

		$oldEmail = $contact->getEmail();

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

			if($contact->getEmail()){
				if( $oldEmail != $contact->getEmail() && $contact->getCompte()->getCompany()->getZeroBounceApiKey() ){
					$contactService = $this->get('appbundle.crm_contact_service');
					$contactService->verifierBounce($contact);
				}	
			} else {
				$contact->setBounce(null);
				$em->persist($contact);
				$em->flush();
			}

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
		$list = $repository->findByCompany($this->getUser()->getCompany()->getId());

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
		// unlink($path.DIRECTORY_SEPARATOR.$filename);

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

		ini_set('max_execution_time', 3600);
		
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

	/**
	 * @Route("/crm/contact/verifier-bounce/{id}/{redirectToContact}", name="crm_contact_verifier_bounce", options={"expose"=true})
	 */
	public function verifierBounce(Contact $contact, $redirectToContact = 0){

		$response = new JsonResponse();

		$arr_result = array(
			'ignored' => 0, 
			'bounce' => 0, 
			'valid' => 0,
			'warning' => 0
		);

		if( !$this->getUser()->getCompany()->getZeroBounceApiKey() ){
			return $response;
		}
		
		$contactService = $this->get('appbundle.crm_contact_service');

		//only check if the last check was more than 15 days ago
		$dateValide = $contactService->verifierBounceDateValide($contact);
		if(!$dateValide){
            $arr_result['ignored']++;
            $response->setData($arr_result);
            if($redirectToContact == 1){
				return $this->redirect(
					$this->generateUrl( 'crm_contact_voir', array('id' => $contact->getId()) )
				);
			}
			return $response;
		}

		$bounce = $contactService->verifierBounce($contact);
		if(strtoupper($bounce) == "BOUNCE"){
			$arr_result['bounce']++;
		} elseif(strtoupper($bounce) == "VALID") {
			$arr_result['valid']++;
		} else {
			$arr_result['warning']++;
		}
		
		$response->setData($arr_result);
		if($redirectToContact == 1){
			return $this->redirect(
				$this->generateUrl( 'crm_contact_voir', array('id' => $contact->getId()) )
			);
		}
		return $response;
		
	}

	/**
	 * @Route("/crm/contact/set-bounce/{id}/{bounce}", name="crm_contact_set_bounce", options={"expose"=true})
	 */
	public function setBounce(Contact $contact, $bounce){
		
		$em = $this->getDoctrine()->getManager();

		if($contact->getBounce() == "WARNING" && strtoupper(trim($bounce)) == "VALID"){
			$contact->setStopBounceWarning(true);
		}

		$contact->setBounce(strtoupper(trim($bounce)));
		$contact->setDateBounceCheck(new \DateTime(date('Y-m-d')));
		$em->persist($contact);
		$em->flush();

		return $this->redirect(
			$this->generateUrl( 'crm_contact_voir', array('id' => $contact->getId()) )
		);
	}
	
}
