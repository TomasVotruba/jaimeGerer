<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class LigneJournalCorrectionType extends AbstractType
{

    protected $companyId;

    public function __construct ($companyId = null)
    {
      $this->companyId = $companyId;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('compteComptable', 'entity', array(
            'class'=>'AppBundle:Compta\CompteComptable',
            'query_builder' => function (EntityRepository $er) {
              return $er->createQueryBuilder('c')
              ->where('c.company = :company')
              ->setParameter('company', $this->companyId)
              ->orderBy('c.num');
            },
            'required' => true,
            'label' => 'Compte comptable',
            'attr' => array('class' => 'select-compte-comptable'),
            'mapped' => false
        ))
        ->add('compteNotInList', 'checkbox', array(
          'label' => 'Le compte comptable n\'existe pas encore',
          'attr' => array('class' => 'checkbox-not-in-list'),
          'mapped' => false,
          'required'=> false
        ))
        ->add('comptePrefixe', 'choice', array(
          'choices' => array(
            '401' => '401',
            '411' => '411',
          ),
          'mapped' => false,
          'required'=> false
        ))
        ->add('compteNum', 'text', array(
          'attr' => array('class' => 'input-compte-num'),
          'mapped' => false,
          'required'=> false
        ))
        ->add('compteNom', 'text', array(
          'attr' => array('class' => 'input-compte-nom'),
          'label' => 'Nom du compte',
          'mapped' => false,
          'required'=> false
        ))
        ->add('corriger', 'submit', array(
          'label' => 'Corriger directement',
          'attr' => array('class' => 'btn btn-success submit-button')
        ))
        ->add('creerOD', 'submit', array(
          'label' => 'Corriger via une OD',
          'attr' => array('class' => 'btn btn-info btn-xs submit-button')
        ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\JournalBanque'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_comptecomptablecorrection';
    }
}
