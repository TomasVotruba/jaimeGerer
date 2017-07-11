<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ProduitType extends AbstractType
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
            ->add('nom', 'text', array(
        		'required' => true,
            	'label' => 'Nom',
        	))
            ->add('description', 'textarea', array(
        			'required' => 'true',
            	'label' => 'Description'
        	))
            ->add('tarifUnitaire', 'number', array(
            		'required' => true,
            		'label' => 'Tarif unitaire (€)',
            		'precision' => 2,
            		'attr' => array('class' => 'produit-tarif')
     	   ))
            ->add('quantite', 'number', array(
            		'required' => true,
            		'label' => 'Quantité',
            		'attr' => array('class' => 'produit-quantite')
            ))
             ->add('montant', 'number', array(
            		'required' => true,
            		'label' => 'Montant (€)',
            		'precision' => 2,
             		'mapped' => false,
             		'disabled' => true,
             		'attr' => array('class' => 'produit-montant')
     	   ))
            ->add('remise', 'number', array(
            		'required' => false,
            		'label' => 'Remise (€)',
            		'precision' => 2,
            		'attr' => array('class' => 'produit-remise')
     	   ))
           ->add('type', 'entity', array(
            		'class'=>'AppBundle:Settings',
            		'property' => 'valeur',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.parametre = :parametre')
            			->andWhere('s.company = :company')
            			->andWhere('s.module = :module')
            			->setParameter('parametre', 'TYPE_PRODUIT')
            			->setParameter('company', $this->companyId)
            			->setParameter('module', 'CRM');
            		},
            		'required' => false,
            		'label' => 'Type',
            		'attr' => array('class' => 'produit-type')
            ))
            ->add('total', 'number', array(
            		'required' => true,
            		'label' => 'Total (€)',
            		'precision' => 2,
            		'mapped' => false,
            		'disabled' => true,
            		'attr' => array('class' => 'produit-total')
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Produit'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_produit';
    }
}
