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


}
