<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use libphonenumber\PhoneNumberFormat;

class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('nom', 'text', array(
        		'required' => true,
            	'label' => 'Raison sociale'
        	))
            ->add('telephone', 'text', array(
            	'required' => false,
            	//'default_region' => 'FR',
            	//'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone'
        	))
            ->add('fax','text', array(
            	'required' => false,
            	//'default_region' => 'FR',
            	//'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('adresse', 'text', array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', 'text', array(
        		'required' => false,
            	'label' => 'Code postal'
        	))
            ->add('ville', 'text', array(
        		'required' => false,
            	'label' => 'Ville'
        	))
            ->add('region', 'text', array(
        		'required' => false,
            	'label' => 'Région'
        	))
            ->add('pays', 'text', array(
        		'required' => false,
            	'label' => 'Pays'
        	))
        	->add('color', 'color_picker', array(
        			'required' => false,
        			'label' => 'Couleur principale',
        			'attr' => array('class' => 'colorpicker'),
        			'picker_options' => array('palettes' => true),
        			'empty_data' => '#FFFFFF'
        	))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Company'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_company';
    }
}
