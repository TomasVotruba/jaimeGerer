<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use AppBundle\Form\CRM\ProduitType;

class DevisType extends AbstractType
{
	protected $userGestionId;
	protected $companyId;

	public function __construct ($userGestionId = null, $companyId = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objet', 'text', array(
        		'required' => true,
            	'label' => 'Objet'
        	))
            ->add('dateValidite', 'date', array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput'),
        			'required' => true,
        			'label' => 'Date de validité'
        	))
             ->add('adresse', 'text', array(
        		'required' => true,
            	'label' => 'Adresse',
             	'attr' => array('class' => 'input-adresse'),
        	))
            ->add('codePostal', 'text', array(
        		'required' => true,
            	'label' => 'Code postal',
            	'attr' => array('class' => 'input-codepostal'),
        	))
            ->add('ville', 'text', array(
        		'required' => true,
            	'label' => 'Ville',
            	'attr' => array('class' => 'input-ville'),
        	))
    			->add('etat', 'choice', array(
    				'label' => 'Etat du devis',
    				'choices' => array(
    						'DRAFT' => 'Brouillon',
    						'SENT' => 'Envoyé',
    						'WON' => 'Gagné',
    						'LOST' => 'Perdu'
    					),
    			))
            ->add('region', 'text', array(
        		'required' => true,
            	'label' => 'Région',
            	'attr' => array('class' => 'input-region'),
        	))
            ->add('pays', 'text', array(
        		'required' => true,
            	'label' => 'Pays',
            	'attr' => array('class' => 'input-pays'),
        	))
            ->add('description', 'textarea', array(
        		'required' => false,
            	'label' => 'Commentaire'
        	))
            ->add('remise', 'number', array(
        		'required' => false,
            	'label' => 'Remise',
            	'precision' => 2,
            		'attr' => array('class' => 'devis-remise')
        	))
        	->add('cgv', 'textarea', array(
        			'required' => false,
        			'label' => 'Conditions d\'utilisation'
        	))
            ->add('userGestion', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label' => 'Gestionnaire du devis',
           			'query_builder' => function (EntityRepository $er) {
           				return $er->createQueryBuilder('u')
           				->where('u.company = :company')
           				->andWhere('u.enabled = :enabled')
           				->orWhere('u.id = :id')
           				->orderBy('u.firstname', 'ASC')
           				->setParameter('company', $this->companyId)
           				->setParameter('enabled', 1)
           				->setParameter('id', $this->userGestionId);
           			},
           	))
             ->add('compte_name', 'text', array(
             		'required' => true,
             		'mapped' => false,
             		'label' => 'Organisation',
             		'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
             ))
             ->add('compte', 'hidden', array(
             		'required' => true,
             		'attr' => array('class' => 'entity-compte')
             ))
           	->add('contact_name', 'text', array(
           			'required' => true,
           			'mapped' => false,
           			'label' => 'Contact',
           			'attr' => array('class' => 'typeahead-contact', 'autocomplete' => 'off')
           	))
           	->add('contact', 'hidden', array(
           			'required' => true,
           			'attr' => array('class' => 'entity-contact')
           	))
           	->add('produits', 'collection', array(
           			'type' => new ProduitType($this->companyId),
           			'allow_add' => true,
           			'allow_delete' => true,
           			'by_reference' => false,
           			'label_attr' => array('class' => 'hidden')
           	))
           	->add('sousTotal', 'number', array(
           			'required' => false,
           			'label' => 'Sous total',
           			'precision' => 2,
           			'mapped' => false,
           			'read_only' => true,
           			'attr' => array('class' => 'devis-sous-total')
           	))
           	->add('taxe', 'number', array(
           			'required' => false,
           			'precision' => 2,
           			'label_attr' => array('class' => 'hidden'),
           			'attr' => array('class' => 'devis-taxe'),
           			'read_only' => true
           	))
           	->add('taxePercent', 'percent', array(
           			'required' => false,
           			'precision' => 2,
           			'label' => 'TVA',
           			'attr' => array('class' => 'devis-taxe-percent'),
           	))
           	->add('totalHT', 'number', array(
           			'required' => false,
           			'label' => 'Total HT',
           			'precision' => 2,
           			'mapped' => false,
           			'read_only' => true,
           			'attr' => array('class' => 'devis-total-ht')
           	))
           	->add('totalTTC', 'number', array(
           			'required' => false,
           			'label' => 'Total TTC',
           			'precision' => 2,
           			'mapped' => false,
           			'read_only' => true,
           			'attr' => array('class' => 'devis-total-ttc')
           	))
        ;

      }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\DocumentPrix'
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_devis';
    }

}
