<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class OpportuniteWonBonCommandeType extends AbstractType
{

    public function __construct ()
    {
      
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           	->add('bonsCommande', 'collection', array(
           			'type' => new BonCommandeType(),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
								'label_attr' => array('class' => 'hidden')
           	));
            
        $builder
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
        return 'appbundle_crm_opportunite_won_bon_commande';
    }

}
