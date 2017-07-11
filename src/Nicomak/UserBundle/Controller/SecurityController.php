<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nicomak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;


use FOS\UserBundle\Controller\SecurityController as BaseController;

class SecurityController extends BaseController
{

	public function loginAction()
	{
		if ($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') || $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
		{
			// redirect authenticated users to homepage
			//~ return $this->redirect($this->generateUrl('homepage'));
			return new RedirectResponse($this->container->get('router')->generate('homepage'));
		}
		
		return parent::loginAction();
	}

}
