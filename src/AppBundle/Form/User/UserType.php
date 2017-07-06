<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
              'required' => true,
              'label' => 'Prénom'
            ))
            ->add('lastName', 'text', array(
              'required' => true,
              'label' => 'Nom'
            ))
            ->add('email', 'email', array(
              'required' => true,
              'label' => 'Adresse email'
            ))
            ->add('enabled', 'checkbox', array(
              'required' => false,
              'label' => ' ',
              'attr' => array(
                'data-toggle'=> "toggle",
                'data-onstyle'=> "success",
                'data-offstyle'=> "danger",
                'data-on'=> "Oui",
                'data-off'=> "Non"
              )
            ))
            ->add('admin', 'checkbox', array(
              'required' => false,
              'label' => ' ',
              'mapped' => false,
              'attr' => array(
                'data-toggle'=> "toggle",
                'data-onstyle'=> "success",
                'data-offstyle'=> "danger",
                'data-on'=> "Oui",
                'data-off'=> "Non"
              )
            ))
            ->add('permissions', 'choice', array(
              'mapped' => false,
              'multiple' => true,
              'expanded' => true,
              'label' => 'Peut utiliser :',
              'choices' => array(
                'ROLE_COMMERCIAL' => 'J\'aime le commercial',
                'ROLE_COMPTA' => 'J\'aime la compta',
                'ROLE_COMMUNICATION' => 'J\'aime communiquer',
                'ROLE_RH' => 'J\'aime le recrutement'
              )
            ))
            ->add('receiptBankId', 'text', array(
              'required' => false,
              'label' => 'ID Receipt Bank'
            ))
            ->add('submit', 'submit', array(
              'label' => 'Ajouter',
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
            'data_class' => 'AppBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_user';
    }
}
