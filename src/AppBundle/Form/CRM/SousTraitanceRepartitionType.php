<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SousTraitanceRepartitionType extends AbstractType
{
    protected $opportunite;
    public function __construct ($opportunite = null)
    {
      $this->opportunite = $opportunite;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('date', 'date', array(
            'years' => range($this->opportunite->getRepartitionStartDate()->format('Y'), $this->opportunite->getRepartitionEndDate()->format('Y')),
            'required' => true,
            'input' => 'datetime',
            'widget' => 'choice',
          ))
          ->add('montantMonetaire', 'number', array(
              'required' => true,
              'attr' => array('class' => 'align-right')
          ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\SousTraitanceRepartition'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_soustraitancerepartition';
    }
}
