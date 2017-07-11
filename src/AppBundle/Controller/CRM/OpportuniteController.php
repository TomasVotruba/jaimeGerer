<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

use AppBundle\Entity\CRM\Opportunite;
use AppBundle\Entity\CRM\OpportuniteSousTraitance;
use AppBundle\Entity\CRM\OpportuniteRepartition;
use AppBundle\Entity\CRM\Contact;
use AppBundle\Entity\Settings;
use AppBundle\Entity\Rapport;

use AppBundle\Form\CRM\OpportuniteType;
use AppBundle\Form\CRM\OpportuniteRepartitionType;
use AppBundle\Form\CRM\OpportuniteWonRepartitionType;
use AppBundle\Form\CRM\OpportuniteSousTraitanceType;
use AppBundle\Form\CRM\OpportuniteFilterType;
use AppBundle\Form\CRM\ContactType;
use AppBundle\Form\SettingsType;

use \DateTime;

use libphonenumber\PhoneNumberFormat;

class OpportuniteController extends Controller
{
	/**
	 * @Route("/crm/opportunite/liste/{etat}",
	 *   defaults={"etat" = "ONGOING"},
	 *   name="crm_opportunite_liste"
	 * )
	 */
	public function opportuniteListeAction($etat)
	{
		$opportuniteService = $this->get('appbundle.crm_opportunite_service');
		$dataTauxTransformation = $opportuniteService->getTauxTransformationData($this->getUser()->getCompany(), date('Y'));

		$chartService = $this->get('appbundle.chart_service');
		$chartTauxTransformation = $chartService->opportuniteTauxTransformationPieChart($dataTauxTransformation);

		return $this->render('crm/opportunite/crm_opportunite_liste.html.twig', array(
			'etat' => $etat,
			'chartTauxTransformation' => $chartTauxTransformation
		));
	}

	/**
	 * @Route("/crm/opportunite/liste/ajax/{etat}",
	 *   name="crm_opportunite_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteListeAjaxAction($etat)
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository 		= $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
		$arr_search = $requestData->get('search');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				$etat
		);

		for($i=0; $i<count($list); $i++){

			$arr_o = $list[$i];

			$opportunite = $repository->find($arr_o['id']);
			$list[$i]['ca_attendu'] = $opportunite->getCa_attendu();
		}


		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany(), $etat),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(),$arr_search['value'], $etat),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/crm/opportunite/voir/{id}",
	 *   name="crm_opportunite_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteVoirAction(Opportunite $opportunite)
	{
		return $this->render('crm/opportunite/crm_opportunite_voir.html.twig', array(
				'opportunite' => $opportunite,
		));
	}

	/**
	 * @Route("/crm/opportunite/ajouter/{id_to_use}/{type}",
	 *   name="crm_opportunite_ajouter_from_voir_contact_compte",
	 *   defaults={"type" = null, "id_to_use" = null}
	 *  )
	 * @Route("/crm/opportunite/ajouter", name="crm_opportunite_ajouter")
	 */
	public function opportuniteAjouterAction($id_to_use, $type)
	{
		$opportunite = new Opportunite();
		$opportunite->setUserGestion($this->getUser());
		$form = $this->createForm(
				new OpportuniteType(
						$opportunite->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId(),
						$this->getRequest()
				),
				$opportunite
		);

		if( !is_null($id_to_use) && !$form->isSubmitted() )
		{
			$em = $this->getDoctrine()->getManager();
			switch($type){
				case 'contact':
					$dataPrerempli = $em->getRepository('AppBundle:CRM\Contact')->findOneById($id_to_use);
					$form->get('contact_name')->setData($dataPrerempli->getNom());
					$form->get('contact')->setData($dataPrerempli->getId());
					$form->get('compte_name')->setData($dataPrerempli->getCompte()->getNom());
					$form->get('compte')->setData($dataPrerempli->getCompte()->getId());
					break;
				case 'compte':
					$dataPrerempli = $em->getRepository('AppBundle:CRM\Compte')->findOneById($id_to_use);
					$form->get('compte_name')->setData($dataPrerempli->getNom());
					$form->get('compte')->setData($dataPrerempli->getId());
					$dataPrerempli = $em->getRepository('AppBundle:CRM\Contact')->findOneByCompte($id_to_use);
					$form->get('contact_name')->setData($dataPrerempli->getNom());
					$form->get('contact')->setData($dataPrerempli->getId());
					break;
			}
		}
		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$opportunite->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));
			$opportunite->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));

			$opportunite->setDateCreation(new \DateTime(date('Y-m-d')));
			$opportunite->setUserCreation($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($opportunite);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_opportunite_voir',
					array('id' => $opportunite->getId())
			));
		}

		return $this->render('crm/opportunite/crm_opportunite_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/opportunite/editer/{id}",
	 *   name="crm_opportunite_editer",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteEditerAction(Opportunite $opportunite)
	{
		$_compte = $opportunite->getCompte();
		$_contact = $opportunite->getContact();

		$opportunite->setCompte($_compte->getId());
		if($_contact){
			$opportunite->setContact($_contact->getId());
		}

		$form = $this->createForm(
				new OpportuniteType(
						$opportunite->getUserGestion()->getId(),
						$this->getUser()->getCompany()->getId()
				),
				$opportunite
		);

		$form->get('compte_name')->setData($_compte->__toString());
		if($_contact){
			$form->get('contact_name')->setData($_contact->__toString());
		}

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();

			$data = $form->getData();

			$opportunite->setCompte($em->getRepository('AppBundle:CRM\Compte')->findOneById($data->getCompte()));
			$opportunite->setContact($em->getRepository('AppBundle:CRM\Contact')->findOneById($data->getContact()));

			$opportunite->setDateEdition(new \DateTime(date('Y-m-d')));
			$opportunite->setUserEdition($this->getUser());
			$em = $this->getDoctrine()->getManager();
			$em->persist($opportunite);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'crm_opportunite_voir',
					array('id' => $opportunite->getId())
			));
		}

		return $this->render('crm/opportunite/crm_opportunite_editer.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/opportunite/supprimer/{id}",
	 *   name="crm_opportunite_supprimer",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteSupprimerAction(Opportunite $opportunite)
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->remove($opportunite);
			$em->flush();

			return $this->redirect($this->generateUrl(
				'crm_opportunite_liste'
			));
		}

		return $this->render('crm/opportunite/crm_opportunite_supprimer.html.twig', array(
				'form' => $form->createView(),
				'opportunite' => $opportunite
		));
	}

	/**
	 * @Route("/crm/opportunite/gagner/{id}",
	 *   name="crm_opportunite_gagner",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteGagnerAction(Opportunite $opportunite)
	{
		$opportuniteService = $this->get('appbundle.crm_opportunite_service');
		$opportuniteService->win($opportunite);

		$activationOutilsService = $this->get('appbundle.activation_outils');
		$comptaActive = $activationOutilsService->isActive('COMPTA', $this->getUser()->getCompany());

		//compta non activée : renvoyer vers la liste des opportunités
		if(!$comptaActive){
			return $this->redirect($this->generateUrl('crm_opportunite_liste'));
		}

		//compta activée : renvoyer vers la répartition des montants pour le tableau de bord
		return $this->redirect($this->generateUrl('crm_opportunite_gagner_repartition', array(
			'id' => $opportunite->getId()
		)));
	}


	/**
	 * @Route("/crm/opportunite/gagner/repartition/{id}/{edition}",
	 *   name="crm_opportunite_gagner_repartition",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteGagnerRepartitionAction(Opportunite $opportunite, $edition = false)
	{
		$form = $this->createForm(
				new OpportuniteWonRepartitionType($edition),
				$opportunite
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($opportunite);
			$em->flush();

			if($form['sousTraitance']->getData() === true){
					  return $this->redirect($this->generateUrl('crm_opportunite_gagner_sous_traitance', array(
							'id' => $opportunite->getId()
						)));
			}

			return $this->redirect($this->generateUrl('crm_opportunite_voir', array(
				'id' => $opportunite->getId()
			)));

		}

		return $this->render('crm/opportunite/crm_opportunite_won_repartition.html.twig', array(
				'opportunite' => $opportunite,
				'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/crm/opportunite/gagner/sous-traitance/{id}",
	 *   name="crm_opportunite_gagner_sous_traitance",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteGagnerSousTraitanceAction(Opportunite $opportunite)
	{

		$opportuniteSousTraitance = new OpportuniteSousTraitance();
		$opportuniteSousTraitance->setOpportunite($opportunite);

		$form = $this->createForm(
				new OpportuniteSousTraitanceType(),
				$opportuniteSousTraitance
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$opportunite->addOpportuniteSousTraitance($opportuniteSousTraitance);

			$em = $this->getDoctrine()->getManager();
			$em->persist($opportunite);
			$em->flush();


			if ($form->has('add') && $form->get('add')->isClicked()){
				// reload
				return $this->redirect($this->generateUrl('crm_opportunite_gagner_sous_traitance', array(
					'id' => $opportunite->getId()
				)));

			}

			return $this->redirect($this->generateUrl('crm_opportunite_voir', array(
				'id' => $opportuniteSousTraitance->getOpportunite()->getId()
			)));

		}

		return $this->render('crm/opportunite/crm_opportunite_won_sous_traitance.html.twig', array(
				'opportunite' => $opportunite,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/opportunite/perdre/{id}",
	 *   name="crm_opportunite_perdre",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportunitePerdreAction(Opportunite $opportunite)
	{
		$opportuniteService = $this->get('appbundle.crm_opportunite_service');
		$opportuniteService->lose($opportunite);

		return $this->redirect($this->generateUrl('crm_opportunite_liste'));
	}

	/**
	 * @Route("/crm/opportunite-repartition/editer/{id}",
	 *   name="crm_opportunite-repartition_editer"
	 * )
	 */
	public function opportuniteRepartitionEditerAction(OpportuniteRepartition $opportuniteRepartition)
	{
		$form = $this->createForm(
				new OpportuniteRepartitionType(),
				$opportuniteRepartition
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($opportuniteRepartition);
			$em->flush();


			return $this->redirect($this->generateUrl('crm_opportunite_voir', array(
				'id' => $opportuniteRepartition->getOpportunite()->getId()
			)));

		}

		return $this->render('crm/opportunite/crm_opportunite_repartition_editer.html.twig', array(
				'opportuniteRepartition' => $opportuniteRepartition,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/opportunite-sous-traitance/editer/{id}",
	 *   name="crm_opportunite-sous-traitance_editer"
	 * )
	 */
	public function opportuniteSousTraitanceEditerAction(OpportuniteSousTraitance $sousTraitance)
	{
		$form = $this->createForm(
				new OpportuniteSousTraitanceType(),
				$sousTraitance
		);

		$form->remove('submit')
					->remove('add');

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$em = $this->getDoctrine()->getManager();
			$em->persist($sousTraitance);
			$em->flush();

			return $this->redirect($this->generateUrl('crm_opportunite_voir', array(
				'id' => $sousTraitance->getOpportunite()->getId()
			)));

		}

		return $this->render('crm/opportunite/crm_opportunite_sous_traitance_editer.html.twig', array(
				'opportuniteSousTraitance' => $sousTraitance,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/crm/opportunite/nettoyer", name="crm_opportunite_nettoyer")
	 */
	public function opportuniteNettoyerAction()
	{
		$form = $this->createFormBuilder()->getForm();

		$request = $this->getRequest();
		$echeance = $request->request->get('nettoyer_echeance');

		$date = DateTime::createFromFormat('d/m/Y', $echeance);

		if ($date) {
			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');

			$arr_nettoyer = $repository->nettoyer($this->getUser()->getCompany(), $date->format('Y-m-d'));
			$em = $this->getDoctrine()->getManager();
			foreach($arr_nettoyer as $opportunite){
				$em->remove($opportunite);
			}
			$em->flush();

			$response = new Response('ok');
		} else {
			$response = new Response('ko',500);
		}

		return $response;
	}

	/**
	 * @Route("/crm/opportunite/rapport/liste", name="crm_opportunite_rapport_liste")
	 */
	public function opportuniteRapportListeAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Rapport');

		$list = $repository->findByUserCreation($this->getUser());

		return $this->render('crm/opportunite/crm_opportunite_rapport_liste.html.twig', array(
				'list' => $list
		));
	}


	/**
	 * @Route("/crm/opportunite/rapport/ajouter", name="crm_opportunite_rapport_ajouter")
	 */
	public function opportuniteRapportAjouterAction()
	{
		$form = $this->createFormBuilder()
		->add('filters', 'collection', array(
				'type' => new OpportuniteFilterType(),
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
				'mapped' => false

		))
		->getForm();

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
			$data = $form->get('filters')->getData();

			$arr_opportunites = $repository->createQueryAndGetResult($data);
			$arr_return = array();

			foreach($arr_opportunites as $opportunite){

				$s_telephone = "";
				$s_fax = "";
				if($opportunite->getTelephone()){
					$s_telephone = $this->get('libphonenumber.phone_number_util')->format($opportunite->getTelephone(), 'INTERNATIONAL');
				}
				if($opportunite->getFax()){
					$s_fax = $this->get('libphonenumber.phone_number_util')->format($opportunite->getFax(), 'INTERNATIONAL');
				}

				$arr_return[] =
				array(
						'id' => $opportunite->getId(),
						'nom' => $opportunite->getNom(),
						'ville' => $opportunite->getVille(),
						'codePostal' => $opportunite->getCodePostal(),
						'region' => $opportunite->getRegion(),
						'pays' => $opportunite->getPays(),
						'telephone' => $s_telephone,
						'fax' => $s_fax,
						'url' => $opportunite->getUrl(),
						'description' => $opportunite->getDescription(),
						'gestionnaire' => $opportunite->getUserGestion()->__toString(),
				);

			}

			$arr_headers = array('Nom', 'Ville', 'Code postal', 'Region', 'Pays', 'Telephone', 'Fax', 'Site web', 'Description', 'Gestionnaire du opportunite');

			return $this->render('crm/opportunite/crm_opportunite_rapport_voir.html.twig', array(
					'arr_opportunites' => $arr_return,
					'arr_headers' => $arr_headers
			));

		}

		return $this->render('crm/opportunite/crm_opportunite_rapport_ajouter.html.twig', array(
				'form' => $form->createView()
		));

	}


	/**
	 * @Route("/crm/opportunite/rapport/voir/{id}", name="crm_opportunite_rapport_voir")
	 */
	public function opportuniteRapportVoirAction(Rapport $rapport)
	{
		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);

 		$arr_data = json_decode($rapport->getData(), true);
		$arr_return = array();

		$arr_opportunites = array();
		foreach($arr_data as $data){

			$opportunite = $serializer->deserialize(json_encode($data), 'AppBundle\Entity\CRM\Opportunite', 'json');
			//TO BE CONTINUED
		}

		$arr_headers = array('Nom', 'Ville', 'Code postal', 'Region', 'Pays', 'Telephone', 'Fax', 'Site web', 'Description', 'Gestionnaire du opportunite');

		return $this->render('crm/opportunite/crm_opportunite_rapport_voir.html.twig', array(
				'arr_opportunites' => $arr_return,
				'arr_headers' => $arr_headers
		));

	}
	/**
	 * @Route("/crm/opportunite/rapport/enregistrer", name="crm_opportunite_rapport_enregistrer")
	 */
	public function opportuniteRapportEnregistrerAction()
	{
		$request = $this->getRequest();

		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);
		$data = $request->request->get('data');
		$data_json = $serializer->serialize($data, 'json');

		$rapport = new Rapport();
		$rapport->setModule('CRM');
		$rapport->setType('Opportunite');
		$rapport->setDateCreation(new \DateTime(date('Y-m-d')));
		$rapport->setUserCreation($this->getUser());
		$rapport->setData($data_json);
		$rapport->setNom('test');
		$em = $this->getDoctrine()->getManager();
		$em->persist($rapport);
		$em->flush();
	}

	/**
	 * @Route("/crm/opportunite/chart/data/{type}",
	 *  name="crm_opportunite_chart_data",
	 *  options={"expose"=true}
	 * )
	 */
	public function opportuniteFunnelChartAction($type)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$arr_keys = $settingsRepository->findValeurs('CRM','OPPORTUNITE_STATUT', $this->getUser()->getCompany());

		$data = array();

    if($type === 'funnel'){
        foreach ($arr_keys as $key) {
            if($key->getValeur() != "ClosedWon - 100%"){
                $data[$key->getValeur()] = array("title"=>$key->getValeur(), 'value'=>0);
            }
        }
    }

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Opportunite');
		$arr_res = $repository->getChartData($this->getUser()->getCompany());
		foreach ($arr_res as $result) {
			$_statut = $result['probabilite'];
			$data[$_statut]['value'] = $result['total'];
		}

		$encoders = array(new JsonEncoder());
		$normalizers = array(new GetSetMethodNormalizer());
		$serializer = new Serializer($normalizers, $encoders);

		$data_json = $serializer->serialize(array_values($data), 'json');

		$response = new JsonResponse();
		$response->setContent($data_json);

		return $response;

	}



	/**
	 * @Route("/crm/opportunite/dupliquer/{id}",
	 *   name="crm_opportunite_dupliquer",
	 *   options={"expose"=true}
	 * )
	 */
	public function opportuniteDupliquerAction(Opportunite $opportunite)
	{
		$em = $this->getDoctrine()->getManager();
		$newOpportunite = clone $opportunite;
		$newOpportunite->setUserCreation($this->getUser());
		$newOpportunite->setNom('COPIE '.$opportunite->getNom());

		$em->persist($newOpportunite);
		$em->flush();

		return $this->redirect($this->generateUrl(
				'crm_opportunite_voir',
				array('id' => $newOpportunite->getId())
		));
	}

}
