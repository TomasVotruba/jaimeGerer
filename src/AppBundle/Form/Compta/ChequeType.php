<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\DataTransformer\ChequeToArrayTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class ChequeType extends AbstractType
{
    protected $arr_cheque_pieces;
    protected $companyId;
	protected $autre;

	public function __construct ( $arr_cheque_pieces = null, $companyId)
	{
      $this->arr_cheque_pieces = $arr_cheque_pieces;
		  $this->companyId = $companyId;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nomBanque', 'text', array(
          		'required' => false,
              	'label' => 'Banque'
            ))
            ->add('num', 'text', array(
          		'required' => false,
              	'label' => 'N° chèque'
            ))
            ->add('select', 'choice', array(
               	'choices' => $this->arr_cheque_pieces,
          		'required' => true,
               	'multiple' => true,
               	'expanded' => false,
               	'mapped' => false,
          		'attr' => array('class' => 'select-piece'),
               	'label' => 'Pièces',
            ))
            ->add('autre', 'checkbox', array(
                'label' => 'Autre',
                'attr' => array('class' => 'checkbox-autre'),
                'mapped' => false,
                'required' => false,
            ))
            ->add('libelle', 'text', array(
                'required' => false,
                'label' => 'Libellé',
                'mapped' => false,
                'attr' => array('class' => 'input-libelle'),
            ))
            ->add('emetteur', 'text', array(
                'required' => false,
                'label' => 'Emetteur',
                'mapped' => false,
                'attr' => array('class' => 'input-emetteur'),
            ))
            ->add('compteComptableTiers', 'entity', array(
                'class'=>'AppBundle:Compta\CompteComptable',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num  LIKE :num4 ')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num4', "4%")
                        ->orderBy('c.num');
                },
                'required' => false,
                'label' => 'Compte comptable du tiers',
                'attr' => array('class' => 'select-cc'),
                'mapped' => false,
            ))
            ->add('compteComptable', 'entity', array(
                'class'=>'AppBundle:Compta\CompteComptable',
                'property' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num NOT LIKE :num2 and c.num NOT LIKE :num401 and c.num NOT LIKE :num411 and c.num NOT LIKE :num6')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num2', "2%")
                        ->setParameter('num401', "401%")
                        ->setParameter('num411', "411%")
                        ->setParameter('num6', "6%")
                        ->orderBy('c.num');
                },
                'required' => false,
                'label' => 'Compte comptable',
                'attr' => array('class' => 'select-cc'),
                'mapped' => false,
            ))
           ->add('montant', 'number', array(
     	   		'required' => true,
     	   		'label' => 'Montant (€)',
     	   		'precision' => 2,
     	   		'mapped' => false,
     	   		'read_only' => true,
           	    'attr' => array('class' => 'input-montant')
      	   ));
        ;

       
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\Cheque'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_cheque';
    }
}
