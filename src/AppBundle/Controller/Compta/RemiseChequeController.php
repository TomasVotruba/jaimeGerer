<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Compta\RemiseCheque;
use AppBundle\Entity\Compta\ChequePiece;
use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Form\Compta\RemiseChequeType;

class RemiseChequeController extends Controller
{
	/**
	 * @Route("/compta/remise-cheque", name="compta_remise_cheque_liste")
	 */
	public function remiseChequeListeAction()
	{
		return $this->render('compta/remise-cheque/compta_remise_cheque_liste.html.twig');
	}

	/**
	 * @Route("/compta/remise-cheque/liste/ajax",
	 *   name="compta_remise_cheque_liste_ajax",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeListeAjaxAction()
	{
		$requestData = $this->getRequest();
		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');

		$col = $arr_sort[0]['column'];

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\RemiseCheque');

		$arr_search = $requestData->get('search');

		$list = $repository->findForList(
				$this->getUser()->getCompany(),
				$requestData->get('length'),
				$requestData->get('start'),
				$arr_cols[$col]['data'],
				$arr_sort[0]['dir'],
				$arr_search['value']
		);


		for($i=0; $i<count($list); $i++){
			$arr_f = $list[$i];

			$remiseCheques = $repository->find($arr_f['id']);
			$total = $remiseCheques->getTotal();
			$list[$i]['total'] = $total;
			$list[$i]['nbCheques'] = count($remiseCheques->getCheques());

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
	 * @Route("/compta/remise-cheque/voir/{id}",
	 *   name="compta_remise_cheque_voir",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeVoirAction(RemiseCheque $remiseCheque)
	{
		return $this->render('compta/remise-cheque/compta_remise_cheque_voir.html.twig', array(
				'remiseCheque' => $remiseCheque,
		));
	}

	/**
	 * @Route("/compta/remise-cheque/ajouter", name="compta_remise_cheque_ajouter")
	 */
	public function remiseChequeAjouterAction()
	{
		$em = $this->getDoctrine()->getManager();
		$repoRemiseCheque = $em->getRepository('AppBundle:Compta\RemiseCheque');
		$repoFactures = $em->getRepository('AppBundle:CRM\DocumentPrix');

		//remises de cheque
		$arr_all_remises_cheques = $repoRemiseCheque->findForCompany($this->getUser()->getCompany());
		$arr_remises_cheques = array();
		$arr_factures_rapprochees_par_remises_cheques = array();
		$arr_avoirs_rapprochees_par_remises_cheques = array();
		foreach($arr_all_remises_cheques as $remiseCheque){
			if($remiseCheque->getTotalRapproche() < $remiseCheque->getTotal()){
				$arr_remises_cheques[] = $remiseCheque;
			} else {
				foreach($remiseCheque->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()){
							$arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}else if($piece->getAvoir()){
							$arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}
					}
				}
			}
		}

		//factures
		$arr_all_factures = $repoFactures->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
		$arr_factures = array();
		foreach($arr_all_factures as $facture){
			if($facture->getTotalRapproche() < $facture->getTotalTTC() && $facture->getEtat() != "PAID" && !in_array($facture->getId(), $arr_factures_rapprochees_par_remises_cheques) && $facture->getTotalAvoirs() < $facture->getTotalTTC()){
				$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
			}
		}

		$avoirsRepo = $em->getRepository('AppBundle:Compta\Avoir');
		$arr_avoirs = array();
		$arr_avoirs_tmp = $avoirsRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
		foreach($arr_avoirs as $avoir){
			$arr_avoirs['A'.$avoir->getId()] = $avoir->getNum().' - '.$avoir->getCompte().' - '.$avoir->getTotalTTC();
		}

		$arr_autres = array(
			'AUTRE' => 'Autre'
		);

		$arr_pieces = array(
			'FACTURES' => $arr_factures,
			'AVOIRS FOURNISSEURS' => $arr_avoirs,
			'AUTRE' => $arr_autres,
		);

		$remiseCheque = new RemiseCheque();
		$remiseCheque->setDate(new \DateTime(date('Y-m-d')));
		$form = $this->createForm(
				new RemiseChequeType(
					$this->getUser()->getCompany()->getId(),
					$arr_pieces
				),
				$remiseCheque
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$data = $form->getData();

			$arr_cheques = $form['cheques'];
			$i = 0;
			foreach($arr_cheques as $form_cheque){
				$arr_pieces_id = $form_cheque['select']->getData();
				foreach($arr_pieces_id as $s_id){
					$chequePiece = new ChequePiece();
					$cheque = $data->getCheques()[$i];
					$chequePiece->setCheque($cheque);

					$type = substr($s_id,0,1);
					$num = substr($s_id,1);

					if($type == 'F'){
						$facture = $repoFactures->find($num);
						$facture->setEtat('PAID');
						$em->persist($facture);
						$chequePiece->setFacture($facture);
					} else if($type == 'A'){
						$avoir = $avoirsRepo->find($num);
						$chequePiece->setAvoir($avoir);
					}
					$cheque->addPiece($chequePiece);
				}
				$i++;
			}

			$remiseCheque->setDateCreation(new \DateTime(date('Y-m-d')));
			$remiseCheque->setUserCreation($this->getUser());

			//numéro de remise de cheque
			$settingsRepository = $em->getRepository('AppBundle:Settings');
			$settingsNum = $settingsRepository->findOneBy(array('module' => 'COMPTA', 'parametre' => 'NUMERO_REMISE_CHEQUE', 'company'=>$this->getUser()->getCompany()));
			$currentNum = $settingsNum->getValeur();
			$prefixe = 'RDC-'.date('Y').'-';
			if($currentNum < 10){
				$prefixe.='00';
			} else if ($currentNum < 100){
				$prefixe.='0';
			}
			$remiseCheque->setNum($prefixe.$currentNum);

			//mise à jour du numero de remise de cheque
			$currentNum++;
			$settingsNum->setValeur($currentNum);
			$em->persist($settingsNum);


			$em->persist($remiseCheque);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'compta_remise_cheque_liste'));

		}

		return $this->render('compta/remise-cheque/compta_remise_cheque_ajouter.html.twig', array(
				'form' => $form->createView(),
		));
	}

	/**
	 * @Route("/compta/remise-cheque/editer/{id}",
	 *   name="compta_remise_cheque_editer",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeEditerAction(RemiseCheque $remiseCheque)
	{
		$em = $this->getDoctrine()->getManager();
		$repoRemiseCheque = $em->getRepository('AppBundle:Compta\RemiseCheque');
		$repoFactures = $em->getRepository('AppBundle:CRM\DocumentPrix');

		//remises de cheque
		$arr_all_remises_cheques = $repoRemiseCheque->findForCompany($this->getUser()->getCompany());
		$arr_remises_cheques = array();
		$arr_factures_rapprochees_par_remises_cheques = array();
		$arr_avoirs_rapprochees_par_remises_cheques = array();
		foreach($arr_all_remises_cheques as $remiseCheque){
			if($remiseCheque->getTotalRapproche() < $remiseCheque->getTotal()){
				$arr_remises_cheques[] = $remiseCheque;
			} else {
				foreach($remiseCheque->getCheques() as $cheque){
					foreach($cheque->getPieces() as $piece){
						if($piece->getFacture()){
							$arr_factures_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}else if($piece->getAvoir()){
							$arr_avoirs_rapprochees_par_remises_cheques[] = $piece->getFacture()->getId();
						}
					}
				}
			}
		}

		//factures
		$arr_all_factures = $repoFactures->findForCompany($this->getUser()->getCompany(), 'FACTURE', true);
		$arr_factures = array();
		foreach($arr_all_factures as $facture){
			if($facture->getTotalRapproche() < $facture->getTotalTTC() && $facture->getEtat() != "PAID" && !in_array($facture->getId(), $arr_factures_rapprochees_par_remises_cheques) && $facture->getTotalAvoirs() < $facture->getTotalTTC()){
				$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
			}
		}

		foreach($remiseCheque->getCheques() as $cheque){
			foreach($cheque->getPieces() as  $piece){
				if($piece->getFacture()){
					$facture = $piece->getFacture();
					$arr_factures['F'.$facture->getId()] = $facture->getNum().' - '.$facture->getCompte().' - '.$facture->getTotalTTC().'€';
				}
			}
		}

		$avoirsRepo = $em->getRepository('AppBundle:Compta\Avoir');
		$arr_avoirs = array();
		$arr_avoirs_tmp = $avoirsRepo->findForCompany('FOURNISSEUR', $this->getUser()->getCompany());
		foreach($arr_avoirs as $avoir){
			$arr_avoirs['A'.$avoir->getId()] = $avoir->getNum().' - '.$avoir->getCompte().' - '.$avoir->getTotalTTC();
		}

		$arr_autres = array(
			'AUTRE' => 'Autre'
		);

		$arr_pieces = array(
			'FACTURES' => $arr_factures,
			'AVOIRS FOURNISSEURS' => $arr_avoirs,
			'AUTRE' => $arr_autres,
		);

		$form = $this->createForm(
				new RemiseChequeType(
					$this->getUser()->getCompany()->getId(),
					$arr_pieces
				),
				$remiseCheque
		);

		$request = $this->getRequest();
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$arr_cheques = $form['cheques'];

			//clear cheques collection
			foreach($remiseCheque->getCheques() as $oldCheque){
				$pieces = $oldCheque->getPieces();
				$pieces->clear();
				// $remiseCheque->removeCheque($oldCheque);
				// $em->remove($oldCheque);
			}

			foreach($arr_cheques as $form_cheque){
				$arr_pieces_id = $form_cheque['select']->getData();
				foreach($arr_pieces_id as $s_id){
					$chequePiece = new ChequePiece();
					$cheque = $form_cheque->getData();
					// $chequePiece->setCheque($cheque);

					$type = substr($s_id,0,1);
					$num = substr($s_id,1);
					if($type == 'F'){
						$facture = $repoFactures->find($num);
						$facture->setEtat('PAID');
						$em->persist($facture);
						$chequePiece->setFacture($facture);
					} else if($type == 'A'){
						$avoir = $avoirsRepo->find($num);
						$chequePiece->setAvoir($avoir);
					}
					$em->persist($chequePiece);
					$cheque->addPiece($chequePiece);
					$em->persist($cheque);

				}
				$remiseCheque->addCheque($cheque);
			}

			$remiseCheque->setDateEdition(new \DateTime(date('Y-m-d')));
			$remiseCheque->setUserEdition($this->getUser());

			$em->persist($remiseCheque);

			// dump($remiseCheque);
			$em->flush();

			return $this->redirect($this->generateUrl(
					'compta_remise_cheque_liste'
			));
		}

		return $this->render('compta/remise-cheque/compta_remise_cheque_editer.html.twig', array(
				'form' => $form->createView(),
				'remiseCheque' => $remiseCheque
		));
	}

	/**
	 * @Route("/compta/remise-cheque/exporter/{id}",
	 *   name="compta_remise_cheque_exporter",
	 *   options={"expose"=true}
	 * )
	 */
	public function remiseChequeExporterAction(RemiseCheque $remiseCheque)
	{
		$html = $this->renderView('compta/remise-cheque/compta_remise_cheque_exporter.html.twig',array(
				'remiseCheque' => $remiseCheque,
		));

		$filename = $remiseCheque->getNum().'.pdf';

		//$filename = $facture->getNum().'.'.$nomClient.'.pdf';
		return new Response(
				$this->get('knp_snappy.pdf')->getOutputFromHtml($html,
						array(
								'margin-bottom' => '10mm',
								'margin-top' => '10mm',
								'zoom' => 0.8, //prod only, zoom level is not the same on Windows
								'default-header'=>false,
						)
				),
				200,
				array(
						'Content-Type'          => 'application/pdf',
						'Content-Disposition'   => 'attachment; filename='.$filename,
				)
		);
	}

}
