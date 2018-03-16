<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class OperationDiverseCreationType extends AbstractType
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
                    'label' => 'Date de l\'opération'
             ))
            ->add('libelle', 'text', array(
                'required' => true,
                'label' => 'Libellé'
            ))
            ->add('debit')
            ->add('credit')
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
        	))
            ->add('submit','submit', array(
                    'label' => 'Enregistrer',
                    'attr' => array('class' => 'btn btn-success')
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
