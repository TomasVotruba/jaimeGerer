<?php
namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->remove('username');
		$builder->remove('email');
		$builder->remove('plainPassword');
	
		$builder
			->add('firstname', 'text', array(
					'label' => 'Prénom')
			)
            ->add('lastname', 'text', array(
            		'label' => 'Nom')
            )
             ->add('email', 'email', array(
            		'label' => 'Email')
            )
             ->add('plainPassword', 'password', array(
             	'label' => 'Mot de passe'
             ))
             ->add('username', 'text', array(
             		'label' => 'Identifiant' 
             ));
	}

	public function getParent()
	{
		return 'fos_user_registration';
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'appbundle_user_registration';
	}
}