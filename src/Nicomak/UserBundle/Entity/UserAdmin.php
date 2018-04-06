<?php

namespace Nicomak\UserBundle\Entity;

use Sonata\UserBundle\Admin\Entity\UserAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class UserAdmin extends BaseAdmin
{

	/**
	 * {@inheritdoc}
	 */
    protected $baseRoutePattern = 'utilisateurs';
	protected $baseRouteName = 'AppBundle\Entity\SuperEntityAdmin';
    
	/**
        * {@inheritdoc}
        */
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->with('company_section')
                ->add('company')
            ->end()
        ;
    }
	
}