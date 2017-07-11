<?php

namespace Nicomak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('NicomakUserBundle:Default:index.html.twig', array('name' => $name));
    }
    
    
}
