<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\Rapport;

use AppBundle\Entity\CRM\CompteRepository;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\CompteFilterType;
use AppBundle\Form\CRM\CompteFusionnerType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;
use AppBundle\Form\CRM\CompteImportType;
use AppBundle\Form\CRM\CompteImportMappingType;

use AppBundle\Service\CRM\CompteService;

use AppBundle\Entity\Compta\CompteComptable;

use libphonenumber\PhoneNumberFormat;

use FOS\RestBundle\Controller\Annotations as Rest;

class CompteController extends Controller
{
	/**
	 * @Route("/crm/compte/liste", name="crm_compte_liste")
	 */
	public function compteListeAction()
	{
		return $this->render('crm/compte/crm_compte_liste.html.twig');
	}

	/**
	 * @Route("/crm/compte/liste/ajax", name="crm_compte_liste_ajax", options={"expose"=true})
	 */
	public function compteListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		$repositoryContact = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');

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
			$fusion = $repositoryContact->findBy(array('compte' => $v['id']));
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
	 * @Route("/crm/compte", name="crm_compte_datatables")
	 */
	public function compteDatatablesAction(Compte $compte)
	{
	}
	/**
	 * @Route("/crm/compte/voir/{id}", name="crm_compte_voir", options={"expose"=true})
	 */
	public function compteVoirAction(Compte $compte)
	{
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$opportuniteRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
		$docPrixRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$arr_contacts = $contactRepository->findByCompte($compte);
		$arr_opportunites = $opportuniteRepository->findByCompte($compte);
		$arr_devis = $docPrixRepository->findBy(array('compte' => $compte, 'type' => 'DEVIS'));
		$arr_factures = $docPrixRepository->findBy(array('compte' => $compte, 'type' => 'FACTURE'));


		return $this->render('crm/compte/crm_compte_voir.html.twig', array(
			'compte' => $compte,
			'arr_contacts' => $arr_contacts,
			'arr_opportunites' => $arr_opportunites,
			'arr_devis' => $arr_devis,
			'arr_factures' => $arr_factures,
		));
	}

	/**
	 * @Route("/crm/compte/ajouter", name="crm_compte_ajouter", options={"expose"=true})
	 */
	public function compteAjouterAction()
	{
		$compte = new Compte();
		$compte->setUserGestion($this->getUser());
		$compte->setCompany($this->getUser()->getCompany());
		$form = $this->createForm(
					new CompteType(
							$compte->getUserGestion()->getId(),
							$this->getUser()->getCompany()->getId()
					),
					$compte
				);
		$form->add('addressPicker', 'text', array(
			'label' => 'Veuillez renseigner l\'adresse ici',
			'mapped' => false,
			'required' => false,
		));
		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$compte->setDateCreation(new \DateTime(date('Y-m-d')));
			$compte->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();

			$nom = strtoupper(str_replace(' ','',$compte->getNom()));
			$code = substr($nom,0,4);
			$i=2;

			$ccRepo = $em->getRepository('AppBundle:Compta\CompteComptable');
			$arrCodes = $ccRepo->findCodes($this->getUser()->getCompany());

			while(in_array($code, $arrCodes)){
				$code = substr($nom,0,4);
				$code = $code.$i;
				$i++;
			}

			$em->persist($compte);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_compte_voir',
					array('id' => $compte->getId())
			));
		}

		return $this->render('crm/compte/crm_compte_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}

    /**
	 * @Route("/crm/compte/fusionner/{id}/rechercher", name="crm_compte_fusionner_rechercher", options={"expose"=true})
	 */
	public function compteFusionnerRechercherAjaxAction(Compte $compte, Request $request)
	{
        $comptes = [];
        if($request->get('search')){
            /* @var $compteRepository CompteRepository */
            $compteRepository = $this->getDoctrine()->getRepository(Compte::class);
            $comptes = $compteRepository->findForMerge($this->getUser()->getCompany(), $compte, $request->get('search'));
        }
        
        return $this->render('crm/compte/crm_compte_fusionner_rechercher_resultats.html.twig', array(
           'comptes' => $comptes, 
        ));
	}    
    
	/**
	 * @Route("/crm/compte/fusionner/{id}/rechercher/modal", name="crm_compte_fusionner_rechercher_modal", options={"expose"=true})
	 */
	public function compteFusionnerRechercherModalAction(Compte $compte)
	{

		return $this->render('crm/compte/crm_compte_fusionner_rechercher_modal.html.twig', array(
				'compte' 	=> $compte,
		));
	}
    
	/**
	 * @Route("/crm/compte/fusionner/recapitulatif/modal", name="crm_compte_fusionner_recapitulatif_modal", options={"expose"=true})
	 */    
    public function compteFusionnerRecapitulatifAction(Request $request)
    {
        if((null !== $idCompteA = $request->get('idCompteA')) && (null !== $idCompteB = $request->get('idCompteB'))){
            /* @var $compteRepository CompteRepository */
            $compteRepository = $this->getDoctrine()->getRepository(Compte::class);
            $compteA = $compteRepository->find($idCompteA);
            $compteB = $compteRepository->find($idCompteB);
            if($compteA && $compteB){
                $compteFusionnerForm = $this->createForm(new CompteFusionnerType($compteA, $compteB), $compteA, []); 
                $compteFusionnerForm->handleRequest($request);
                if($compteFusionnerForm->isSubmitted() && $compteFusionnerForm->isValid()){
                    /* @var $compteService CompteService */
                    $compteService = $this->get('appbundle.crm_compte_service');
                    $compteComptableClientToKeep = $compteFusionnerForm->has('_compteComptableClient') ? $compteFusionnerForm->get('_compteComptableClient')->getData() : null;
                    $compteComptableFournisseurToKeep = $compteFusionnerForm->has('_compteComptableFournisseur') ? $compteFusionnerForm->get('_compteComptableFournisseur')->getData() : null;
                    if($compteService->mergeComptes($compteA, $compteB, $compteComptableClientToKeep, $compteComptableFournisseurToKeep)){
                        
                        return new JsonResponse(['success' => true], Response::HTTP_OK);
                    }else{
                        
                        return new JsonResponse(['success' => false], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                
                return $this->render('crm/compte/crm_compte_fusionner_recap_modal.html.twig', [
                    'compteFusionnerForm' => $compteFusionnerForm->createView(),
                    'compteA' => $compteA,
                    'compteB' => $compteB,
                ]);                 
            }
        }
        
        throw new NotFoundHttpException();
    }

	/**
	 * @Route("/crm/compte/ajouter_modal", name="crm_compte_ajouter_modal", options={"expose"=true})
	 */
	public function compteAjouterModalAction()
	{
		$compte = new Compte();
		$compte->setUserGestion($this->getUser());
		$compte->setCompany($this->getUser()->getCompany());
		$form = $this->createForm(
					new CompteType(
							$compte->getUserGestion()->getId(),
							$this->getUser()->getCompany()->getId(),
							$this->get('router')->generate('crm_compte_ajouter_modal')
					),
					$compte
				);
		$form->add('addressPicker1', 'text', array(
			'label' => 'Veuillez renseigner l\'adresse ici',
			'mapped' => false,
			'required' => false,
		));
		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$compte->setDateCreation(new \DateTime(date('Y-m-d')));
			$compte->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($compte);
			$em->flush();

			return new JsonResponse(array(
				'status' => 'success',
				'nom' => $compte->getNom(),
				'id' => $compte->getId(),
				'adresse' => $compte->getAdresse(),
				'codePostal' => $compte->getCodePostal(),
				'ville' => $compte->getVille(),
				'region' => $compte->getRegion(),
				'pays' => $compte->getPays()

			));
		}

		return $this->render('crm/compte/crm_compte_ajouter_modal.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/compte/get-comptes-fusionner/{compte_id}", name="crm_compte_get_liste_fusionner", defaults={"compte_id" = null})
	 */
	public function compte_list_fusionnerAction($compte_id)
	{
		//~ echo "hich"; exit;
		$request = $this->getRequest();
		//~ var_dump($request->get('id')); exit;
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');

		//~ if( is_null($compte_id) )
			//~ $list = $repository->findByCompany($this->getUser()->getCompany());
		//~ else
			//~ $list = $repository->findAll($this->getUser()->getCompany(), $compte_id);
		$compte = $repository->find($compte_id);


		$list = $repository->findAllExcept($compte->getId());


		$res = array();


		if( count($list) > 0 )
		{
			foreach ($list as $compte) {

				$_res = array('id' => $compte->getId(), 'display' => $compte->getNom());

				$res[] = $_res;
			}
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/compte/editer/{id}", name="crm_compte_editer", options={"expose"=true})
	 */
	public function compteEditerAction(Compte $compte)
	{
		$em = $this->getDoctrine()->getManager();
		$contactRepository = $em->getRepository('AppBundle:CRM\Contact');
		$form = $this->createForm(
			new CompteType(
				$compte->getUserGestion()->getId(),
				$this->getUser()->getCompany()->getId()
			), $compte
		);

		$form->add('addressPicker', 'text', array(
			'label' 	=> 'Veuillez renseigner l\'adresse ici',
			'mapped' 	=> false,
			'required' 	=> false
		));
		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$compte->setDateEdition(new \DateTime(date('Y-m-d')));
			$compte->setUserEdition($this->getUser());
			
			$em->persist($compte);
			$em->flush();

			if($form->get('updateContacts')->getData() == 1){
				$arr_contacts = $contactRepository->findByCompte($compte);
				foreach($arr_contacts as $contact){
					$contact->setAdresse($compte->getAdresse());
					$contact->setVille($compte->getVille());
					$contact->setCodePostal($compte->getCodePostal());
					$contact->setRegion($compte->getRegion());
					$contact->setPays($compte->getPays());
					$em->persist($contact);
				}
				$em->flush();
			}

			return $this->redirect($this->generateUrl(
				'crm_compte_voir',
				array('id' => $compte->getId())
			));
		}

		return $this->render('crm/compte/crm_compte_editer.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/compte/supprimer/{id}", name="crm_compte_supprimer", options={"expose"=true})
	 */
	public function compteSupprimerAction(Compte $compte)
	{
		$em = $this->getDoctrine()->getManager();
		$contactRepo = $em->getRepository('AppBundle:CRM\Contact');
		$documentPrixRepo = $em->getRepository('AppBundle:CRM\DocumentPrix');

		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		$arr_contacts = $contactRepo->findByCompte($compte);
		$arr_documentPrix = $documentPrixRepo->findByCompte($compte);

		if ($form->isSubmitted() && $form->isValid()) {

			foreach($arr_contacts as $contact){
				$em->remove($contact);
			}
			$em->flush();

			$em->remove($compte);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'crm_compte_liste'
			));
		}

		return $this->render('crm/compte/crm_compte_supprimer.html.twig', array(
			'form' => $form->createView(),
			'compte' => $compte,
			'countContacts' => count($arr_contacts),
			'countDocumentPrix' => count($arr_documentPrix)
		));
	}

	/**
	 * @Route("/crm/compte/get_coordonnees/{nom}", name="crm_compte_get_coordonnees", options={"expose"=true})
	 */
	public function compteGetCoordonnees($nom)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		$compte = $repository->findOneBy(array(
			'nom' => $nom,
			'company' => $this->getUser()->getCompany()
		));
		/*
		$s_telephone = null;
		if($compte->getTelephone()){
			$s_telephone = $this->get('libphonenumber.phone_number_util')->format($compte->getTelephone(), 'INTERNATIONAL');
		}

		$s_fax = null;
		if($compte->getFax()){
			$s_fax = $this->get('libphonenumber.phone_number_util')->format($compte->getFax(), 'INTERNATIONAL');
		}
		*/
		$response = new JsonResponse();
		$response->setData(array(
    		'adresse' => $compte->getAdresse(),
			'codePostal' => $compte->getCodePostal(),
			'ville' => $compte->getVille(),
			'region' => $compte->getRegion(),
			'pays' => $compte->getPays(),
			'telephone' => $compte->getTelephone(),
			'fax' => $compte->getFax()
		));

		return $response;

	}

	/**
	 * @Route("/crm/compte/get_coordonnees_by_id/{id}", name="crm_compte_get_coordonnees_by_id", options={"expose"=true})
	 */
	public function compteGetCoordonneesById(Compte $compte)
	{
		/*
		$s_telephone = null;
		if($compte->getTelephone()){
			$s_telephone = $this->get('libphonenumber.phone_number_util')->format($compte->getTelephone(), 'INTERNATIONAL');
		}

		$s_fax = null;
		if($compte->getFax()){
			$s_fax = $this->get('libphonenumber.phone_number_util')->format($compte->getFax(), 'INTERNATIONAL');
		}
		*/

		$response = new JsonResponse();
		$response->setData(array(
                'secteur' => $compte->getSecteurActivite(),
				'adresse' => $compte->getAdresse(),
				'codePostal' => $compte->getCodePostal(),
				'ville' => $compte->getVille(),
				'region' => $compte->getRegion(),
				'pays' => $compte->getPays(),
				'telephone' => $compte->getTelephone(),
				'fax' => $compte->getFax(),
				'priveOrPublic' => $compte->getPriveOrPublic()
		));

		return $response;
	}

	/**
	 * @Route("/crm/compte/get-comptes",
	 *   name="crm_compte_get_liste",
	 *   options={"expose"=true}
	 * )
	 */
	public function compte_listAction() {

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');

		$list = $repository->findByCompany($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $compte) {
			$_res = array('id' => $compte->getId(), 'display' => $compte->getNom());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/compte/get-comptes-and-contacts", name="crm_compte_contacts_get_liste")
	 */
	public function compte_contacts_listAction() {

		$compteRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		$list = $compteRepository->findByCompany($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $compte) {
			$_res = array('id' => $compte->getId(), 'display' => $compte->getNom());
			$res[] = $_res;
		}

		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$list = $contactRepository->findByCompany($this->getUser()->getCompany());
		foreach ($list as $contact) {
			$_res = array('id' => $contact->getCompte()->getId(), 'display' => $contact->getCompte()->getNom().' ('.$contact.')');
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/compte/importer", name="crm_compte_importer", options={"expose"=true})
	 */
	public function compteImporterAction()
	{
		$form = $this->createForm(new CompteImportType($this->getUser()->getCompany()));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			//recuperation des données du formulaire
			$data = $form->getData();
			$file = $data['file'];

			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/import_comptes';
			$file->move($path, $filename);
			$session = $request->getSession();
			$session->set('import_comptes_filename', $filename);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('crm_compte_importer_mapping'));
		}

		return $this->render('crm/compte/crm_compte_importer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/compte/importer/mapping", name="crm_compte_importer_mapping")
	 */
	public function compteImporterMappingAction()
	{
		$request = $this->getRequest();
		$session = $request->getSession();

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/import_comptes';
		$filename = $session->get('import_comptes_filename');
		$fh = fopen($path.'/'.$filename, 'r+');

		//récupération de la première ligne pour créer le formulaire de mapping
		$arr_headers = array();
		$i = 0;

		while( ($row = fgetcsv($fh)) !== FALSE && $i<1 ) {
			$arr_headers = explode(';',$row[$i]);
			$i++;
		}
		$arr_headers = array_combine($arr_headers, $arr_headers); //pour que l'array ait les mêmes clés et valeurs

		// 		$col = 'A';
		// 		foreach($arr_headers as $key => $header){
		// 			$arr_headers[$key] = $header.' (col '.$col.')';
		// 			$col++;
		// 		}

		$form_mapping = $this->createForm(new CompteImportMappingType($arr_headers, $filename));
		$form_mapping->handleRequest($request);

		if ($form_mapping->isSubmitted() && $form_mapping->isValid()) {

			$data = $form_mapping->getData();

			$arr_mapping = array();
			//recuperation des colonnes
			$arr_mapping['objet'] = $data['objet'];
			$arr_mapping['num'] = $data['num'];
			$arr_mapping['compte'] = $data['compte'];
			$arr_mapping['date'] = $data['date'];
			$arr_mapping['echeance'] = $data['echeance'];
			$arr_mapping['tva'] = $data['tva'];
			$arr_mapping['tauxTVA'] = $data['tauxTVA'];
			$arr_mapping['remise'] =$data['remise'];
			$arr_mapping['description'] = $data['description'];
			$arr_mapping['etat'] =$data['etat'];
			$arr_mapping['user'] =$data['user'];

			$arr_mapping['produitNom'] = $data['produitNom'];
			$arr_mapping['produitType'] = $data['produitType'];
			$arr_mapping['produitDescription'] = $data['produitDescription'];
			$arr_mapping['produitTarif'] = $data['produitTarif'];
			$arr_mapping['produitQuantite'] = $data['produitQuantite'];

			$session->set('import_historique_facture_arr_mapping', $arr_mapping);

			//creation du formulaire de validation
			return $this->redirect($this->generateUrl('compta_facture_importer_historique_validation'));
		}

		return $this->render('compta/facture/compta_facture_importer_historique_mapping.html.twig', array(
				'form' => $form_mapping->createView()
		));

		return 0;
	}


	/**
	 * @Route("/crm/compte/verifier-bounce/{id}", name="crm_compte_verifier_bounce")
	 */
	public function compteVerifierBounceAction(Compte $compte)
	{
		$contactRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
		$arr_contacts = $contactRepository->findByCompte($compte);

		$contactService = $this->get('appbundle.crm_contact_service');

		$arr_result = array(
			'ignored' => 0, 
			'bounce' => 0, 
			'valid' => 0,
			'warning' => 0
		);

		foreach($arr_contacts as $contact){
			//only check if the last check was more than 15 days ago
			$dateValide = $contactService->verifierBounceDateValide($contact);
			if($dateValide){
	            $bounce = $contactService->verifierBounce($contact);
	            if(strtoupper($bounce) == "BOUNCE"){
					$arr_result['bounce']++;
				} elseif(strtoupper($bounce) == "VALID") {
					$arr_result['valid']++;
				} else {
					$arr_result['warning']++;
				}
			} else {
				 $arr_result['ignored']++;
			}
		}	

		$result = '<strong>Vérification des adresses emails terminées</strong><ul><li>Valides : '.$arr_result['valid'].'</li><li>Bounces : '.$arr_result['bounce'].'</li><li>Bounces potentiels : '.$arr_result['warning'].'</li><li>Ignorés : '.$arr_result['ignored'].'</li></ul>';
		$this->get('session')->getFlashBag()->add(
			'info',
			$result
		);

		return $this->redirect(
			$this->generateUrl( 'crm_compte_voir', array('id' => $compte->getId()) )
		);
	}

}
