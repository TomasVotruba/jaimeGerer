<?php

namespace AppBundle\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampagneType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomCampagne', 'text', array(
            	'required' => true,
            	'label' => 'Nom de la campagne'
        	))
            ->add('objetEmail', 'text', array(
            	'required' => true,
            	'label' => 'Objet de l\'email'
        	)) 
            ->add('nomExpediteur', 'text', array(
            	'required' => true,
            	'label' => 'Nom de l\'expéditeur'
        	))
            ->add('emailExpediteur', 'email', array(
            	'required' => true,
            	'label' => 'Email de l\'expéditeur'
        	))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Emailing\Campagne'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_emailing_campagne';
    }

}
