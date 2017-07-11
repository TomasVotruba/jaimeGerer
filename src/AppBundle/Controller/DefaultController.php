<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\Company;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class DefaultController extends Controller
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

    /**
     * @Route("/importer", name="importer")
     */
    public function importerAction(){

    	return $this->render('crm/settings/crm_settings_importer.html.twig');
    }



}
