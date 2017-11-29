<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use AppBundle\Form\Compta\DepenseSousTraitanceType;

class DepenseSousTraitanceRepartitionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder       
            ->add('sousTraitances', 'collection', array(
                'type' => new DepenseSousTraitanceType(),
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden')
            ));
    }

   /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\Depense'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_depensesoustraitancerepartition';
    }
}
