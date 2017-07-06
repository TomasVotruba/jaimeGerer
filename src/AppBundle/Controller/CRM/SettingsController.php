<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\SettingsType;

use AppBundle\Entity\Settings;

class SettingsController extends Controller
{



	/**
	 * @Route("/crm/settings/liste", name="crm_settings_liste")
	 */
	public function settingsListeAction()
	{
		$em = $this->getDoctrine()->getManager();
		$settingsRepo = $em->getRepository('AppBundle:Settings');
		$arr_settings = array();

		//settings existants
		$arr_settings = $settingsRepo->findBy(
			array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'CRM'
			),array(
				'parametre' => 'asc'
			));
		$arr_parametres = array();
		foreach($arr_settings as $settings){
			$arr_parametres[$settings->getParametre()] = $settings;
            $settings->setCompany($this->getUser()->getCompany());
		}

		//settings par defaut
		$arr_settings_default = $settingsRepo->findBy(
			array(
				'company' => null,
				'module' => 'CRM'
			),
			array(
				'parametre' => 'asc'
		));

		foreach($arr_settings_default as $settings_def){
			if(!array_key_exists($settings_def->getParametre(), $arr_parametres)){
				$settings = clone $settings_def;
				$settings->setCompany($this->getUser()->getCompany());
				$em->persist($settings);

				$arr_parametres[$settings->getParametre()] = $settings;
			}
		}
		$em->flush();

		//recherche des différentes valeurs pour les listes
		$arr_valeursSettingsListe = array();
		foreach($arr_parametres as $parametre => $settings){
			if($settings->getType() == "LISTE"){
				$arr_valeurs = $settingsRepo->findBy(array(
						'company' => $this->getUser()->getCompany(),
						'module' => 'CRM',
						'parametre' => $parametre
				));

				$arr_valeursSettingsListe[$settings->getId()] = $arr_valeurs;
			}
		}

		return $this->render('crm/settings/crm_settings_liste.html.twig', array(
				'arr_settings' => $arr_parametres,
                'test' => 'test',
				'arr_valeursSettingsListe' => $arr_valeursSettingsListe
		));
	}



	/**
	 * @Route("/crm/settings/editer/{id}", name="crm_settings_editer")
	 */
	public function settingsEditerAction(Settings $settings)
	{
		$form = $this->createForm(new SettingsType(), $settings);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			if($settings->getCompany() == null){
				$settings->setCompany($this->getUser()->getCompany());
			}
			$em->persist($settings);
			$em->flush();
			return $this->redirect($this->generateUrl('crm_settings_liste'));
		}
		return $this->redirect($this->generateUrl('crm_settings_liste'));
	}

// 	/**
// 	 * @Route("/crm/settings/supprimer/{id}", name="crm_settings_supprimer")
// 	 */
// 	public function settingsSupprimerAction(Settings $settings)
// 	{
// 		$form = $this->createFormBuilder()->getForm();

// 		$request = $this->getRequest();
// 		$form->handleRequest($request);

// 		if ($form->isSubmitted() && $form->isValid()) {

// 			$em = $this->getDoctrine()->getManager();
// 			$em->remove($settings);
// 			$em->flush();

// 			return $this->redirect($this->generateUrl(
// 					'crm_settings_liste'
// 			));
// 		}

// 		return $this->render('crm/settings/crm_settings_supprimer.html.twig', array(
// 				'form' => $form->createView(),
// 				'settings' => $settings
// 		));
// 	}


	/**
	 * @Route("/crm/settings/initialiser", name="crm_settings_initialiser")
	 */
	public function settingsInitialiserAction(){

		$em = $this->getDoctrine()->getManager();
		$settingsRepo = $em->getRepository('AppBundle:Settings');
		$arr_settings = array();

		//settings existants
		$arr_settings = $settingsRepo->findBy(
			array(
				'company' => $this->getUser()->getCompany(),
				'module' => 'CRM'
			),array(
				'parametre' => 'asc'
			));
		$arr_parametres = array();
		foreach($arr_settings as $settings){
			$arr_parametres[$settings->getParametre()] = $settings;
		}

		//settings par defaut
		$arr_settings_default = $settingsRepo->findBy(
			array(
				'company' => null,
				'module' => 'CRM'
			),
			array(
				'parametre' => 'asc'
		));

		foreach($arr_settings_default as $settings_def){
			if(!array_key_exists($settings_def->getParametre(), $arr_parametres)){
				$settings = clone $settings_def;
				$settings->setCompany($this->getUser()->getCompany());
				$em->persist($settings);

				$arr_parametres[$settings->getParametre()] = $settings;
			}
		}
		$em->flush();

		//recherche des différentes valeurs pour les listes
		$arr_valeursSettingsListe = array();
		foreach($arr_parametres as $parametre => $settings){
			if($settings->getType() == "LISTE"){
				$arr_valeurs = $settingsRepo->findBy(array(
						'company' => $this->getUser()->getCompany(),
						'module' => 'CRM',
						'parametre' => $parametre
				));

				$arr_valeursSettingsListe[$settings->getId()] = $arr_valeurs;
			}
		}

		return $this->render('crm/settings/crm_settings_initialiser.html.twig', array(
				'arr_settings' => $arr_parametres,
				'arr_valeursSettingsListe' => $arr_valeursSettingsListe
		));

	}

	/**
	 * @Route("/crm/settings/completer/{id}", name="crm_settings_completer")
	 */
	public function settingsCompleterAction(Settings $settings){
		$response = new Response();

		try{
			$em = $this->getDoctrine()->getManager();

			$requestData = $this->getRequest();
			$val = $requestData->get('value');

			$settings->setValeur($val);

 			$em->persist($settings);
			$em->flush();


		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/crm/settings/completer-cc/{id}", name="crm_settings_completer_cc")
	 */
	public function settingsCompleterCCAction(Settings $settings){
		$response = new Response();

		try{
			$em = $this->getDoctrine()->getManager();

			$requestData = $this->getRequest();
			$val = $requestData->get('value');

			$ccRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\CompteComptable');
			$cc = $ccRepo->find($val);

			$newSettings = clone $settings;
			$newSettings->setCompteComptable($cc);

 			$em->persist($newSettings);
			$em->flush();


		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		$response->setContent($cc->__toString());
		return $response;
	}


	/**
	 * @Route("/crm/settings/liste-ajouter", name="crm_settings_liste_ajouter")
	 */
	public function settingsListeAjouterAction(){

        $em = $this->getDoctrine()->getManager();
        $settingsRepo = $em->getRepository('AppBundle:Settings');

        $response = new Response();

        $requestData = $this->getRequest();
        $val = $requestData->get('value');
        $pk = $requestData->get('pk');

        $settings = $settingsRepo->find($pk);
        $newSettings = clone $settings;
        $newSettings->setValeur($val);


        try{
			$em->persist($newSettings);
			$em->flush();


		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/crm/settings/supprimer/{id}", name="crm_settings_supprimer", options={"expose"=true})
	 */
	public function settingsSupprimerAction(Settings $settings){
		$em = $this->getDoctrine()->getManager();
		$response = new Response();

		try{
			$em->remove($settings);
			$em->flush();

		} catch(\Exception $e){
			$response->setStatusCode(204);
			return $response;
		}

		$response->setStatusCode(200);
		return $response;
	}

	/**
	 * @Route("/crm/settings/images-upload", name="crm_settings_image_upload", options={"expose"=true})
	 */
	public function settingsImageUploadAction(){
		$em = $this->getDoctrine()->getManager();

		$requestData = $this->getRequest();
		$arr_files = $requestData->files->all();
		$file = $arr_files["files"][0];

		//enregistrement du fichier uploadé
		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/crm/pub_facture/';
		$file->move($path, $filename);

		$settingsRepo = $em->getRepository('AppBundle:Settings');
		$settings = $settingsRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'module' => 'CRM',
			'parametre' => 'PUB_FACTURE_IMAGE'
		));

		$settings->setValeur($filename);
		$em->persist($settings);
		$em->flush();

		$response = new JsonResponse();
		$response->setData(array(
				'filename' => $filename
		));

		return $response;
	}
}
