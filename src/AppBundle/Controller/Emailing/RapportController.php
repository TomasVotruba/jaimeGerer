<?php

namespace AppBundle\Controller\Emailing;

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
use AppBundle\Form\Emailing\RapportListeContactType;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class RapportController extends Controller
{	
	
	/**
	 * @Route("/emailing/rapport/liste/{type}/{user}", name="emailing_rapport_liste")
	 */
	public function rapportListeAction($type, $user=null)
	{//echo "hich"; exit;
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Rapport');
	
		$criteria = array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'Emailing',
				'type' => $type
		);
		
		if($user == 'mine'){
			$criteria['userCreation'] = $this->getUser();
		}
		
		$list = $repository->findBy($criteria);
		
		return $this->render('emailing/rapport/emailing_rapport_liste.html.twig', array(
				'list' => $list,
				'type' => $type,
				'user' => $user
		));
	}
	
	/**
	 * @Route("/emailing/rapport/ajouter/{type}", name="emailing_rapport_ajouter")
	 */
	public function rapportAjouterAction($type)
	{
		$rapport = new Rapport();
		$form = $this->createForm(new RapportListeContactType(), $rapport)
			->add('filters', 'collection', array(
					'type' => new RapportFilterType($type),
					'allow_add' => true,
					'allow_delete' => true,
					'by_reference' => false,
					'label_attr' => array('class' => 'hidden'),
					'mapped' => false
			));
		//~ $form->get('nom')->setLabel('zzzz');
		//~ $form->get('nom')->setConfig()->setOption('label', 'zzz'); 
		//~ echo $form->get('nom')->getConfig()->getOption('label'); exit;
		//~ $form->remove('nom');
		//~ $form->add('nom', 'text', array(
						//~ 'label' => 'Nom de la liste',
						//~ 'required' => true,
						//~ 'attr' => array(
							//~ 'class' => 'input-xxl'
						//~ )
					//~ ));
//~ 
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if($form->isSubmitted()) {
			$rapport->setCompany($this->getUser()->getCompany());
			$rapport->setModule('Emailing');
			$rapport->setType($type);
			$rapport->setDateCreation(new \DateTime(date('Y-m-d')));
			$rapport->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($rapport);
			$em->flush();
			
			$arr_filters = $form->get('filters')->getData();
			
			foreach($arr_filters as $filter){
				$filter->setRapport($rapport);
				$em->persist($filter);
				$em->flush();
			}
			

			return $this->redirect($this->generateUrl(
					'emailing_rapport_voir',
					array('id' => $rapport->getId())
			));
	
		}
	
		return $this->render('emailing/rapport/emailing_rapport_ajouter.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true,
		));
	
	}
	
	
	private function getHTMLRender($col) {
		$arr_readOnly_cols = array(
				'gestionnaire', 'type', 'origine', 'reseau', 'carte_voeux', 'services_interet', 'themes_interet', 'num', 'compte', 'contact'
		);
		if (in_array($col, $arr_readOnly_cols)) {
			return array('data' => $col, 'readOnly' => true, 'renderer' => 'html');
		} else {
			return array('data' => $col, 'renderer' => 'html');
		}
	}
	
	
	/**
	 * @Route("/emailing/rapport/voir/{id}", name="emailing_rapport_voir")
	 */
	public function rapportVoirAction(Rapport $rapport)
	{
		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		
		$arr_data = array();
		$arr_data = json_decode($rapport->getData(), true);
		
		$arr_headers = array();
		$arr_columns = array();
		
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$arr_filters = $filterRepo->findByRapport($rapport);

		switch(strtolower($rapport->getType())){
			case 'contact':
				$objRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Contact');
				$arr_headers_keys = array(
						'prenom' => 'Prénom',
						'nom' =>	'Nom',
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
						'gestionnaire' =>	'Gestionnaire du contact'
				);
				break;
		}

		$arr_obj = $objRepo->createQueryAndGetResult($arr_filters, $this->getUser()->getCompany());

 //~ var_dump($arr_obj); exit;
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
				$arr_columns[] = $this->getHTMLRender($col);
			}
 		}
 		
 		//~ \Doctrine\Common\Util\Debug::dump($arr_data);
 		
		return $this->render('emailing/rapport/emailing_rapport_voir.html.twig', array(
				'arr_obj' => $arr_data,
				'arr_headers' => $arr_headers,
				'arr_columns' => $arr_columns,
				'rapport' => $rapport,
				'hide_tiny' => true
		));
	}
	
	
	/**
	 * @Route("/emailing/rapport/editer/{id}", name="emailing_rapport_editer")
	 */
	public function rapportEditerAction(Rapport $rapport)
	{
		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
	
		$arr_data = array();
		$arr_data = json_decode($rapport->getData(), true);
	
		$arr_headers = array();
		$arr_columns = array();
	
		$filterRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\RapportFilter');
		$arr_filters = $filterRepo->findByRapport($rapport);
		//~ var_dump($arr_filters); exit;
		//\Doctrine\Common\Util\Debug::dump($arr_filters);
		
		$type = strtolower($rapport->getType());
	
		//$rapport = new Rapport();
		$form = $this->createForm(new RapportListeContactType(), $rapport);
		//~ $form->remove('nom');
		//~ $form->add('nom', 'text', array(
						//~ 'label' => 'Nom de la liste',
						//~ 'required' => true,
						//~ 'attr' => array(
							//~ 'class' => 'input-xxl'
						//~ )
					//~ ));
		$form->add('filters', 'collection', array(
				'type' => new RapportFilterType($type),
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
				'mapped' => false
		));
		
		$form->get('filters')->setData($arr_filters);
		
		//\Doctrine\Common\Util\Debug::dump($form);
	
		$request = $this->getRequest();
		$form->handleRequest($request);
	
		if ($form->isSubmitted()) {
			
			$rapport->setModule('Emailing');
			$rapport->setType($type);
			$rapport->setDateCreation(new \DateTime(date('Y-m-d')));
			$rapport->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($rapport);
			$em->flush();
			
			$arr_filters = $form->get('filters')->getData();
			
			foreach($arr_filters as $filter){
				$filter->setRapport($rapport);
				$em->persist($filter);
				$em->flush();
			}
			

			return $this->redirect($this->generateUrl(
					'emailing_rapport_editer',
					array('id' => $rapport->getId())
			));
	
		}
	
		return $this->render('emailing/rapport/emailing_rapport_editer.html.twig', array(
				'form' => $form->createView(),
				'hide_tiny' => true,
		));
	}
	
	
	/**
	 * @Route("/emailing/rapport/supprimer/{id}", name="emailing_rapport_supprimer")
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
					'emailing_rapport_liste', array('type' => $type)
			));
		}
	
		return $this->render('emailing/rapport/emailing_rapport_supprimer.html.twig', array(
				'form' => $form->createView(),
				'rapport' => $rapport
		));
	}
	
	/**
	 * @Route("/emailing/rapport/enregistrer", name="emailing_rapport_enregistrer")
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
	 * @Route("/emailing/rapport/row_maj", name="emailing_rapport_row_maj")
	 */
	public function rapportRowMajAction()
	{
		//~ var_dump($_POST); exit;
		$request = $this->getRequest();
		
		$data = $request->request->get('data');
		$type = $request->request->get('type');
		
		$em = $this->getDoctrine()->getManager();
		
		switch($type){
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
		
		}
		
		$response = new JsonResponse();
		$response->setData('ok');
		return $response;
	}
	
	private function _rapportProcessData($type, $arr_obj){
	
		$arr_processed_data = array();
		$phoneUtil = PhoneNumberUtil::getInstance();
		switch($type){
				
			case 'contact':
				foreach($arr_obj as $contact){
			
					$s_telephone_fixe = "";
					$s_telephone_portable = "";
					$s_fax = "";
			
					$arr_data = array(
							'id' => $contact->getId(),
							'prenom' => $contact->getPrenom(),
							'nom' => $contact->getNom(),
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
					}
					
					if($contact->getNewsletter()){
						$arr_data['newsletter'] = '<span class="glyphicon glyphicon-ok"></span>';
					}
						
					
					$arr_processed_data[] = $arr_data;
			
				}
			
				break;
				
		}
	
		return $arr_processed_data;
	}
}
