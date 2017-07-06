<?php

namespace AppBundle\Form\Emailing\mautic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MauticChangeCredentialsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('secretKey', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'Secret Key'
            ))
            ->add('publicKey', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'Public Key'
            ))
            ->add('Enregistrer', 'submit', array());

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_credentials';
    }
}