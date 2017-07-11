<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\FormInterface;

class OpportuniteType extends AbstractType
{
	protected $userGestionId;
	protected $companyId;

	public function __construct ($userGestionId = null, $companyId = null, Request $request = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
		$this->request = $request;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	//~ var_dump($this->request->request->get($this->getName())); exit;
    	//~ $posts = $this->request->request->get($this->getName());
        $builder
            ->add('nom', 'text', array(
        		'label' => 'Nom de l\'opportunité'
        	   ))
            ->add('montant','money', array(
            	'required' => true,
            	'label' => 'Montant HT',
            	'attr' => array('class' => 'opp-montant')
        	   ))
            ->add('probabilite', 'entity', array(
            		'class'=>'AppBundle:Settings',
            		'property' => 'valeur',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.parametre = :parametre')
            			->andWhere('s.company = :company')
            			->setParameter('parametre', 'OPPORTUNITE_STATUT')
            			->setParameter('company', $this->companyId);
            		},
            		'required' => true,
            		'label' => 'Probabilité',
            		'attr' => array('class' => 'opp-probabilite')
            ))
        	  ->add('type', 'choice', array(
        	  		'label' => 'Type',
        	  		'choices' => array(
        	  				'Existing Business' => 'Compte existant',
        	  				'New Business' => 'Nouveau compte',
        	  		),
        	  		'required' => true
        	  ))
		        ->add('compte_name', 'text', array(
		        		'required' => true,
		        		'mapped' => false,
		        		'label' => 'Organisation',
		        		'attr' => array('class' => 'typeahead-compte')
		        ))

		        ->add('compte', 'hidden', array(
		        		'required' => true,
		        		'attr' => array('class' => 'entity-compte'),
		        ))
		        ->add('contact_name', 'text', array(
		        		'required' => false,
		        		'mapped' => false,
		        		'label' => 'Contact',
		        		'attr' => array('class' => 'typeahead-contact')
		        ))

		        ->add('contact', 'hidden', array(
		        		'required' => false,
		        		'attr' => array('class' => 'entity-contact'),
		        ))
		        ->add('userGestion', 'entity', array(
		           			'class'=>'AppBundle:User',
		           			'required' => true,
		           			'label' => 'Gestionnaire de l\'opportunite',
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
           		->add('origine', 'entity', array(
           				'class'=>'AppBundle:Settings',
           				'property' => 'valeur',
           				'query_builder' => function (EntityRepository $er) {
           					return $er->createQueryBuilder('s')
           					->where('s.parametre = :parametre')
           					->andWhere('s.company = :company')
           					->orderBy('s.valeur', 'ASC')
           					->setParameter('parametre', 'ORIGINE')
           					->setParameter('company', $this->companyId);
           				},
           				'required' => false,
           				'label' => 'Origine'
           		))
           		->add('caAttendu','money', array(
           				'mapped' => false,
           				'label' => 'Chiffre d\'affaires attendu',
           				'read_only' => true,
           				'attr' => array('class' => 'opp-ca-attendu')
           		))
						->add('appelOffre', 'checkbox', array(
							'label' => 'Appel d\'offre',
							'required' => false,
						))
            ->add('priveOrPublic', 'choice', array(
              'label' => 'Privé ou public ?',
              'required' => true,
              'choices' => array(
                'PUBLIC' => 'Public',
                'PRIVE' => 'Privé'
                )
            ))
						->add('analytique', 'entity', array(
								'class'=> 'AppBundle\Entity\Settings',
								'required' => true,
								'label' => 'Analytique',
								'property' => 'valeur',
								'query_builder' => function(EntityRepository $er) {
									return $er->createQueryBuilder('s')
									->where('s.company = :company')
									->andWhere('s.module = :module')
									->andWhere('s.parametre = :parametre')
									->setParameter('company', $this->companyId)
									->setParameter('module', 'CRM')
									->setParameter('parametre', 'ANALYTIQUE');
								},
						))
            ->add('date', 'date', array(
              'years' => range(date('Y')-2, date('Y')+10),
              'days' => array(1),
              'required' => true,
              'input' => 'datetime',
              'widget' => 'choice',
            ))
        	 ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Opportunite'
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_opportunite';
    }
}
