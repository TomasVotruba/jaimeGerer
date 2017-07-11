<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\File;

use Doctrine\ORM\EntityRepository;

class UploadReleveBancaireType extends AbstractType
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

    	$arr_formats = array(
    		'd/m/Y' => 'jour/mois/année (4 chiffres)',
    		'd/m/y'=> 'jour/mois/année (2 chiffres)',
    		'd-m-Y' => 'jour-mois-année (4 chiffres)',
    		'd-m-y'=> 'jour-mois-année (2 chiffres)',
    	);

       	$builder ->add('compteBancaire', 'entity', array(
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
        			->add('solde', 'number', array(
        					'label'	=> 'Solde du compte (€)',
        					'required' => true,
        					'attr' => array('class' => 'input-solde'),
        					'precision' => 2
        			))
        			->add('dateFormat', 'choice', array(
        					'label'	=> 'Format des dates du fichier importé',
        					'required' => true,
        					'choices' => $arr_formats
        			))
						->add('file', 'file', array(
			        'label' => 'Fichier',
			        'required' => true,
							'attr' => array('class' => 'file-upload'),
			        'constraints' => array(
	            	new File(array(
	                 'mimeTypes' => array(
	                     'text/csv',
											 'text/plain'
	                 ),
	                 'mimeTypesMessage' => 'Vous devez choisir un fichier .csv',
	         				)
	      				)
							)
						))
						->add('submit','submit', array(
							'label' => 'Suite',
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
