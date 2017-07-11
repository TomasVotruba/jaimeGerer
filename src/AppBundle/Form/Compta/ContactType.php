<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use libphonenumber\PhoneNumberFormat;

class ContactType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $formAction;
    protected $type;
	
	public function __construct ($userGestionId = null, $companyId = null, $formAction = null, $type = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
        $this->formAction = $formAction;
		$this->type = $type;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
        		'label' => 'Nom de l\'organisation'
        		//~ 'attr'   =>  array(
					//~ 'data-validation-engine' => "validate[required,custom[email]]",
					//~ 'data-errormessage-value-missing' => "Email is required!" ,
					//~ 'data-errormessage-custom-error' => "Let me give you a hint: someone@nowhere.com" ,
					//~ 'data-errormessage' => "This is the fall-back error message."
					//~ 'class' => 'form-control validate[required]'
				//~ )
        	))
            ->add('telephone','text', array(
            	'required' => false,
            	//'default_region' => 'FR',
            	//'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone'
        	))
            ->add('fax', 'text', array(
            	'required' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('url', 'url', array(
            	'required' => false,
            	'label' => 'URL du site web',
                'attr'   =>  array(
                    'class' => "urlId",
                    'value' => 'http://'
                )
        	))
            ->add('adresse', 'text', array(
            	'required' => true,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', 'text', array(
            	'required' => true,
            	'label' => 'Code postal'
        	))
            ->add('ville', 'text', array(
            	'required' => true,
            	'label' => 'Ville'
        	))
            ->add('region', 'text', array(
            	'required' => true,
            	'label' => 'Région'
        	))
            ->add('pays', 'text', array(
            	'required' => true,
            	'label' => 'Pays'        	
            ))
            ->add('description', 'textarea', array(
            	'required' => false,
            	'label' => 'Description'
        		//~ 'attr'   =>  array(
					//~ 'data-validation-engine' => "validate[required]",
					//~ 'data-errormessage-value-missing' => "Email is required!" ,
					//~ 'data-errormessage-custom-error' => "Let me give you a hint: someone@nowhere.com" ,
					//~ 'data-errormessage' => "This is the fall-back error message."
					//~ 'class' => 'form-control validate[required]'
				//~ )
        	))
            ->add('compteParent', 'shtumi_ajax_autocomplete', array(
            		'entity_alias'=>'comptes',
            		'required' => false,
            		'label' => 'Organisation parente'
           	))
           	->add('userGestion', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label' => 'Gestionnaire de l\'organisation',
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
            ->add('addressPicker', 'text', array(
                'label'     => 'Veuillez renseigner l\'adresse ici',
                'mapped'    => false,
                'required'  => false
            ));

            if($this->type == "CLIENT"){
                $builder->add('compteComptableClient', 'entity', array(
                    'class'=>'AppBundle:Compta\CompteComptable',
                    'property' => 'nom',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num LIKE :num')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num', '411%')
                        ->orderBy('c.nom');
                    },
                    'required' => false,
                    'label' => 'Compte comptable client',
                    'attr' => array('class' => 'select-compte-comptable')
              ));
            } else {
                $builder->add('compteComptableFournisseur', 'entity', array(
                    'class'=>'AppBundle:Compta\CompteComptable',
                    'property' => 'nom',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                        ->where('c.company = :company')
                        ->andWhere('c.num LIKE :num')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('num', '401%')
                        ->orderBy('c.nom');
                    },
                    'required' => false,
                    'label' => 'Compte comptable fournisseur',
                     'attr' => array('class' => 'select-compte-comptable')
                ));
            }
            $builder->add('secteurActivite', 'entity', array(
                'class'=>'AppBundle:Settings',
                'property' => 'valeur',
                'required' => false,
                'label' => 'Secteur d\'activité',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SECTEUR')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->companyId)
                        ->orderBy('s.valeur');
                }
            ));
            
           	if( $this->formAction )
				$builder->setAction($this->formAction);
	
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Compte'
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_compte';
    }
}
