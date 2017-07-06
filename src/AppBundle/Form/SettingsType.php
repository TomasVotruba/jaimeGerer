<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        	->add('parametre', 'text', array(
        			'required' => true,
        			'label' => 'Parametre',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	))
        	->add('module', 'text', array(
        			'required' => true,
        			'label' => 'Module',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	));	
        	
        	$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }
    
    public function onPreSetData(FormEvent $event){
    	$settings = $event->getData();
    	$form = $event->getForm();
    	
    	if($settings->getType() == 'TEXTE'){
    		$form->add('valeur', 'textarea', array(
    				'required' => true,
    				'label' => 'Valeur',
    				'label_attr' => array('class' => 'hidden')
    		));
    	} else if($settings->getType() == 'IMAGE'){
    		$form->add('file', 'file', array(
    				'label'	=> 'Image',
    				'required' => true
    		));
    	} else {
    		$form->add('valeur', 'text', array(
    				'required' => true,
    				'label' => 'Valeur',
    				'label_attr' => array('class' => 'hidden')
    		));

    	}
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Settings'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_settings';
    }
}
