<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use AppBundle\Form\CRM\OpportuniteRepartitionType;
use AppBundle\Form\CRM\OpportuniteSousTraitanceType;

class OpportuniteWonRepartitionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           	->add('opportuniteRepartitions', 'collection', array(
           			'type' => new OpportuniteRepartitionType(),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
								'label_attr' => array('class' => 'hidden')
           	))
            ->add('sousTraitance', 'checkbox', array(
              'mapped' => false,
              'label' => ' ',
              'required' => false,
              'attr' => array(
                'data-toggle' => 'toggle',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-on' => 'Oui',
                'data-off' => 'Non'
              ),
            ))
            ->add('submit', 'submit', array(
                'label' => 'Valider',
                'attr' => array(
                  'class' => 'btn btn-success',
                  'disabled' => true,

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
            'data_class' => 'AppBundle\Entity\CRM\Opportunite'
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_opportunite_won_repartition';
    }

}
