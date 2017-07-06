<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class OperationDiverseType extends AbstractType
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
        			'required' => false,
        			'class' => 'AppBundle:Compta\CompteComptable',
        			'label' => 'Compte comptable',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->andWhere('c.company = :company')
        				->setParameter('company', $this->companyId)
        				->orderBy('c.num', 'ASC');
        			}
        	));
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\OperationDiverse'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_operationdiverse';
    }
}
