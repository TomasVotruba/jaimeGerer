<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;



class MerciController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
    	if($this->getUser() != null){

    		if($this->getUser()->getCompany() == null){
    			//company non paramétrée : paramétrage
    			return $this->redirect($this->generateUrl('admin_company_edit'));
    		} else {
    			//homepage
       	    	return $this->render('default/index.html.twig');
    		}
    	} else {
    		return $this->redirect('login');
    	}
    }


}
