<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class OpportuniteWonPlanPaiementType extends AbstractType
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
        $actionCommerciale = $builder->getData();

        $builder
            ->add('type', 'choice', array(
                'mapped' => false,
                'choices' => array(
                    'COMMANDE' => '100 % à la commande',
                    'FIN' => '100 % à la fin du projet',
                    'CUSTOM' => 'Personnalisé'
                ),
                'multiple' => false,
                'expanded' => true,
                'attr' => array('class' => 'type-select'),
                'data' => $actionCommerciale->getModePaiement()
            ))
           	->add('planPaiements', 'collection', array(
       			'type' => new PlanPaiementType(),
       			'allow_add' => true,
       			'allow_delete' => true,
       			'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
                'data' => $actionCommerciale->getPlansPaiementsCustom()
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
        return 'appbundle_crm_opportunite_won_plan_paiement';
    }

}
