<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DepenseSousTraitanceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('montant')
            ->add('sousTraitance', 'entity', array(
                'class'=>'AppBundle:CRM\OpportuniteSousTraitance',
                'disabled' => true,
          
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\DepenseSousTraitance'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_depensesoustraitance';
    }
}
