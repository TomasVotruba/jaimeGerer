<?php

namespace AppBundle\Form\NDF;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RecuType extends AbstractType
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
            ->add('date', 'date', array('widget' => 'single_text',
              'input' => 'datetime',
              'format' => 'dd/MM/yyyy',
              'attr' => array('class' => 'dateInput'),
              'required' => true,
              'label' => 'Date du reçu'
            ))
            ->add('fournisseur', 'text', array(
              'label' => 'Fournisseur',
              'required' => true
            ))
            ->add('montantHT', 'number', array(
               'required' => true,
               'label' => 'Montant HT (€)',
               'precision' => 2,
               'attr' => array('class' => 'montant-ht')
             ))
           ->add('tva', 'number', array(
              'required' => true,
              'label' => 'TVA (€)',
              'precision' => 2,
              'attr' => array('class' => 'montant-tva')
            ))
            ->add('montantTTC', 'number', array(
               'required' => true,
               'label' => 'Montant TTC (€)',
               'precision' => 2,
               'attr' => array('class' => 'montant-ttc')
             ))
            ->add('analytique', 'entity', array(
           			'class'=>'AppBundle:Settings',
           			'required' => true,
           			'label' => 'Analytique',
           			'query_builder' => function (EntityRepository $er) {
           				return $er->createQueryBuilder('s')
           				->where('s.company = :company')
           				->andWhere('s.parametre = :parametre')
           				->setParameter('company', $this->companyId)
           				->setParameter('parametre', 'analytique')
                  ->orderBy('s.valeur', 'ASC');
           			},
           	))
            ->add('compteComptable', 'entity', array(
                'class'=>'AppBundle:Compta\CompteComptable',
                'required' => true,
                'label' => 'Compte comptable',
                'property' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('c')
                  ->where('c.company = :company')
                  ->andWhere('c.num LIKE :num')
                  ->setParameter('company', $this->companyId)
                  ->setParameter('num', '6%')
                  ->orderBy('c.nom', 'ASC');
                },
            ))
            ->add('submit', 'submit', array(
              'label' => 'Enregistrer',
            ));
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\NDF\Recu'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_ndf_recu';
    }
}
