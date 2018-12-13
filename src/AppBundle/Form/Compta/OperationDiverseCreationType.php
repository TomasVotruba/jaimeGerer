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
            ->add('debit', 'number', array(
                'mapped' => false
            ))
            ->add('credit', 'number', array(
                'mapped' => false
            ))
            ->add('compteComptableDebit', 'entity', array(
    			'required' => false,
    			'class' => 'AppBundle:Compta\CompteComptable',
    			'label' => 'Compte comptable',
                'mapped' => false,
    			'query_builder' => function (EntityRepository $er) {
    				return $er->createQueryBuilder('c')
    				->andWhere('c.company = :company')
    				->setParameter('company', $this->companyId)
    				->orderBy('c.num', 'ASC');
    			}
        	))
            ->add('compteComptableCredit', 'entity', array(
                'required' => false,
                'class' => 'AppBundle:Compta\CompteComptable',
                'label' => 'Compte comptable',
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ->andWhere('c.company = :company')
                    ->setParameter('company', $this->companyId)
                    ->orderBy('c.num', 'ASC');
                }
            ))
            ->add('commentaire', 'text', array(
                'required' => false,
                'label' => 'Commentaire'
            ))
            ->add('pieceType', 'choice', array(
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => array(
                    'NONE' => 'Aucune pièce',
                    'FACTURE' => 'Facture',
                    'DEPENSE' => 'Dépense',
                    'AVOIR-CLIENT' => 'Avoir client',
                    'AVOIR-FOURNISSEUR' => 'Avoir fournisseur'
                ),
                'mapped' => false,
                'label' => 'Type de pièce',
                'attr' => array('class' => 'piece-type')
            ))
            ->add('piece', 'text', array(
                'required' => false,
                'label' => 'Pièce (numéro)',
                'mapped' => false,
                'attr' => array('class' => 'typeahead-piece', 'autocomplete' => 'off'),
                'disabled' => true
            ))
            ->add('facture', 'entity', array(
                'attr' => array('class' => 'entity-facture hidden'),
                'class' => 'AppBundle:CRM\DocumentPrix',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
            ))
            ->add('depense', 'entity', array(
                'attr' => array('class' => 'entity-depense hidden'),
                'class' => 'AppBundle:Compta\Depense',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
            ))
            ->add('avoir', 'entity', array(
                'attr' => array('class' => 'entity-avoir hidden'),
                'class' => 'AppBundle:Compta\Avoir',
                'required' => false,
                'label_attr'=> array('class' => 'hidden')
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
