<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MouvementBancaireType extends AbstractType
{
	protected $mouvementBancaire;
	
	public function __construct($mouvementBancaire)
	{
		$this->mouvementBancaire = $mouvementBancaire;
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
             		'label' => 'Date',
           			'data' => $this->mouvementBancaire->getDate()
             ))
             ->add('montant', 'number', array(
            		'required' => true,
            		'label' => 'Montant (€)',
            		'precision' => 2,
            		'attr' => array('class' => 'montant')
     	   ))
            ->add('libelle', 'text', array(
             		'required' => true,
            		'label' => 'Libellé',
            		'data' => $this->mouvementBancaire->getLibelle()
             ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\MouvementBancaire'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_mouvementbancaire';
    }
}
