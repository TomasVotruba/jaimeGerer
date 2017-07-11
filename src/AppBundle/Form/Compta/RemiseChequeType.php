<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class RemiseChequeType extends AbstractType
{

	protected $companyId;
	protected $arr_cheque_pieces;

	public function __construct ($companyId = null, $arr_cheque_pieces = null)
	{
		$this->companyId = $companyId;
		$this->arr_cheque_pieces = $arr_cheque_pieces;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('total', 'number', array(
           			'required' => false,
           			'label' => 'Total (€)',
           			'precision' => 2,
           			'mapped' => false,
           			'read_only' => true,
           			'attr' => array('class' => 'remise-cheque-total-input')
           	))
            ->add('compteBancaire', 'entity', array(
        			'required' => true,
        			'class' => 'AppBundle:Compta\CompteBancaire',
        			'label' => 'Compte bancaire',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->andWhere('c.company = :company')
        				->setParameter('company', $this->companyId);
        			},
        			'attr' => array('class' => 'compte-select')
        	))
        	->add('date', 'date', array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput dateCreationInput'),
        			'required' => true,
        			'label' => 'Date de la remise de chèque',
        	))
        	->add('cheques', 'collection', array(
        			'type' => new ChequeType($this->arr_cheque_pieces),
        			'allow_add' => true,
        			'allow_delete' => true,
        			'by_reference' => false,
        			'label_attr' => array('class' => 'hidden'),
        	))
        	->add('submit','submit', array(
        			'label' => 'Enregistrer',
        			'attr' => array('class' => 'btn btn-success')
        	))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\RemiseCheque'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_remisecheque';
    }
}
