<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\CRM\Rapport;

use AppBundle\Form\CRM\CompteType;
use AppBundle\Form\CRM\RapportFilterType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;
use AppBundle\Form\CRM\RapportType;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class RapportController extends Controller
{	
	
	/**
	 * @Route("/crm/rapport/liste/{type}/{user}", name="crm_rapport_liste")
	 */
	public function rapportListeAction($type, $user=null)
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Rapport');
	
		$criteria = array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'CRM',
				'type' => $type
		);
		
		if($user == 'mine'){
			$criteria['userCreation'] = $this->getUser();
		}
		
		$list = $repository->findBy($criteria);
		
		return $this->render('crm/rapport/crm_rapport_liste.html.twig', array(
				'list' => $list,
				'type' => $type,
				'user' => $user
		));
	}
	
	/**
	 * @Route("/crm/rapport/ajouter/{type}", name="crm_rapport_ajouter")
	 * @Route("/crm/rapport/ajouter/{type}/{module}", requirements={"module" = "CRM|Emailing"}, name="crm_rapport_emailing_ajouter")
	 */
	public function rapportAjouterAction($type, $module='CRM')
	{
		$em = $this->getDoctrine()->getManager();
        $settingsRepo = $em->getRepository('AppBundle:Settings');
        
		$rapport = new Rapport();
		$form = $this->createForm(new RapportType($type), $rapport);
			
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted()) {
			$rapport->setCompany($this->getUser()->getCompany());
			$rapport->setModule($module);
			$rapport->setType($type);
			$rapport->setDateCreation(new \DateTime(date('Y-m-d')));
			$rapport->setUserCreation($this->getUser());
			
			$em->persist($rapport);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_rapport_voir',
					array('id' => $rapport->getId())
			));
		}

		$organisation = $this->getUser()->getCompany();
		$opportunityStatutList = $settingsRepo->findBy(array('company'=> $organisation,'parametre'=> 'OPPORTUNITE_STATUT'));
	
		return $this->render('crm/rapport/crm_rapport_ajouter.html.twig', array(
				'form' => $form->createView(),
                'opportuniteList' => $opportunityStatutList
		));
	
	}
	
	
	private function getHTMLRender($col) {
		$arr_readOnly_cols = array(
				'gestionnaire', 'type', 'origine', 'reseau', 'carte_voeux', 'services_interet', 'themes_interet', 'num', 'compte', 'contact'
		);
		if (in_array($col, $arr_readOnly_cols)) {
			//if($col == 'gestionnaire' || $col == 'type' || $col == 'origine' || $col == 'reseau' || $col == 'carte_voeux' || $col == 'services_interet' || $col == 'themes_interet' || $col == 'num' || $col == 'compte' || $col == 'contact'){
			return array('data' => $col, 'readOnly' => true, 'renderer' => 'html');
		} else {
			return array('data' => $col, 'renderer' => 'html');
		}
	}
	
	
	/**
	 * @Route("/crm/rapport/voir/{id}", name="crm_rapport_voir")
	 */
	public function rapportVoirAction(Rapport $rapport)
	{


        //mautic session
        $tokenMautic = $this->get('session')->get('accessTokenData');
        //sert a savoir apres une redirection si l'utilisateur avait demandé un export de segment vers mautic
        if($this->get('session')->get('actionInit') || $this->get('session')->get('actionInit') !== null ){
            $actionInit = true;
        }
        else{
            $actionInit = null;
        }
        $this->get('session')->set('actionInit', null);
        //pour redirection apres la connexion mautic
        if($tokenMautic === null) {
            $this->get('session')->set('redirection', 'crm_rapport_voir');
            //Est necessaire pour la redirection vers le bon rappport apres avoir été redirigé pour authentification
            $this->get('session')->set('rapportId' , $rapport->getId());
        }


		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		
		$arr_data = array();
		$arr_data = json_decode($rapport->getData(), true);
		
		$arr_headers = array();
		$arr_columns = array();
		
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$arr_filters = $filterRepo->findByRapport($rapport);

		switch($rapport->getType()){
			case 'compte':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
				$arr_headers_keys = array(
						'nom' =>	'Nom',
						'adresse' => 'Adresse',
						'ville' =>	'Ville',
						'codePostal' =>	'Code postal',
						'region' =>	'Region',
						'pays' =>	'Pays',
						'telephone' =>	'Telephone',
						'fax' =>	'Fax',
						'url' =>	'Site web',
						'description' => 'Description',
						'gestionnaire' => 'Gestionnaire du compte',
						'secteurActivite' => 'Secteur d\'activité'
				);
				break;
				
			case 'contact':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
				$arr_headers_keys = array(
						'prenom' => 'Prénom',
						'nom' =>	'Nom',
						'compte' => 'Organisation',
						'titre' => 'Titre',
						'adresse' => 'Adresse',
						'ville' =>	'Ville',
						'codePostal' =>	'Code postal',
						'region' =>	'Region',
						'pays' =>	'Pays',
						'telephoneFixe' =>	'Telephone fixe',
						'telephonePortable' =>	'Telephone portable',
						'fax' =>	'Fax',
						'email' =>	'Email',
						'description' =>	'Description',
						'type' => 'Type de relation commerciale',
						'reseau' => 'Réseau',
						'origine' => 'Origine',
						'themes_interet' => 'Thèmes d\'intérêt',
						'services_interet' => 'Services d\'intérêt',
						'carte_voeux' => 'Carte de voeux',
						'newsletter' => 'Newsletter',
						'gestionnaire' =>	'Gestionnaire du contact',
				);
				break;
				
			case 'devis':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
				$arr_headers_keys = array(
						'objet' => 'Objet',
						'num' => 'Numéro de devis',
						'date_validite' => 'Date de validité',
						'compte' =>	'Compte',
						'contact' => 'Contact',
						'adresse' => 'Adresse',
						'ville' =>	'Ville',
						'codePostal' =>	'Code postal',
						'region' =>	'Region',
						'description' =>	'Description',
						'gestionnaire' =>	'Gestionnaire du devis',
						'dateCreation' => 'Date de création',
						'dateEdition' => 'Date de modification',
						'total_ht' => 'Total HT',
						'total_ttc' => 'Total TTC',
				);
				break;
			case 'facture':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
				$arr_headers_keys = array(
						'objet' => 'Objet',
						'num' => 'Numéro de facture',
						'date_validite' => 'Date de validité',
						'compte' =>	'Compte',
						'contact' => 'Contact',
						'adresse' => 'Adresse',
						'ville' =>	'Ville',
						'codePostal' =>	'Code postal',
						'region' =>	'Region',
						'description' =>	'Description',
						'gestionnaire' =>	'Gestionnaire de la facture',
						'bon_commande_interne' =>	'Numéro de bon de commande interne',
						'bon_commande_client' =>	'Numéro de bon de commande client',
						'dateCreation' => 'Date de création',
						'dateEdition' => 'Date de modification',
				);
				break;
			case 'opportunite':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
				$arr_headers_keys = array(
						'nom' => 'Nom',
						'montant' => 'Montant',
						'echeance' => 'Echéance',
						'statut' => 'Statut',
						'type' =>	'Type',
						'origine' => 'Origine',
						'probabilite' => 'Probabilite',
						'compte' =>	'Compte',
						'contact' =>	'Contact',
						'gestionnaire' =>	"Gestionnaire de l'opportunité",
						'dateCreation' => 'Date de création',
						'dateEdition' => 'Date de modification',
				);
				break;
		}

		if (($rapport->getType() == 'devis') || ($rapport->getType() == 'facture')) {
			$arr_obj = $objRepo->createQueryAndGetResult($arr_filters,$rapport->getType(), $this->getUser()->getCompany());
		} else {
			$arr_obj = $objRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany());
		}

		$arr_new = array();
 		
 		if($arr_data != null){
 			
 			foreach($arr_obj as $obj){
 				$found = false;
 				foreach($arr_data as $data){
 					$id = $data['id'];
 					if($obj->getId() == $id){
 						$found = true;
 						break;
 					}
 				}
 				if(!$found){
 					$arr_new[] = $obj;
 				}
 			}
 		
	 		foreach($arr_data as $data){
	 			$keys = array_keys($data);
	 			for($i = 0; $i<count($data); $i++){
	 				$col = $keys[$i];
	 				if($col != 'id'){
		 				if(key_exists($col, $arr_headers_keys) && !in_array($arr_headers_keys[$col], $arr_headers)) {
		 					$arr_headers[] = $arr_headers_keys[$col];
		 					
		 					/*if (in_array($col, $arr_readOnly_cols)) {
		 					//if($col == 'gestionnaire' || $col == 'type' || $col == 'origine' || $col == 'reseau' || $col == 'carte_voeux' || $col == 'services_interet' || $col == 'themes_interet' || $col == 'num' || $col == 'compte' || $col == 'contact'){ 
		 						$arr_columns[] = array('data' => $col, 'readOnly' => true, 'renderer' => 'html');
		 					} else {
		 						$arr_columns[] = array('data' => $col, 'renderer' => 'html');
		 					}*/
		 					$arr_columns[] = $this->getHTMLRender($col);
		 				} 
		 			} 
	 			}
	 			for($i = 0; $i<count($data); $i++){
	 				$col = $keys[$i];
	 				if($col != 'id'){
		 				if(!key_exists($col, $arr_headers_keys) && !in_array($col, $arr_headers)) {
		 					$arr_headers[] = $col;
		 					$arr_columns[] = array('data' => $col, 'renderer' => 'html');
		 				 }
	 				}
	 			}
	 		}
	 		
	 		$arr_new_data = $this->_rapportProcessData($rapport->getType(), $arr_new);
	 		$arr_data = array_merge($arr_data, $arr_new_data);
	 		
 		} else {
 			
 			$arr_data = $this->_rapportProcessData($rapport->getType(), $arr_obj);
				
			foreach($arr_headers_keys as $col => $header){
				$arr_headers[] = $header;
				
				/*if (in_array($col, $arr_readOnly_cols)) {
				//if($col == 'gestionnaire' || $col == 'type' || $col == 'origine' || $col == 'reseau' || $col == 'carte_voeux' || $col == 'services_interet' || $col == 'themes_interet' || $col == 'num' || $col == 'compte' || $col == 'contact'){
					$arr_columns[] = array('data' => $col, 'readOnly' => true, 'renderer' => 'html');
				} else {
					$arr_columns[] = array('data' => $col, 'renderer' => 'html');
				}*/
				$arr_columns[] = $this->getHTMLRender($col);
			}
 		}
 		
 		//~ \Doctrine\Common\Util\Debug::dump($arr_data);
 		
		return $this->render('crm/rapport/crm_rapport_voir.html.twig', array(
				'arr_obj' => $arr_data,
				'arr_headers' => $arr_headers,
				'arr_columns' => $arr_columns,
				'rapport' => $rapport,
				'hide_tiny' => true,
                'type' => $rapport->getType(),
                'token' => $tokenMautic,
                'actionInit' => $actionInit
		));
	}
	
	
	/**
	 * @Route("/crm/rapport/editer/{id}", name="crm_rapport_editer")
	 */
	public function rapportEditerAction(Rapport $rapport)
	{
		$em = $this->getDoctrine()->getManager();
		$settingsRepo = $em->getRepository('AppBundle:Settings');

		$form = $this->createForm(new RapportType( $rapport->getType() ), $rapport);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted()) {
			
			 $em->persist($rapport);
			 $em->flush();
			
			return $this->redirect($this->generateUrl(
					'crm_rapport_voir',
					array('id' => $rapport->getId())
			));
	
		}

		$organisation = $this->getUser()->getCompany();
        $opportunityStatutList = $settingsRepo->findBy(array('company'=> $organisation,'parametre'=> 'OPPORTUNITE_STATUT'));
	
		return $this->render('crm/rapport/crm_rapport_editer.html.twig', array(
			'form' => $form->createView(),
			'rapport' => $rapport,
			'opportuniteList' => $opportunityStatutList
		));
	}
	
	
	/**
	 * @Route("/crm/rapport/supprimer/{id}", name="crm_rapport_supprimer")
	 */
	public function rapportSupprimerAction(Rapport $rapport)
	{
		$form = $this->createFormBuilder()->getForm();
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted() && $form->isValid()) {
	
			$type = $rapport->getType();
			
			$em = $this->getDoctrine()->getManager();
			$em->remove($rapport);
			$em->flush();
	
			return $this->redirect($this->generateUrl(
					'crm_rapport_liste', array('type' => $type)
			));
		}
	
		return $this->render('crm/rapport/crm_rapport_supprimer.html.twig', array(
				'form' => $form->createView(),
				'rapport' => $rapport
		));
	}
	
	/**
	 * @Route("/crm/rapport/enregistrer", name="crm_rapport_enregistrer")
	 */
	public function rapportEnregistrerAction()
	{
		//~ echo "<pre>";
		//~ print_r($arr_data);
		//~ echo "\n";
		//~ print_r($_POST); echo "</pre>"; exit;
		$request = $this->getRequest();
	
		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		
		$id = $request->request->get('id');
		//~ $arr_data = json_decode($request->request->get('data'), true);
		//~ var_dump($arr_data);exit;

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Rapport');
		$rapport = $repository->find($id);

		$data = json_decode($request->request->get('data'), true);
		//~ $data = json_decode($data);
		$data_json = $serializer->serialize($data, 'json');
		//~ var_dump($data_json); exit;
		$rapport->setData($data_json);
		
		$cols = $request->request->get('cols');
		$cols_json = $serializer->serialize($cols, 'json');
		$rapport->setCols($cols_json);
		
		$em = $this->getDoctrine()->getManager();
		$em->persist($rapport);
		$em->flush();
		
		$response = new JsonResponse();
		$response->setData('ok');
		
		return $response;
	
	}
	
	/**
	 * @Route("/crm/rapport/row_maj", name="crm_rapport_row_maj")
	 */
	public function rapportRowMajAction()
	{
		//~ var_dump($_POST); exit;
		$request = $this->getRequest();
		
		$data = $request->request->get('data');
		$type = $request->request->get('type');
		
		$em = $this->getDoctrine()->getManager();
		
		switch($type){
			
			case 'compte':
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
				$compte = $repository->find($data['id']);
				
				$compte->setNom($data['nom']);
				$compte->setAdresse($data['adresse']);
				$compte->setVille($data['ville']);
				$compte->setCodePostal($data['codePostal']);
				$compte->setRegion($data['region']);
				$compte->setPays($data['pays']);
// 				if($data['telephone'] != null){
// 					$compte->setTelephone( $this->get('libphonenumber.phone_number_util')->parse($data['telephone'], 'INTERNATIONAL'));
// 				}
// 				if($data['fax'] != null){
// 					$compte->setFax($this->get('libphonenumber.phone_number_util')->parse($data['fax'], 'INTERNATIONAL'));
// 				} 
				$compte->setTelephone($data['telephone']);
				$compte->setFax($data['fax']);
				$compte->setUrl($data['url']);
				$compte->setDescription($data['description']);

				$em->persist($compte);
				$em->flush();
				
				break;
				
			case 'contact':
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
				$contact = $repository->find($data['id']);
			
				$contact->setPrenom($data['prenom']);
				$contact->setNom($data['nom']);
				$contact->setAdresse($data['adresse']);
				$contact->setVille($data['ville']);
				$contact->setCodePostal($data['codePostal']);
				$contact->setRegion($data['region']);
				$contact->setPays($data['pays']);
				if($data['telephoneFixe'] != null){
					$contact->setTelephoneFixe( $this->get('libphonenumber.phone_number_util')->parse($data['telephoneFixe'], 'FR'));
				}
				if($data['telephonePortable'] != null){
					$contact->setTelephonePortable( $this->get('libphonenumber.phone_number_util')->parse($data['telephonePortable'], 'FR'));
				}
				if($data['fax'] != null){
					$contact->setFax($this->get('libphonenumber.phone_number_util')->parse($data['fax'], 'FR'));
				}
				$contact->setEmail($data['email']);
				$contact->setDescription($data['description']);
				
				$em->persist($contact);
				$em->flush();
				
				break;
				
			case 'devis':
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
				$devis = $repository->find($data['id']);
			
				$devis->setObjet($data['objet']);
				$devis->setAdresse($data['adresse']);
				$devis->setVille($data['ville']);
				$devis->setCodePostal($data['codePostal']);
				$devis->setRegion($data['region']);
				$devis->setPays($data['pays']);
				$devis->setDescription($data['description']);
				
				if ($data['date_validite']) {
					$dateValidite = \DateTime::createFromFormat('d/m/Y', $data['date_validite']);
					if ($dateValidite)
						$devis->setDateValidite($dateValidite);
				}
				
				$dateCreation = \DateTime::createFromFormat('d/m/Y', $data['dateCreation']);
				$devis->setDateCreation($dateCreation);
				
				$dateEdition = \DateTime::createFromFormat('d/m/Y', $data['dateEdition']);
				$devis->setDateCreation($dateEdition);
			
				$em->persist($devis);
				$em->flush();
			
				break;
				
			case 'facture':
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
				$facture = $repository->find($data['id']);
					
				$facture->setObjet($data['objet']);
				$facture->setAdresse($data['adresse']);
				$facture->setVille($data['ville']);
				$facture->setCodePostal($data['codePostal']);
				$facture->setRegion($data['region']);
				$facture->setPays($data['pays']);
				$facture->setDescription($data['description']);
			
				if ($data['date_validite']) {
					$dateValidite = \DateTime::createFromFormat('d/m/Y', $data['date_validite']);
					if ($dateValidite)
						$facture->setDateValidite($dateValidite);
				}
				
				$dateCreation = \DateTime::createFromFormat('d/m/Y', $data['dateCreation']);
				$facture->setDateCreation($dateCreation);
				
				$dateEdition = \DateTime::createFromFormat('d/m/Y', $data['dateEdition']);
				$facture->setDateCreation($dateEdition);
					
				$em->persist($facture);
				$em->flush();
					
				break;
				
			case 'opportunite':
				$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
				$opportunite = $repository->find($data['id']);
					
				$opportunite->setNom($data['nom']);
				$opportunite->setMontant($data['montant']);
				
				if ($data['echeance']) {
					$echeance = \DateTime::createFromFormat('d/m/Y', $data['echeance']);
					if ($echeance)
						$opportunite->setEcheance($echeance);
				}
				$opportunite->setType($data['type']);
				//$opportunite->setOrigine($data['origine']);
				//$opportunite->setProbabilite($data['probabilite']);
					
				$dateCreation = \DateTime::createFromFormat('d/m/Y', $data['dateCreation']);
				$opportunite->setDateCreation($dateCreation);
				
				$dateEdition = \DateTime::createFromFormat('d/m/Y', $data['dateEdition']);
				$opportunite->setDateCreation($dateEdition);
					
				
				$em->persist($opportunite);
				$em->flush();
					
				break;
		
		}
		
		$response = new JsonResponse();
		$response->setData('ok');
		return $response;
	}
	
	private function _rapportProcessData($type, $arr_obj){
	
		$arr_processed_data = array();
		$phoneUtil = PhoneNumberUtil::getInstance();
		switch($type){
				
			case 'compte':
				foreach($arr_obj as $compte){
	
// 					$s_telephone = "";
// 					$s_fax = "";
// 						//~ echo $i++ . " zz    ".$compte->getTelephone()." --- > ";
// 					if($compte->getTelephone() && $phoneUtil->isViablePhoneNumber($compte->getTelephone())){
// 						$phoneNumber = $phoneUtil->parse($compte->getTelephone(), 'FR', null, true);
// 						$s_telephone = $phoneUtil->format($phoneNumber , 'INTERNATIONAL');
// 					}
// 					if($compte->getFax() && $phoneUtil->isViablePhoneNumber($compte->getFax())){
// 						$phoneNumber = $phoneUtil->parse($compte->getFax(), 'FR', null, true);
// 						$s_fax = $this->get('libphonenumber.phone_number_util')->format($phoneNumber, 'INTERNATIONAL');
// 					}

					$arr_processed_data[] =
					array(
							'id' => $compte->getId(),
							'nom' => $compte->getNom(),
							'adresse' => $compte->getAdresse(),
							'ville' => $compte->getVille(),
							'codePostal' => $compte->getCodePostal(),
							'region' => $compte->getRegion(),
							'pays' => $compte->getPays(),
							'telephone' => $compte->getTelephone(),
							'fax' => $compte->getFax(),
							'url' => $compte->getUrl(),
							'description' => $compte->getDescription(),
							'gestionnaire' => $compte->getUserGestion()->__toString(),
                            'secteurActivite' => $compte->getSecteurActivite()
					);
				}

				break;
				
			case 'contact':
				foreach($arr_obj as $contact){
			
					$s_telephone_fixe = "";
					$s_telephone_portable = "";
					$s_fax = "";
// 					if($contact->getTelephoneFixe() && $phoneUtil->isViablePhoneNumber($contact->getTelephoneFixe())){
// 						$phoneNumber = $phoneUtil->parse($contact->getTelephoneFixe(), 'FR', null, true);
// 						$s_telephone_fixe = $phoneUtil->format($phoneNumber , 'INTERNATIONAL');
// 					}
// 					if($contact->getTelephonePortable() && $phoneUtil->isViablePhoneNumber($contact->getTelephonePortable())){
// 						$phoneNumber = $phoneUtil->parse($contact->getTelephonePortable(), 'FR', null, true);
// 						$s_telephone_portable = $this->get('libphonenumber.phone_number_util')->format($phoneNumber, 'INTERNATIONAL');
// 					}
// 					if($contact->getFax() && $phoneUtil->isViablePhoneNumber($contact->getFax())){
// 						$phoneNumber = $phoneUtil->parse($contact->getFax(), 'FR', null, true);
// 						$s_fax = $this->get('libphonenumber.phone_number_util')->format($phoneNumber, 'INTERNATIONAL');
// 					}
					//~ if($contact->getTelephoneFixe()){
						//~ $s_telephone_fixe = $this->get('libphonenumber.phone_number_util')->format($contact->getTelephoneFixe(), 'INTERNATIONAL');
					//~ }
					//~ if($contact->getTelephonePortable()){
						//~ $s_telephone_portable = $this->get('libphonenumber.phone_number_util')->format($contact->getTelephonePortable(), 'INTERNATIONAL');
					//~ }
					//~ if($contact->getFax()){
						//~ $s_fax = $this->get('libphonenumber.phone_number_util')->format($contact->getFax(), 'INTERNATIONAL');
					//~ }
			
					$arr_data = array(
							'id' => $contact->getId(),
							'prenom' => $contact->getPrenom(),
							'nom' => $contact->getNom(),
							'compte' => $contact->getCompte()->getNom(),
							'titre' => $contact->getTitre(),
							'adresse' => $contact->getAdresse(),
							'ville' => $contact->getVille(),
							'codePostal' => $contact->getCodePostal(),
							'region' => $contact->getRegion(),
							'pays' => $contact->getPays(),
							'telephoneFixe' => $contact->getTelephoneFixe(),
							'telephonePortable' => $contact->getTelephonePortable(),
							'fax' => $contact->getFax(),
							'email' => $contact->getEmail(),
							'type' => null,
							'reseau' => null,
							'origine' => null,
							'themes_interet' => null,
							'services_interet' => null,
							'carte_voeux' => null,
							'newsletter' => null,
							'description' => $contact->getDescription(),
							'gestionnaire' => $contact->getUserGestion()->__toString(),
					);
					
					if($contact->getReseau()){
						$arr_data['reseau'] = $contact->getReseau()->__toString();
					}

					if($contact->getOrigine()){
						$arr_data['origine'] = $contact->getOrigine()->__toString();
					}
					
					$s_types = '';
					$s_themes = '';
					$s_services = '';
					foreach($contact->getSettings() as $setting){
						if($setting->getParametre() == 'TYPE'){
							$s_types.=$setting->getValeur().'<br />';
						} else if ($setting->getParametre() == 'SERVICE_INTERET'){
							$s_services.=$setting->getValeur().'<br />';
						} else if($setting->getParametre() == 'THEME_INTERET'){
							$s_themes.=$setting->getValeur().'<br />';
						}
					}
					$arr_data['type'] = $s_types;
					$arr_data['themes_interet'] = $s_themes;
					$arr_data['services_interet'] = $s_services;
					
					if($contact->getCarteVoeux()){
						$arr_data['carte_voeux'] = '<span class="glyphicon glyphicon-ok"></span>';
					}else {
						$arr_data['carte_voeux'] = "";
					}
					
					if($contact->getNewsletter()){
						$arr_data['newsletter'] = '<span class="glyphicon glyphicon-ok"></span>';
					} else {
						$arr_data['newsletter'] = "";
					}
						
					$arr_processed_data[] = $arr_data;
			
				}
			
				break;
				
			case 'devis':
				foreach($arr_obj as $devis){
			
					$contact = null;
					if($devis->getContact() != null){
						$contact = $devis->getContact()->__toString();
					}
						
					
					$arr_data =
					array(
							'id' => $devis->getId(),
							'compte' => $devis->getCompte()->__toString(),
							'contact' => $contact,
							'objet' => $devis->getObjet(),
							'num' => $devis->getNum(),
							'date_validite' => $devis->getDateValidite()->format('d/m/Y'),
							'adresse' => $devis->getAdresse(),
							'ville' => $devis->getVille(),
							'codePostal' => $devis->getCodePostal(),
							'region' => $devis->getRegion(),
							'pays' => $devis->getPays(),
							'description' => $devis->getDescription(),
							'gestionnaire' => $devis->getUserGestion()->__toString(),
							'dateCreation' => $devis->getDateCreation()->format('d/m/Y')	
					);
					if($devis->getDateEdition()){
						$arr_data['dateEdition'] = $devis->getDateEdition()->format('d/m/Y');
					}
					
					$arr_data['total_ht'] = $devis->getTotalHT().' €';
					$arr_data['total_ttc'] = $devis->getTotalTTC().' €';
			
					$arr_processed_data[] = $arr_data;
				}
			
				break;
			
			case 'facture':
				foreach($arr_obj as $facture){
						
					$contact = null;
					if($facture->getContact() != null){
						$contact = $facture->getContact()->__toString();
					}
					
					
					$arr_data=
					array(
							'id' => $facture->getId(),
							'compte' => $facture->getCompte()->__toString(),
							'contact' => $contact,
							'objet' => $facture->getObjet(),
							'num' => $facture->getNum(),
							'date_validite' => $facture->getDateValidite()->format('d/m/Y'),
							'adresse' => $facture->getAdresse(),
							'ville' => $facture->getVille(),
							'codePostal' => $facture->getCodePostal(),
							'region' => $facture->getRegion(),
							'pays' => $facture->getPays(),
							'description' => $facture->getDescription(),
							'gestionnaire' => $facture->getUserGestion()->__toString(),
							'bon_commande_interne' => $facture->getNumBCInterne(),
							'bon_commande_client' => $facture->getNumBCClient(),
							'dateCreation' => $facture->getDateCreation()->format('d/m/Y')
					);
					
					if($devis->getDateEdition()){
						$arr_data['dateEdition'] = $facture->getDateEdition()->format('d/m/Y');
					}
						
					$arr_processed_data[] = $arr_data;
				}
					
				break;
				
			case 'opportunite':
				//~ $fff = 0;
				foreach($arr_obj as $opportunite){
					//~ if( $fff++ == 226 ) {break;}
					$contact = null;
					if($opportunite->getContact()){
						$contact = $opportunite->getContact()->__toString();
					}
					$arr_data =
					array(
							'id' => $opportunite->getId(),
							'compte' => $opportunite->getCompte()->__toString(),
							'contact' => $contact,
							'nom' => $opportunite->getNom(),
							'montant' => $opportunite->getMontant(),
							'echeance' => $opportunite->getEcheance()->format('d/m/Y'),
							'statut' => '',
							'probabilite' => $opportunite->getProbabilite()->__toString(),
							'type' => $opportunite->getType(),
							'gestionnaire' => $opportunite->getUserGestion()->__toString(),
							'dateCreation' => $opportunite->getDateCreation()->format('d/m/Y')
					);
					
					if ($opportunite->getOrigine()) {
						$arr_data['origine']= $opportunite->getOrigine()->__toString();
					} else {
						$arr_data['origine'] = null;
					}
					
					if($opportunite->getDateEdition()){
						$arr_data['dateEdition'] = $opportunite->getDateEdition()->format('d/m/Y');
					}
					
					$arr_processed_data[] = $arr_data;
			
				}
		
				break;
					
		}
	
		return $arr_processed_data;
	}
}
