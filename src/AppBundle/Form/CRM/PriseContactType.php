<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PriseContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$arr_types = array(
    		'PHONE' => 'Téléphone',
    		'RDV' => 'Rendez-vous',
    		'EMAIL' => 'Email',
    		'LETTRE' => 'Courrier',
    		'SOCIAL' => 'Réseaux sociaux',
    	);
    	
        $builder
            ->add('type', 'choice', array(
        		'choices' => $arr_types,
            	'required' => true,
            	'label' => 'Comment avez-vous pris contact ?'
        ))
            ->add('date', 'date', array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput'),
        			'required' => true,
        			'label' => 'Date de la prise de contact',
            		'data' => new \DateTime()
        	))
            ->add('description', 'textarea', array(
        		'required' => true,
            	'label' => 'Description'
        	))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\PriseContact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_prisecontact';
    }
}
