<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Compta\Relance;
use AppBundle\Entity\CRM\DocumentPrix;
use AppBundle\Entity\CRM\Produit;
use AppBundle\Entity\CRM\Compte;

use AppBundle\Form\Compta\UploadHistoriqueFactureType;
use AppBundle\Form\Compta\UploadHistoriqueFactureMappingType;

use Swift_Attachment;

class FactureController extends Controller
{

	/**
	 * @Route("/compta/facture/analytique", name="compta_facture_analytique")
	 */
	public function factureAnalytique(){
		$em = $this->getDoctrine()->getManager();
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$analytiqueRepo =  $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

		$arr_factures= $repository->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);

		foreach($arr_factures as $facture){

			if($facture->getAnalytique() == null){
				$produit = $facture->getProduits()[0];
				$type = $produit->getType()->getValeur();
				echo $facture->getNum().'<br />';
				echo $type.'<br />';
				$analytique = $analytiqueRepo->findOneBy(array(
					'module' => 'CRM',
					'parametre' => 'ANALYTIQUE',
					'company' => $this->getUser()->getCompany(),
					'valeur' => $type
				));

				$facture->setAnalytique($analytique);
				$em->persist($facture);

			}
		}
		$em->flush();
		return 0;
	}

	/**
	 * @Route("/compta/facture/liste", name="compta_facture_liste")
	 */
	public function factureListeAction()
	{
		return $this->render('compta/facture/compta_facture_liste.html.twig');
	}

	/**
	 * @Route("/compta/facture/liste/retard", name="compta_facture_liste_retard")
	 */
	public function factureListeRetardAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$arr_factures = $repository->findFacturesRetardByCompany(
				$this->getUser()->getCompany()
		);

		$arr_totaux = array(
				'ht' => 0,
				'ttc' => 0
		);

		foreach($arr_factures as $facture){
			$arr_totaux['ht']+= $facture->getTotalHT();
			$arr_totaux['ttc']+= $facture->getTotalTTC();
		}

		return $this->render('compta/facture/compta_facture_liste_retard.html.twig', array(
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/facture/liste/echeance", name="compta_facture_liste_echeance")
	 */
	public function factureListeEcheanceAction()
	{
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$arr_factures = $repository->findFacturesEcheance($this->getUser()->getCompany());

		$arr_totaux = array(
				'ht' => 0,
				'ttc' => 0
		);

		foreach($arr_factures as $facture){
			$arr_totaux['ht']+= $facture->getTotalHT();
			$arr_totaux['ttc']+= $facture->getTotalTTC();
		}

		return $this->render('compta/facture/compta_facture_liste_echeance.html.twig', array(
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/facture/liste/ajax",
	 * 	name="compta_facture_liste_ajax",
	 * 	options={"expose"=true}
	 * )
	 */
	public function factureListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$arr_search = $requestData->get('search');
		$arr_date = $requestData->get('dateRange');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				'FACTURE',
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value'],
				null,
				null,
				true,
				$arr_date
		);

		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$facture = $repository->find($arr_f['id']);
			$totaux = $facture->getTotaux();
			$list[$i]['totaux'] = $totaux;

			$list[$i]['avoir'] = null;
			foreach($facture->getAvoirs() as $avoir){
				$list[$i]['avoir'].=$avoir->getNum().' ';
			}

		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany(), 'FACTURE', null, null, true),
				'recordsFiltered' => $repository->countForList($this->getUser()->getCompany(), 'FACTURE', $arr_search['value'], null, null, true, $arr_date),
				'aaData' => $list,
				'total' => 40
		));

		return $response;
	}


	/**
	 * Calculate the total (HT and TTC) all invoices in a date range
	 * passed as POST parameter
	 * @return Response 	Rendered view
	 *
	 * @Route("/compta/facture/total/ajax",
	 * 	name="compta_facture_total_ajax",
	 * 	options={"expose"=true}
	 * )
	 */
	public function factureTotalAjaxAction(){

		$arr_date = $this->getRequest()->get('dateRange');

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$arr_factures = $repository->findForCompany(
				$this->getUser()->getCompany(),
				'FACTURE',
				true,
				$arr_date
		);

		$arr_totaux = array(
			'ht' => 0,
			'ttc' => 0
		);

		foreach($arr_factures as $facture){
			$arr_totaux['ht']+= $facture->getTotalHTMoinsAvoirs();
			$arr_totaux['ttc']+= $facture->getTotalTTCMoinsAvoirs();
		}

		return $this->render('compta/facture/compta_facture_liste_totaux.html.twig', array(
			'arr_totaux' => $arr_totaux
		));
	}

	/**
	 * @Route("/compta/facture/liste/retard/ajax",
	 *   name="compta_facture_liste_retard_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function factureListeRetardAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$arr_search = $requestData->get('search');
		$orderBy = $arr_cols[$col]['data'];

		$list = $repository->findForListRetard(
				$this->getUser()->getCompany(),
				'FACTURE',
				$requestData->get('length'),
				$requestData->get('start'),
				$orderBy,
				$arr_sort[0]['dir'],
				$arr_search['value'],
				true
		);

		$chequesRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');
		$arr_remise_cheques = $chequesRepository->findForCompany($this->getUser()->getCompany());

		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$facture = $repository->find($arr_f['id']);
			$totaux = $facture->getTotaux();
			$list[$i]['totaux'] = $totaux;

			$list[$i]['contact'] = $facture->getContact();

			$list[$i]['cheque'] = null;
			foreach($arr_remise_cheques as $remiseCheque){
				foreach($remiseCheque->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()->getId() == $arr_f['id']){
							$list[$i]['cheque'] = $remiseCheque->getNum();
						}
					}
				}
			}

			$list[$i]['relance'] = "";
			foreach($facture->getRelancesRetard() as $relance){
				$list[$i]['relance'].=$relance->__toString().'<br />';
			}
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany(), 'FACTURE', null, null, true),
				'recordsFiltered' => $repository->countForListRetard($this->getUser()->getCompany(), 'FACTURE', $arr_search['value'], true),
				'aaData' => $list,
		));

		return $response;
	}

	/**
	 * @Route("/compta/facture/liste/echeance/ajax",
	 *   name="compta_facture_liste_echeance_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function factureListeEcheanceAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$arr_search = $requestData->get('search');
		$orderBy = $arr_cols[$col]['data'];

		$list = $repository->findForListEcheance(
				$this->getUser()->getCompany(),
				'FACTURE',
				$requestData->get('length'),
				$requestData->get('start'),
				$orderBy,
				$arr_sort[0]['dir'],
				$arr_search['value'],
				true
		);

		$chequesRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');
		$arr_remise_cheques = $chequesRepository->findForCompany($this->getUser()->getCompany());

		for($i=0; $i<count($list); $i++){

			$arr_f = $list[$i];

			$facture = $repository->find($arr_f['id']);
			$totaux = $facture->getTotaux();
			$list[$i]['totaux'] = $totaux;

			$list[$i]['cheque'] = null;
			foreach($arr_remise_cheques as $remiseCheque){
				foreach($remiseCheque->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()->getId() == $arr_f['id']){
							$list[$i]['cheque'] = $remiseCheque->getNum();
						}
					}
				}
			}

			$list[$i]['relance'] = "";
			foreach($facture->getRelancesEcheance() as $relance){
				$list[$i]['relance'].=$relance->__toString().'<br />';
			}
		}

		$response = new JsonResponse();
		$response->setData(array(
				'draw' => intval( $requestData->get('draw') ),
				'recordsTotal' => $repository->count($this->getUser()->getCompany(), 'FACTURE', null, null, true),
				'recordsFiltered' => $repository->countForListEcheance($this->getUser()->getCompany(), 'FACTURE', $arr_search['value'], true),
				'aaData' => $list,
		));

		return $response;
	}


	/**
	 * Display an invoice
	 * @param  DocumentPrix $facture Invoice to display
	 * @return Response              Rendered view
	 *
	 * @Route("/compta/facture/voir/{id}",
	 * 	name="compta_facture_voir",
	 * 	options={"expose"=true}
	 * )
	 */
	public function factureVoirAction(DocumentPrix $facture)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$footerfacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE'));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE'));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE'));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF'));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB'));

		return $this->render('compta/facture/compta_facture_modal.html.twig', array(
				'facture' => $facture,
				'footer' => $footerfacture,
				'pub' => $arr_pub,
				'contact_admin' => $contactAdmin->getValeur(),
				'RIB' => $rib
		));
	}


	/**
	 * @Route("/compta/facture/relancer/email/{id}", name="compta_facture_relancer_email", options={"expose"=true})
	 */
	public function factureRelancerEmailAction($id)
	{
		$factureRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');
		$facture = $factureRepo->find($id);

		//formulaire de redaction du mail
		$form = $this->createFormBuilder()->getForm();
		$form->add('objet', 'text', array(
			'required' => true,
			'label' => 'Objet',
			'data' => 'Relance : facture '.$facture->getObjet()
		));

		$arr_factures = $factureRepo->findFacturesRetard($facture->getCompte());

		//calcul du total à régler
		$total = 0;
		foreach($arr_factures as $f){
			$total+=$f->getTotalTTC();
		}

		$relanceRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Relance');
		$arr_relances = $relanceRepo->findBy(array(
			'facture' => $facture,
			'objet' => 'RETARD'
		));
		if (count($arr_relances) == 0) {
			$numRelance = 1;
			$message = $this->renderView('compta/relance/compta_relance1_email.html.twig', array(
				'arr_factures' => $arr_factures,
				'total_du' => $total
			));
		} else if (count($arr_relances)) {
			$numRelance = 2;
			$message = $this->renderView('compta/relance/compta_relance2_email.html.twig', array(
				'arr_factures' => $arr_factures,
				'total_du' => $total
			));
		}

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $message,
				'attr' => array('class' => 'tinymce')
		));
		$form->add('addcc', 'checkbox', array(
				'required' => false,
				'label' => 'Recevoir une copie de l\'email'
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
				->setTo($facture->getContact()->getEmail())
				->setBody($message, 'text/html');

				$arr_filenames = array();
				foreach($arr_factures as $facture){
					$filename = $this->_factureCreatePDF($facture);
					$mail->attach(Swift_Attachment::fromPath($filename));
					$arr_filenames[] = $filename;
				}

				if( $form->get('addcc')->getData() ) $mail->addCc($this->getUser()->getEmail());
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'La facture a bien été envoyée.'
				);

				foreach($arr_filenames as $filename){
					unlink($filename);
				}

				$relance = new Relance();
				$relance->setType('EMAIL');
				$relance->setDate(new \DateTime(date('Y-m-d')));
				$relance->setFacture($facture);
				$relance->setContact($facture->getContact());
				$relance->setUser($this->getUser());
				$relance->setMessage($message);
				$relance->setNum(count($arr_relances)+1);
				$relance->setObjet('RETARD');

				$em = $this->getDoctrine()->getManager();
				$em->persist($relance);
				$em->flush();


			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}


			return $this->redirect($this->generateUrl(
					'compta_facture_liste_retard'
			));
		}

		return $this->render('compta/relance/compta_relance_email_modal.html.twig', array(
				'type' => 'RETARD',
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/compta/facture/rappeler-echeance/{id}",
	 *   name="compta_facture_rappeler-echeance",
	 *   options={"expose"=true}
	 * )
	 */
	public function factureRappelerEcheanceEmailAction(DocumentPrix $facture)
	{
		//formulaire de redaction du mail
		$form = $this->createFormBuilder()->getForm();
		$form->add('objet', 'text', array(
				'required' => true,
				'label' => 'Objet',
				'data' => 'La facture n°'.$facture->getNum().' arrive bientôt à échéance.'
		));


		$message = $this->renderView('compta/relance/compta_echeance_email.html.twig', array(
				'facture' => $facture,
		));

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $message,
				'attr' => array('class' => 'tinymce')
		));
		$form->add('addcc', 'checkbox', array(
				'required' => false,
				'label' => 'Recevoir une copie de l\'email'
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

			$filename = $this->_factureCreatePDF($facture);

			try{
				$mail = \Swift_Message::newInstance()
				->setSubject($objet)
				->setFrom($this->getUser()->getEmail())
				->setTo($facture->getContact()->getEmail())
				->setBody($message, 'text/html');

				$filename = $this->_factureCreatePDF($facture);
				$mail->attach(Swift_Attachment::fromPath($filename));

				if( $form->get('addcc')->getData() ){
					$mail->addCc($this->getUser()->getEmail());
				}
				$this->get('mailer')->send($mail);
				$this->get('session')->getFlashBag()->add(
						'success',
						'Le message a bien été envoyé.'
				);
				unlink($filename);

				$relance = new Relance();
				$relance->setType('EMAIL');
				$relance->setDate(new \DateTime(date('Y-m-d')));
				$relance->setFacture($facture);
				$relance->setContact($facture->getContact());
				$relance->setUser($this->getUser());
				$relance->setMessage($message);
				$relance->setNum(0);
				$relance->setObjet('ECHEANCE');

				$em = $this->getDoctrine()->getManager();
				$em->persist($relance);
				$em->flush();

			} catch(\Exception $e){
				$error =  $e->getMessage();
				$this->get('session')->getFlashBag()->add('danger', "L'email n'a pas été envoyé pour la raison suivante : $error");
			}


			return $this->redirect($this->generateUrl(
					'compta_facture_liste_echeance'
			));
		}

		return $this->render('compta/relance/compta_relance_email_modal.html.twig', array(
				'type' => 'ECHEANCE',
				'form' => $form->createView(),
				'facture' => $facture
		));
	}

	/**
	 * @Route("/compta/facture/relancer/courrier/{id}",
	 *   name="compta_facture_relancer_courrier",
	 *   options={"expose"=true}
	 * )
	 */
	public function factureRelancerCourrierAction($id)
	{
		$factureRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\DocumentPrix');

		$facture = $factureRepo->find($id);

		//formulaire de redaction du mail
		$form = $this->createFormBuilder()->getForm();

		$message = "";
		$today = new \DateTime();

		$arr_factures = $factureRepo->findFacturesRetard($facture->getCompte());

		//calcul du total à régler
		$total = 0;
		foreach($arr_factures as $f){
			$total+=$f->getTotalTTC();
		}

		$relanceRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\Relance');
		$arr_relances = $relanceRepo->findBy(array(
			'facture' => $facture,
			'objet' => 'RETARD'
		));
		if (count($arr_relances) == 0) {
			$numRelance = 1;
			$message = $this->renderView('compta/relance/compta_relance1_courrier.html.twig', array(
				'arr_factures' => $arr_factures,
				'total_du' => $total
			));
		} else if (count($arr_relances)) {
			$numRelance = 2;
			$message = $this->renderView('compta/relance/compta_relance2_courrier.html.twig', array(
				'arr_factures' => $arr_factures,
				'total_du' => $total
			));
		}

		$form->add('message', 'textarea', array(
				'required' => true,
				'label' => 'Message',
				'data' => $message,
				'attr' => array('class' => 'tinymce')
		));

		$disabled = false;
		if($this->getUser()->getCompany()->getCredits() < 6.8){
			$disabled = true;
		}
		$form->add('recommande', 'checkbox', array(
				'required' => false,
				'disabled' => $disabled,
				'label' =>  'Envoyer en recommandé avec accusé de réception'
		));
		$form->add('submit', 'submit', array(
				'label' => 'Envoyer',
				'attr' => array('class' => 'btn btn-success')
		));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$message = $form->get('message')->getData();
			$recommande = $form->get('recommande')->getData();

			$this->_relanceCreatePDF($facture, $message, $numRelance);

			/*ENVOI COURRIER VIA MAILEVA*/
			$path = $this->container->getParameter('kernel.root_dir').'/../web/files/compta/'.$this->getUser()->getCompany()->getId().'/relance/';
			$nomClient = strtolower(str_ireplace(' ','', $facture->getCompte()->getNom()));
			$accents = array('á','à','â','ä','ã','å','ç','é','è','ê','ë','í','ì','î','ï','ñ','ó','ò','ô','ö','õ','ú','ù','û','ü','ý','ÿ');
			$sans_accents = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y');
			$nomClient=str_ireplace($accents, $sans_accents, $nomClient);
			$fileName = 'relance'.$numRelance.$facture->getNum();
			$fileName = str_replace('-', '', $fileName);

			$mailEvaService = $this->container->get('appbundle.maileva_service');
			try{
				$mailEvaService->pieceJointeDeService($path, $fileName, $facture, $numRelance, $recommande);
				$mailEvaService->fichierDeCommande($path, $fileName);
				$mailEvaService->depotFTP($path, $fileName);
			} catch(\Exception $e){
				$this->get('session')->getFlashBag()->add('danger', "Le courrier n'a pas été envoyé pour la raison suivante : ".$e->getMessage());
					return $this->redirect($this->generateUrl(
					'compta_facture_liste_retard'
				));
			}

			$relance = new Relance();
			$relance->setType('COURRIER');
			$relance->setDate(new \DateTime(date('Y-m-d')));
			$relance->setFacture($facture);
			$relance->setContact($facture->getContact());
			$relance->setUser($this->getUser());
			$relance->setMessage($message);
			$relance->setNum(count($arr_relances)+1);
			$relance->setObjet('RETARD');

			//retirer crédit
			$company = $this->getUser()->getCompany();
			$credit = $company->getCredits();
			if($recommande){
				$credit-=6.8;
			}else {
				$credit-=1.3;
			}
			$company->setCredits($credit);

			$em = $this->getDoctrine()->getManager();
			$em->persist($relance);
			$em->persist($company);
			$em->flush();

			$this->get('session')->getFlashBag()->add('success', "Votre courrier a bien été transmis à Maileva. Vous allez recevoir un email de confirmation.");

			return $this->redirect($this->generateUrl(
					'compta_facture_liste_retard'
			));

		}

		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));

		$today = new \DateTime(date('Y-m-d'));
		return $this->render('compta/relance/compta_relance_courrier_modal.html.twig', array(
				'form' => $form->createView(),
				'facture' => $facture,
				'contact_admin' => $contactAdmin->getValeur(),
				'today' => $today
		));
	}

	/**
	 * @Route("/compta/facture/relancer/{id}",
	 *   name="compta_facture_relancer",
	 *   options={"expose"=true}
	 * )
	 */
	public function factureRelancerAction(DocumentPrix $facture)
	{

		return $this->render('compta/relance/compta_relance_choix_email_courrier_modal.html.twig', array(
				'facture' => $facture
		));
	}


	private function _factureCreatePDF(DocumentPrix $facture)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$footerFacture = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PIED_DE_PAGE_FACTURE', 'company'=>$this->getUser()->getCompany()));

		$arr_pub = array();
		$arr_pub['texte'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_TEXTE', 'company'=>$this->getUser()->getCompany()));
		$arr_pub['image'] = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'PUB_FACTURE_IMAGE', 'company'=>$this->getUser()->getCompany()));

		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$rib = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'RIB'));

		$html = $this->renderView('crm/facture/crm_facture_exporter.html.twig',array(
				'facture' => $facture,
				'footer' => $footerFacture,
				'pub' => $arr_pub,
				'contact_admin' => $contactAdmin->getValeur(),
				'RIB' => $rib
		));

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../web/files/crm/'.$this->getUser()->getCompany()->getId().'/facture/';

		$nomClient = strtolower(str_ireplace(' ','', $facture->getCompte()->getNom()));
		$accents = array('á','à','â','ä','ã','å','ç','é','è','ê','ë','í','ì','î','ï','ñ','ó','ò','ô','ö','õ','ú','ù','û','ü','ý','ÿ');
		$sans_accents = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y');
		$nomClient=str_ireplace($accents, $sans_accents, $nomClient);
		$fileName =$pdfFolder.$facture->getNum().'.'.$nomClient.'.pdf';

		$this->get('knp_snappy.pdf')->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	private function _relanceCreatePDF(DocumentPrix $facture, $message, $numRelance)
	{
		$settingsRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Settings');
		$contactAdmin = $settingsRepository->findOneBy(array('module' => 'CRM', 'parametre' => 'CONTACT_ADMINISTRATIF', 'company'=>$this->getUser()->getCompany()));
		$today = new \DateTime(date('Y-m-d'));

		$html = $this->renderView('compta/relance/compta_relance_exporter.html.twig',array(
				'facture' => $facture,
				'message' => $message,
				'contact_admin' => $contactAdmin->getValeur(),
				'today' => $today
		));

		$pdfFolder = $this->container->getParameter('kernel.root_dir').'/../web/files/compta/'.$this->getUser()->getCompany()->getId().'/relance/';

		$nomClient = strtolower(str_ireplace(' ','', $facture->getCompte()->getNom()));
		$accents = array('á','à','â','ä','ã','å','ç','é','è','ê','ë','í','ì','î','ï','ñ','ó','ò','ô','ö','õ','ú','ù','û','ü','ý','ÿ');
		$sans_accents = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y');
		$nomClient=str_replace($accents, $sans_accents, $nomClient);
		$fileName = str_replace('-', '', $numRelance.$facture->getNum());
		$fileName = $pdfFolder.'relance'.$fileName.'.001';

		$this->get('knp_snappy.pdf')->generateFromHtml($html, $fileName, array('javascript-delay' => 60), true);

		return $fileName;
	}

	/**
	 * @Route("/compta/facture/importer-historique/upload", name="compta_facture_importer_historique_upload")
	 */
	public function factureImporterHistoriqueUploadAction()
	{
		$em = $this->getDoctrine()->getManager();
		$requestData = $this->getRequest();

		$arr_files = $requestData->files->all();
		$file = $arr_files["files"][0];
		///enregistrement temporaire du fichier uploadé
		$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_factures';
		$file->move($path, $filename);
		$session = $requestData->getSession();
		$session->set('import_historique_facture_filename', $filename);

		$response = new JsonResponse();
		$response->setData(array(
				'filename' => $filename
		));

		return $response;
	}

	/**
	 * @Route("/compta/facture/importer-historique", name="compta_facture_importer_historique")
	 */
	public function factureImporterHistoriqueAction()
	{
		$form = $this->createForm(new UploadHistoriqueFactureType($this->getUser()->getCompany()));

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			if($form['file']->getData() == null){
				throw new \Exception("Vous n'avez pas uploadé de fichier.");
			}

			//recuperation des données du formulaire
			$data = $form->getData();
			$file = $data['file'];

			//enregistrement temporaire du fichier uploadé
			$filename = date('Ymdhms').'-'.$this->getUser()->getId().'-'.$file->getClientOriginalName();
			$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_factures';
			$file->move($path, $filename);
			$session = $request->getSession();
			$session->set('import_historique_facture_filename', $filename);

			//creation du formulaire de mapping
			return $this->redirect($this->generateUrl('compta_facture_importer_historique_mapping'));
		}

		return $this->render('compta/facture/compta_facture_importer_historique.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/facture/importer-historique/mapping/{initialisation}", name="compta_facture_importer_historique_mapping")
	 */
	public function factureImporterMappingAction($initialisation = false)
	{
		$request = $this->getRequest();
		$session = $request->getSession();

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_factures';
		$filename = $session->get('import_historique_facture_filename');
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

		$form_mapping = $this->createForm(new UploadHistoriqueFactureMappingType($arr_headers, $filename));
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

	}

	/**
	 * @Route("/compta/facture/importer-historique/validation/{initialisation}", name="compta_facture_importer_historique_validation")
	 */
	public function factureImporterValidationAction($initialisation = false)
	{
		$request = $this->getRequest();
		$session = $request->getSession();

		$compteRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\Compte');
		$userManager = $this->get('fos_user.user_manager');
		$userRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:User');

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_factures';
		$filename = $session->get('import_historique_facture_filename');

		//recuperation du mapping
		$arr_mapping = $session->get('import_historique_facture_arr_mapping');

		//parsing du CSV
		$csv = new \parseCSV();
		$csv->delimiter = ";";
		$csv->parse($path.'/'.$filename);

		//array contenant les erreurs
		$arr_err_comptes = array();
		$arr_err_users = array();

		//parsing ligne par ligne
		$total = 0;
		$i = 0;
		$prevNum = null;
		while($i<count($csv->data)){
			$data = $csv->data[$i];

			$num = $data[$arr_mapping['num']];
			if($num != $prevNum){

				//est-ce que tous les comptes à importer existent ?
				$nomCompte = $data[$arr_mapping['compte']];
				$compte = $compteRepo->findOneBy(array(
						'nom' => $nomCompte,
						'company' => $this->getUser()->getCompany()
				));
				if($compte == null){
					if(!in_array($nomCompte, $arr_err_comptes)){
						$arr_err_comptes[] = $nomCompte;
					}

				}

				//est-ce que tous les utilisateurs à importer existent ?
				$nomUser = $data[$arr_mapping['user']];
				$user = $userManager->findUserByUsernameOrEmail($nomUser);
				if($user == null){
					if(!in_array($nomUser, $arr_err_users)){
						$arr_err_users[] = $nomUser;
					}

				}
				$prevNum = $num;

			}

			$i++;
		}

		$formBuilder = $this->createFormBuilder();

		if(count($arr_err_comptes) != 0){
			$arr_choices_comptes = array('NEW' => 'Créer un nouveau compte');
			$arr_all_comptes = $compteRepo->findBy(
					array('company' => $this->getUser()->getCompany()),
					array('nom' => 'ASC')
			);
			foreach($arr_all_comptes as $compte){
				$arr_choices_comptes[$compte->getId()] = $compte->getNom();
			}

			foreach($arr_err_comptes as  $key => $err_compte){
				$formBuilder->add('compte-'.$key, 'choice', array(
					'choices' => $arr_choices_comptes,
					'label' => $err_compte
				));
			}
		}

		if(count($arr_err_users) != 0){
			$arr_choices_users = array();
			$arr_all_users = $userRepo->findByCompany($this->getUser()->getCompany());
			foreach($arr_all_users as $user){
				$arr_choices_users[$user->getId()] = $user->__toString();
			}

			foreach($arr_err_users as  $key => $err_user){
				$formBuilder->add('user-'.$key, 'choice', array(
						'choices' => $arr_choices_users,
						'label' => $err_user,
						'required' => true
				));
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

			$arr_validation = array();
			foreach($arr_err_comptes as  $key => $err_compte){
				$arr_validation[$err_compte] = $data['compte-'.$key];
			}
			foreach($arr_err_users as  $key => $err_user){
				$arr_validation[$err_user] = $data['user-'.$key];
			}

			$session->set('import_historique_facture_arr_validation', $arr_validation);

			return $this->redirect($this->generateUrl('compta_facture_importer_historique_execution', array('initialisation' => $initialisation)));

		}

		return $this->render('compta/facture/compta_facture_importer_historique_validation.html.twig', array(
				'arr_err_comptes' => $arr_err_comptes,
				'arr_err_users' => $arr_err_users,
				'form' => $form->createView()
		));
	}

	/**
	 * @Route("/compta/facture/importer-historique/execution/{initialisation}", name="compta_facture_importer_historique_execution")
	 */
	public function factureImporterExecutionAction($initialisation = false)
	{
		$request = $this->getRequest();
		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();
		$compteRepo = $em->getRepository('AppBundle:CRM\Compte');
		$userManager = $this->get('fos_user.user_manager');
		$settingsRepo = $em->getRepository('AppBundle:Settings');

		//recuperation et ouverture du fichier temporaire uploadé
		$path =  $this->get('kernel')->getRootDir().'/../web/upload/compta/historique_factures';
		$filename = $session->get('import_historique_facture_filename');

		//recuperation du mapping
		$arr_mapping = $session->get('import_historique_facture_arr_mapping');

		//recuperation de la validation (correction des comptes et users inexistants)
		$arr_validation = $session->get('import_historique_facture_arr_validation');

		//parsing du CSV
		$csv = new \parseCSV();
		$csv->delimiter = ";";
		$csv->parse($path.'/'.$filename);

		$total = 0;
		$i = 0;
		$prevNum = null;
		$arr_factures = array();

		//parsing ligne par ligne
		while($i<count($csv->data)){

			$data = $csv->data[$i];

			$num = $data[$arr_mapping['num']];
			if($num != $prevNum){

				//creation et hydratation de la facture
				$facture = new DocumentPrix();
				$facture->setType('FACTURE');
				$facture->setObjet($data[$arr_mapping['objet']]);
				$facture->setCompta(true);

				$facture->setNum($num);

				$nomCompte = $data[$arr_mapping['compte']];
				if(!array_key_exists($nomCompte, $arr_validation)){
					$compte = $compteRepo->findOneByNom($nomCompte);
				} else {
					if($arr_validation[$nomCompte] == 'NEW'){
						$compte = new Compte();
						$compte->setNom($nomCompte);
						$compte->setDateCreation(new \DateTime(date('Y-m-d')));
						$compte->setUserCreation($this->getUser());
						$compte->setUserGestion($this->getUser());
						$compte->setCompany($this->getUser()->getCompany());
						$em->persist($compte);
					} else {
						$compte = $compteRepo->findOneById($arr_validation[$nomCompte]);
					}
				}
				$facture->setCompte($compte);

				$date = \DateTime::createFromFormat('d/m/Y', $data[$arr_mapping['date']]);
				$facture->setDateCreation($date);
				$echeance = \DateTime::createFromFormat('d/m/Y', $data[$arr_mapping['echeance']]);
				$facture->setDateValidite($echeance);
				$facture->setTaxe($data[$arr_mapping['tva']]);

				$tauxTVA = str_replace('%', '', $data[$arr_mapping['tauxTVA']]);
				if($tauxTVA>1){
					$tauxTVA = $tauxTVA/100;
				}
				$facture->setTaxePercent($tauxTVA);
				$facture->setRemise($data[$arr_mapping['remise']]);
				if($arr_mapping['description']){
					$facture->setDescription($data[$arr_mapping['description']]);
				}
				$facture->setEtat($data[$arr_mapping['etat']]);

				$nomUser = $data[$arr_mapping['user']];
				if(!array_key_exists($nomUser, $arr_validation)){
					$user = $userManager->findUserByUsernameOrEmail($nomUser);
				} else {
					$user = $user = $userManager->findUserBy(array('id' => $arr_validation[$nomUser]));
				}

				$facture->setUserCreation($user);
				$facture->setUserGestion($user);

				$prevNum = $num;

				$em->persist($facture);
				$arr_factures[] = $facture;
			}

			//creation et hydratation du produit
			$produit = new Produit();
			$produit->setDocumentPrix($facture);
			$produit->setNom($data[$arr_mapping['produitNom']]);
			$produit->setDescription($data[$arr_mapping['produitDescription']]);
			$produit->setTarifUnitaire($data[$arr_mapping['produitTarif']]);
			$produit->setQuantite($data[$arr_mapping['produitQuantite']]);

			$nomType = $arr_mapping['produitType'];
			$type = $settingsRepo->findOneBy(array(
					'company' => $this->getUser()->getCompany(),
					'module' => 'CRM',
					'parametre' => 'TYPE_PRODUIT'
			));

			$produit->setType($type);
			$facture->addProduit($produit);

			$em->persist($produit);

			$i++;

		}

		$em->flush();

		foreach($arr_factures as $facture){
			//ecrire dans le journal de vente
			$journalVenteService = $this->container->get('appbundle.compta_journal_ventes_controller');
			$result = $journalVenteService->journalVentesAjouterFactureAction($facture);

		}

		//suppression du fichier temporaire
		unlink($path.'/'.$filename);

		//suppression des variables de session
		$session->remove('import_historique_facture_filename');
		$session->remove('import_historique_facture_arr_mapping');
		$session->remove('import_historique_facture_arr_validation');

		if($initialisation){
			return $this->redirect($this->generateUrl('compta_activer_importer_facture_ok'));
		}

		return $this->redirect($this->generateUrl('compta_facture_choisir'));
	}

	/**
	 * @Route("/compta/facture/choisir/{initialisation}", name="compta_facture_choisir")
	 */
	public function factureChoisirAction($initialisation = false)
	{
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:CRM\DocumentPrix');

		$request = $this->getRequest();
		$data = $request->request->all();
		if($data){

			foreach($data as $id){

				$facture = $repo->find($id);
				$facture->setCompta(true);
				$em->persist($facture);


			}
			$em->flush();

			if($initialisation){
				return $this->redirect($this->generateUrl('compta_activer_tva'));
			}

			return $this->redirect($this->generateUrl('compta_facture_liste'));
		}

		$arr_factures = $repo->findForCompany($this->getUser()->getCompany(), 'FACTURE');

		$arr_by_year = array();
		foreach($arr_factures as $facture){

			$arr_num = explode('-',$facture->getNum());
			$year = $arr_num[0];

			if(!(array_key_exists($year, $arr_by_year))){
				$arr = array($facture);
				$arr_by_year[$year] = $arr;
			} else{
				$arr_by_year[$year][] = $facture;
			}
		}



		return $this->render('compta/facture/compta_facture_choisir.html.twig', array(
				'arr_factures' => $arr_by_year,
				'initialisation' => $initialisation
		));
	}


}
