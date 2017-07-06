<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class CompteBancaireType extends AbstractType
{
	protected $companyId;
	protected $solde;
	protected $soldeDebutAnnee;
	
	public function __construct ($companyId = null, $solde = null, $soldeDebutAnnee = null)
	{
		$this->companyId = $companyId;
		$this->solde = $solde;
		$this->soldeDebutAnnee = $soldeDebutAnnee;
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
            	'label' => 'Nom du compte'	
       	 	))
       	 	->add('num', 'text', array(
       	 			'required' => true,
       	 			'label' => 'Numéro de compte'
       	 	))
            ->add('bic', 'text', array(
        		'required' => true,
            	'label' => 'BIC'
        	))
            ->add('iban', 'text', array(
        		'required' => true,
            	'label' => 'IBAN'
        	))
            ->add('domiciliation', 'text', array(
        		'required' => true,
            	'label' => 'Domiciliation'
        	))
            ->add('solde', 'number', array(
        		'required' => true,
            	'label' => 'Solde actuel du compte (€)',
            	'mapped' => false,
            	'data' => $this->solde
        	))
        	->add('soldeDebutAnnee', 'number', array(
        			'required' => true,
        			'label' => 'Solde du compte (€) au 1er janvier de l\'année en cours',
        			'mapped' => false,
        			'data' => $this->soldeDebutAnnee
        	))
            ->add('compteComptable', 'entity', array(
        			'required' => false,
        			'class' => 'AppBundle:Compta\CompteComptable',
        			'label' => 'Compte comptable',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->where('c.company = :company')
        				->andWhere('c.num LIKE :num')
        				->setParameter('company', $this->companyId)
        				->setParameter('num', "512%");
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
            'data_class' => 'AppBundle\Entity\Compta\CompteBancaire'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_comptebancaire';
    }
}
