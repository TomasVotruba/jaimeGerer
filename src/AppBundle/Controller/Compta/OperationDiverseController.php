<?php

namespace AppBundle\Controller\Compta;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Compta\OperationDiverse;
use AppBundle\Form\Compta\OperationDiverseType;

class OperationDiverseController extends Controller
{
	/**
	 * @Route("/compta/operation-diverse/liste", name="compta_operation_diverse_liste")
	 */
	public function operationDiverseListeAction(){
	
		//lignes des opérations diverses
		$repoOperationDiverse = $this->getDoctrine()->getManager()->getRepository('AppBundle:Compta\OperationDiverse');
		$arr_lignes = $repoOperationDiverse->findForCompany($this->getUser()->getCompany());
		
		return $this->render('compta/operation_diverse/compta_operation_diverse_liste.html.twig', array(
				'arr_lignes' => $arr_lignes,
		));
	}

}