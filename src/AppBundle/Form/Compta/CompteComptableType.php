<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompteComptableType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num', 'text', array(
        		'required' => true,
            	'label' => 'Numéro (8 caractères maximum)'
        	))
            ->add('nom', 'text', array(
        		'required' => true,
            	'label' => 'Libellé',
        	))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\CompteComptable'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_comptecomptable';
    }
}
