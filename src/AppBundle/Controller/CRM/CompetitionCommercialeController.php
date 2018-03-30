<?php

namespace AppBundle\Controller\CRM;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\CRM\CompetCom;
use AppBundle\Form\CRM\CompetComType;


class CompetitionCommercialeController extends Controller
{

	/**
	 * @Route("/crm/competition-commerciale", name="crm_competition_commerciale")
	 */
	public function competitionCommerciale()
	{
		$em = $this->getDoctrine()->getManager();
		$compteComRepo = $em->getRepository('AppBundle:CRM\CompetCom');
		$actionCommercialeRepo = $em->getRepository('AppBundle:CRM\Opportunite');
		$userRepo = $em->getRepository('AppBundle:User');

		$competCom = $compteComRepo->findCurrent();

		$users = $userRepo->findBy(array(
			'company' => $this->getUser()->getCompany(),
			'enabled' => true,
			'competCom' => true
		));

		$arr_users = array();
		foreach($users as $user){
			$arr_users[$user->getId()]['user'] = $user;
			$arr_users[$user->getId()]['CA'] = 0;
			$arr_users[$user->getId()]['ratioCA'] = 0;
			$arr_users[$user->getId()]['nbGagnes'] = 0;
			$arr_users[$user->getId()]['prescriptions'] = array();
			$arr_users[$user->getId()]['nouveauxComptes'] = array();
			$arr_users[$user->getId()]['gagnees'] = array();
		}

		$arr_winners = array(
			'nouveauxComptes' => null,
			'CA' => null,
			'prescriptions' => null,
		);

		if($competCom && count($arr_users)){

			$arr_actionsCommerciales = $actionCommercialeRepo->findWonBetweenDates(
				$this->getUser()->getCompany(),
				$competCom->getStartDate(), 
				$competCom->getEndDate()
			);

			foreach($arr_actionsCommerciales as $actionCommerciale){

				$user = $actionCommerciale->getUserGestion();

				if(!array_key_exists($user->getId(), $arr_users)){
					continue;
				}

				$arr_users[$user->getId()]['gagnees'][] = $actionCommerciale;
				$arr_users[$user->getId()]['CA']+= $actionCommerciale->getMontant();
				
				if($actionCommerciale->getPrescription()){
					$arr_users[$user->getId()]['prescriptions'][] = $actionCommerciale;
				}

				if($actionCommerciale->getPriveOrPublic() == "PRIVE" && $actionCommerciale->getType() == "New Business"){
					$arr_users[$user->getId()]['nouveauxComptes'][] = $actionCommerciale;
				}
			}

			$maxNouveauxComptes = 0;
			$maxRatioCA = 0;
			$maxPrescriptions = 0;
			foreach($arr_users as $userId => $arr_user){

				if(count($arr_users[$userId]['gagnees']) ){

					$ratioCA = $arr_users[$userId]['CA']/count($arr_users[$userId]['gagnees']);
					$arr_users[$userId]['ratioCA'] = $ratioCA;
					if($ratioCA > $maxRatioCA){
						$maxRatioCA = $arr_users[$userId]['CA'];
						$arr_winners['CA'] = $arr_user['user']->getId();
					}

					if(count($arr_users[$userId]['nouveauxComptes']) > $maxNouveauxComptes){
						$maxNouveauxComptes = count($arr_users[$userId]['nouveauxComptes']);
						$arr_winners['nouveauxComptes'] = $arr_user['user']->getId();
					}

					if(count($arr_users[$userId]['prescriptions']) > $maxPrescriptions){
						$maxPrescriptions = count($arr_users[$userId]['prescriptions']);
						$arr_winners['prescriptions'] = $arr_user['user']->getId();
					}

				}
			}
		}

		return $this->render('crm/competition-commerciale/crm_competition_commerciale.html.twig', array(
			'arr_winners' => $arr_winners,
			'arr_users'		=> $arr_users,
			'competCom' => $competCom
		));

	}

	/**
	 * @Route("/crm/competition-commerciale/ajouter", name="crm_competition_commerciale_ajouter")
	 */
	public function competitionCommercialeAjouter()
	{
		$em = $this->getDoctrine()->getManager();
		$competCom = new CompetCom();

		$form = $this->createForm(
			new CompetComType(),
			$competCom
		);

		$request = $this->getRequest();
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {

			$em->persist($competCom);
			$em->flush();

			return $this->redirect($this->generateUrl('crm_competition_commerciale'));
		}

		return $this->render('crm/competition-commerciale/crm_competition_commerciale_ajouter.html.twig', array(
			'form' => $form->createView()
		));
	}	

}