<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class LigneDepenseType extends AbstractType
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
            	'label' => 'Libellé',
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
     	   ))
            ->add('compteComptable', 'entity', array(
            		'class'=>'AppBundle:Compta\CompteComptable',
            		'property' => 'nom',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('c')
            			->where('c.company = :company')
            			->andWhere('c.num LIKE :num OR c.num LIKE :compteAttente OR c.num LIKE :ordi')
            			->setParameter('company', $this->companyId)
            			->setParameter('num', '6%')
            			->setParameter('compteAttente', '471')
                        ->setParameter('ordi', '21831000')
            			->orderBy('c.nom');
            		},
            		'required' => false,
            		'label' => 'Compte comptable',
            		'attr' => array('class' => 'produit-type')
            ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\LigneDepense'
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
