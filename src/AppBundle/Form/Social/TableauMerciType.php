<?php

namespace AppBundle\Form\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TableauMerciType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebut', 'date', array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date de début'
            ))
            ->add('dateFin', 'date', array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date de fin'
            ))
            ->add('objectifInterne','integer', array(
                'required' => true,
                'label' => 'Objectif interne',
            ))
            ->add('objectifExterne','integer', array(
                'required' => true,
                'label' => 'Objectif externe',
            ))
            ->add('submit', 'submit', array(
                'label' => 'Valider',
                'attr' => array('class' => 'btn btn-success'),
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Social\TableauMerci'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_social_tableaumerci';
    }
}
