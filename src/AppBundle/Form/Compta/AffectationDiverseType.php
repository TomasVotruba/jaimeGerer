<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class AffectationDiverseType extends AbstractType
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
        		'label' => 'Libellé',
            	'required' => true
       	 	))
            ->add('recurrent', 'choice', array(
            	'choices' => array(1 => 'Récurrent', 0 => 'Non récurrent'),
            	'label' => 'Récurrent',
            	'expanded' => true,
            	'required' => true
            ))
            ->add('filtre_comptes', 'choice', array(
            		'choices' => array(
            			471 => 'Je ne sais pas',
            		),
            		'label' => 'Type d\'affectation',
            		'expanded' => true,
            		'required' => false,
            		'multiple' => false,
            		'mapped' => false,
            		'attr' => array('class' => 'radio-filtre-comptes'),
            		'empty_value' => 'Je sais !'
            ))
            ->add('type', 'hidden');

			if($this->type == "ACHAT"){
				$builder->add('compteComptable', 'entity', array(
		        			'required' => false,
		        			'class' => 'AppBundle:Compta\CompteComptable',
		        			'label' => 'Compte comptable',
		            		'attr' => array('class' => 'select-compte-comptable'),
		        			'query_builder' => function (EntityRepository $er) {
		        				return $er->createQueryBuilder('c')
		        				->where('c.company = :company')
		        				->andWhere('c.num NOT LIKE :num2 and c.num NOT LIKE :num401 and c.num NOT LIKE :num411 and c.num NOT LIKE :num7')
		        				->setParameter('company', $this->companyId)
		        				->setParameter('num2', "2%")
		        				->setParameter('num401', "401%")
		        				->setParameter('num411', "411%")
		        				->setParameter('num7', "7%")
		        				->orderBy('c.num');
		        			}
				));
    		} else {
    			$builder->add('compteComptable', 'entity', array(
    					'required' => false,
    					'class' => 'AppBundle:Compta\CompteComptable',
    					'label' => 'Compte comptable',
    					'attr' => array('class' => 'select-compte-comptable'),
    					'query_builder' => function (EntityRepository $er) {
    						return $er->createQueryBuilder('c')
    						->where('c.company = :company')
    						->andWhere('c.num NOT LIKE :num2 and c.num NOT LIKE :num401 and c.num NOT LIKE :num411')
    						->setParameter('company', $this->companyId)
    						->setParameter('num2', "2%")
    						->setParameter('num401', "401%")
    						->setParameter('num411', "411%")
    						->orderBy('c.num');
    					}
    			));
    		}

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\AffectationDiverse'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_affectationdiverse';
    }
}
