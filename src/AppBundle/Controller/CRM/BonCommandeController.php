<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use AppBundle\Entity\CRM\BonCommande;

class BonCommandeController extends Controller
{

	/**
	 * @Route("/crm/bon-commande/get-liste",
	 *   name="crm_bon_commande_get_liste",
	 *   options={"expose"=true}
	 * )
	 */
	public function bonCommandeGetListeAction() {

		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\BonCommande');

		$list = $repository->findByCompany($this->getUser()->getCompany());

		$res = array();
		foreach ($list as $bc) {

			$_res = array('id' => $bc->getId(), 'display' => $bc->getNum());
			$res[] = $_res;
		}

		$response = new \Symfony\Component\HttpFoundation\Response(json_encode($res));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}

	/**
	 * @Route("/crm/bon_commande/liste",
	 *   name="crm_bon_commande_liste",
	 *  )
	 */
	public function bonCommandeListeAction()
	{

		$activationRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:SettingsActivationOutil');
		$activation = $activationRepo->findOneBy(array(
			'company' => $this->getUser()->getCompany(),
			'outil' => 'CRM'
		));
		$yearActivation = $activation->getDate()->format('Y');

		$currentYear = date('Y');
		$arr_years = array();
		for($i = $yearActivation ; $i<=$currentYear; $i++){
			$arr_years[$i] = $i;
		}

		$formBuilder = $this->createFormBuilder();
		$formBuilder
			->add('year', 'choice', array(
					'required' => true,
					'label' => 'Année',
					'choices' => $arr_years,
					'attr' => array('class' => 'year-select'),
					'data' => $currentYear
			))
			->add('etat', 'choice', array(
					'required' => true,
					'label' => 'Etat',
					'choices' => array(
						'all' => 'Tous',
						'ok' => 'OK',
						'current' => 'En cours',
						'ko' => 'Problème'
					),
					'attr' => array('class' => 'etat-radio'),
					'expanded' => true,
					'data' => 'all'
			));


		$form = $formBuilder->getForm();

		return $this->render('crm/bon-commande/crm_bon_commande_liste.html.twig', array(
			'form' => $form->createView()
		));
	}	

	/**
	 * @Route("/crm/bon-commande/check",
	 *   name="crm_bon_commande_check",
	 *   options={"expose"=true}
	 * )
	 */
	public function bonCommandeCheckAction() {

		$requestData = $this->getRequest();
		$repository = $this->getDoctrine()->getManager()->getRepository('AppBundle:CRM\BonCommande');

		$arr_sort = $requestData->get('order');
		$arr_cols = $requestData->get('columns');
		$col = $arr_sort[0]['column'];
		$arr_search = $requestData->get('search');

		$year = $requestData->get('year');
		$etat = $requestData->get('etat');

		$arr_all = $repository->findForList(
			$this->getUser()->getCompany(),
			$requestData->get('length'),
			$requestData->get('start'),
			$arr_cols[$col]['data'],
			$arr_sort[0]['dir'],
			$arr_search['value'],
			$year
		);

		$i = 0;
		$list = array();
		foreach ($arr_all as $bc) {

			$ok = false;
			if(strtoupper($etat) == "ALL"){
				$ok = true;
			} else if( strtoupper($etat) == "OK" && $bc->getMontant() ==  $bc->getTotalFacture() ){
				$ok = true;
			} else if ( strtoupper($etat) == "CURRENT" && $bc->getMontant() > $bc->getTotalFacture() ){
				$ok = true;
			} else if ( strtoupper($etat) == 'KO' && $bc->getMontant() < $bc->getTotalFacture() ) {
				$ok = true;
			}

			if($ok){
				$list[$i]['num'] = $bc->getNum();
				$list[$i]['compte'] = $bc->getActionCommerciale()->getCompte()->getNom();
				$list[$i]['compte_id'] = $bc->getActionCommerciale()->getCompte()->getId();
				$list[$i]['objet'] = $bc->getActionCommerciale()->getNom();
				$list[$i]['action_commerciale'] = $bc->getActionCommerciale()->getId();
				$list[$i]['montant'] = $bc->getMontantMonetaire();
				$list[$i]['montant_facture'] = $bc->getTotalFactureMonetaire();
					
				if(count($bc->getFactures()) == 0){
					$list[$i]['factures'] = null;
					$list[$i]['factures_id'] = null;
				} else {
					$list[$i]['factures'] = array();
					$list[$i]['factures_id'] = array();
					foreach($bc->getFactures() as $facture ){
						$list[$i]['factures'][] = $facture->getNum();
						$list[$i]['factures_id'][]= $facture->getId();
					} 
				}
				
				$i++;
			}
			
		}

		$response = new JsonResponse();
		$response->setData(array(
			'draw' => intval( $requestData->get('draw') ),
			'recordsTotal' => $repository->countForList($this->getUser()->getCompany(), $arr_search['value']),
			'recordsFiltered' => count($list),
			'aaData' => $list,
		));
		return $response;

	}


	/**
	 * @Route("/crm/bon-commande/init",
	 *   name="crm_bon_commande_init",
	 *   options={"expose"=true}
	 * )
	 */
	public function init(){

		$em  = $this->getDoctrine()->getManager();
		$bonCommandeRepo = $em->getRepository('AppBundle:CRM\BonCommande');
		$factureRepo = $em->getRepository('AppBundle:CRM\DocumentPrix');


		$all_bc = $bonCommandeRepo->findAll();

		foreach($all_bc as $bc){
			if($bc->getFacture()){
				$facture = $bc->getFacture();
				$facture->setBonCommande($bc);
				$em->persist($facture);
			}
		}

		$em->flush();

		return new Response();

	}



}
