<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use AppBundle\Form\CRM\SousTraitanceRepartitionType;

class OpportuniteSousTraitanceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $opportuniteSousTraitance = $builder->getData();

        $builder
            ->add('sousTraitant', 'text', array(
              'label' => 'Nom du sous-traitant',
              'required' => true
            ))
            ->add('typeForfait', 'choice', array(
              'choices' => array(
                'GLOBAL' => 'Forfait global',
                'JOUR' => 'Forfait jour'
              ),
              'expanded' => true,
              'multiple' => false,
              'label' => 'Type de forfait',
              'required' => true,
              'attr' => array('class' => 'type-forfait')
            ))
            ->add('montantGlobalMonetaire', 'number', array(
              'required' => false,
              'label' => 'Montant du forfait',
            ))
            ->add('tarifJourMonetaire', 'number', array(
              'required' => false,
              'label' => 'Tarif par jour',
            ))
            ->add('nbJours', 'number', array(
              'required' => false,
              'label' => 'Nombre de jours',
            ))
            ->add('repartitions', 'collection', array(
           			'type' => new SousTraitanceRepartitionType($opportuniteSousTraitance->getOpportunite()),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
								'label_attr' => array('class' => 'hidden')
           	))
            ->add('add', 'submit', array(
                'label' => 'Ajouter un autre sous-traitant',
                'attr' => array(
                  'class' => 'btn btn-info',

                )
            ))
            ->add('submit', 'submit', array(
                'label' => 'Terminer',
                'attr' => array(
                  'class' => 'btn btn-success',

                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\OpportuniteSousTraitance'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_opportunitesoustraitance';
    }
}
