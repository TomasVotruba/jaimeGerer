<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

class ProspectionImporterMappingType extends AbstractType
{
	
	protected $arr_headers;
	protected $filename;
	protected $form_index;
	
	public function __construct ($arr_headers = array(), $filename = array(), $form_index = 0)
	{
		$this->arr_headers = $arr_headers;
		$this->filename = $filename;
		$this->form_index = $form_index;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {    	
		$compte = 'B';
		$adresse = 'C';
		$telephone_fixe = 'D';
		$activite = 'E';
		$nom = 'F';
		$prenom = 'G';
		$type = 'H';
		 
		foreach( $this->filename as $k=>$v )
		{
        $builder
            ->add('compte'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Organisation',
				'choices' => $this->arr_headers[$k],
				'data' => $compte
        	))
            ->add('nom'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Nom',
				'choices' => $this->arr_headers[$k],
				'data' => $nom
        	))
            ->add('prenom'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Prénom',
				'choices' => $this->arr_headers[$k],
				'data' => $prenom
        	))
            ->add('adresse'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Adresse',
				'choices' => $this->arr_headers[$k],
				'data' => $adresse
        	))
            ->add('telephoneFixe'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Téléphone fixe',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_fixe
        	))
            ->add('activite'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Activité',
				'choices' => $this->arr_headers[$k],
				'data' => $activite
        	))
            ->add('type'.$k, 'choice', array(
        		'required' => true,
            	'label' => 'Type',
				'choices' => $this->arr_headers[$k],
				'data' => $type
        	))
			->add('filepath'.$k, 'hidden', array(
					'label' => $v['nom_original'],
					'data' => $k,
			));
		}
			$builder
			->add('submit','submit', array(
					'label' => 'Importer les contacts',
					'attr' => array('class' => 'btn btn-success')
			))
			;

    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_importprospectionmapping';
    }
}
