<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BonCommandeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num', 'text', array(
                'required' => true
            ))
            ->add('montantMonetaire', 'number', array(
                'required' => true,
                'attr' => array('class' => 'align-right')
            ))
            ->add('fraisRefacturables', 'checkbox', array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-size' => 'mini',
                    'data-on' => 'Oui',
                    'data-off' => 'Non'
                )
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\BonCommande'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_bon_commande';
    }
}
