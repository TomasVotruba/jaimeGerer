<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class LigneAvoirType extends AbstractType
{
	protected $companyId;
	protected $type;

	public function __construct ($companyId = null, $type = null)
	{
		$this->companyId = $companyId;
		$this->type = $type;
	}


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
         ->add('nom', 'text', array(
      		'required' => true,
        	'label' => 'Nom',
      	))
        ->add('montant', 'number', array(
        		'required' => true,
        		'label' => 'Montant HT (€)',
        		'precision' => 2,
        		'attr' => array('class' => 'produit-montant')
     	   ))
     	   ->add('taxe', 'number', array(
     	   		'required' => false,
     	   		'precision' => 2,
     	   		'label' => 'TVA (€)',
     	   		'attr' => array('class' => 'produit-taxe')
     	   ))
     	   ->add('totalTTC', 'number', array(
     	   		'required' => false,
     	   		'label' => 'Total TTC (€)',
     	   		'precision' => 2,
     	   		'mapped' => false,
     	   		'attr' => array('class' => 'produit-total')
     	   ));

     	   if($this->type == "CLIENT"){

	     	   	$builder->add('compteComptable', 'entity', array(
	     	   			'class'=>'AppBundle:Compta\CompteComptable',
	     	   			'query_builder' => function (EntityRepository $er) {
	     	   				return $er->createQueryBuilder('c')
	     	   				->where('c.company = :company')
	     	   				->orderBy('c.nom', 'ASC')
	     	   				->andWhere('c.num LIKE :num')
	     	   				->setParameter('company', $this->companyId)
	     	   				->setParameter('num', '7%');
	     	   			},
	     	   			'required' => false,
	     	   			'label' => 'Compte comptable',
	     	   			'attr' => array('class' => 'select-compte-comptable')
	     	   	));
     	   } else {

     	   	$builder->add('compteComptable', 'entity', array(
            		'class'=>'AppBundle:Compta\CompteComptable',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('c')
            			->where('c.company = :company')
            			->orderBy('c.nom', 'ASC')
            			->andWhere('c.num LIKE :num')
            			->setParameter('company', $this->companyId)
            			->setParameter('num', '6%');
            		},
            		'required' => false,
            		'label' => 'Compte comptable',
            		'attr' => array('class' => 'select-compte-comptable')
            ));

     	  }



    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\LigneAvoir'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_lignedepense';
    }
}
