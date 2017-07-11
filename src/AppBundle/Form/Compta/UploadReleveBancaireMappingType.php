<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

class UploadReleveBancaireMappingType extends AbstractType
{
	
	protected $arr_headers;
	
	public function __construct ($arr_headers = null)
	{
		$this->arr_headers = $arr_headers;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$libelle = "";
    	$date = "";
    	$credit = "";
    	$debit = "";
    	foreach($this->arr_headers as $header){
    		
    		if(strstr($header, 'Libellé')){
    			$libelle = $header;
    		} 
    		if(strstr($header, 'Date') && !strstr($header, 'valeur')){
    			$date = $header;
    		} 
    		if(strstr($header, 'Crédit')){
    			$credit = $header;
    		}
    		if(strstr($header, 'Débit')){
    			$debit = $header;
    		}
    	}

       		$builder
				->add('date', 'choice', array(
						'required' => true,
						'label' => 'Date',
						'choices' => $this->arr_headers,
						'data' => $date
				))
				->add('libelle', 'choice', array(
						'required' => true,
						'label' => 'Libellé',
						'choices' => $this->arr_headers,
						'data' => $libelle
				))
				->add('debit', 'choice', array(
						'required' => true,
						'label' => 'Débit',
						'choices' => $this->arr_headers,
						'data' => $debit
				))
				->add('credit', 'choice', array(
						'required' => true,
						'label' => 'Crédit',
						'choices' => $this->arr_headers,
						'data' => $credit
				))
				->add('submit','submit', array(
						'label' => 'Importer',
						'attr' => array('class' => 'btn btn-success')
				));

    }
    

    
    
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_uploadrelevebancaire';
    }
}
