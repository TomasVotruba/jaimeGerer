<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\DataTransformer\ChequeToArrayTransformer;

class ChequeType extends AbstractType
{
	protected $arr_cheque_pieces;

	public function __construct ( $arr_cheque_pieces = null)
	{
		$this->arr_cheque_pieces = $arr_cheque_pieces;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('nomBanque', 'text', array(
      			'required' => false,
          	'label' => 'Banque'
        	))
          ->add('num', 'text', array(
      			'required' => false,
          	'label' => 'N° chèque'
        	))
           ->add('select', 'choice', array(
           		'choices' => $this->arr_cheque_pieces,
      				'required' => true,
           		'multiple' => true,
           		'expanded' => false,
           		'mapped' => false,
      				'attr' => array('class' => 'select-piece'),
           		'label' => 'Pièces',
        	))
           ->add('montant', 'number', array(
     	   		'required' => false,
     	   		'label' => 'Montant (€)',
     	   		'precision' => 2,
     	   		'mapped' => false,
     	   		'read_only' => true,
           	'attr' => array('class' => 'input-montant')
      	   ))
        ;

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\Cheque'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_cheque';
    }
}
