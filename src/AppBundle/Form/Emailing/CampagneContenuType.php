<?php

namespace AppBundle\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampagneContenuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
            	'required' => true,
            	'label' => 'Contenu (fichier HTML)',
                'mapped' => false,
                'attr' => array('class' => 'file-input')
        	))
            ->add('preview', 'submit', array(
                'label' => 'Preview',
                'attr' => array('class' => 'btn btn-info preview')
            ))
            ->add('submit', 'submit', array(
                'label' => 'Suite',
                'attr' => array('class' => 'btn btn-success')
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
        return 'appbundle_emailing_campagne_contenu';
    }

}
