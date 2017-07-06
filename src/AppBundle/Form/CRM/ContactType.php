<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use libphonenumber\PhoneNumberFormat;


class ContactType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $compte;
	
	public function __construct ($userGestionId = null, $companyId = null, $compte = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
		$this->compte = $compte;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom', 'text', array(
        		'required' => true,
            	'label' => 'Prénom'
        	))
            ->add('nom', 'text', array(
        		'required' => true,
            	'label' => 'Nom'
        	))
            ->add('telephoneFixe', 'text', array(
            	'required' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	))
            ->add('telephonePortable','text', array(
            	'required' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Tél. portable pro'
        	))
    			->add('telephoneAutres','text', array(
    				'required' => false,
    				//  	'default_region' => 'FR',
    				//  	'format' => PhoneNumberFormat::INTERNATIONAL,
    				'label' => 'Tél. (autre)'
    			))
                ->add('email', 'email', array(
            		'required' => false,
                	'label' => 'Email'
            	))
    			->add('email2', 'email', array(
    				'required' => false,
    				'label' => 'Email 2'
    			))
            ->add('adresse', 'text', array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', 'text', array(
        		'required' => false,
            	'label' => 'Code postal'
        	))
            ->add('ville', 'text', array(
        		'required' => false,
            	'label' => 'Ville'
        	))
            ->add('region', 'text', array(
        		'required' => false,
            	'label' => 'Région'
        	))
            ->add('pays', 'text', array(
        		'required' => false,
            	'label' => 'Pays'
        	))
            ->add('description', 'textarea', array(
        		'required' => false,
            	'label' => 'Description'
        	))
            ->add('titre', 'text', array(
        		'required' => false,
            	'label' => 'Titre'
        	))
            ->add('fax', 'text', array(
            	'required' => false,
           // 	'default_region' => 'FR',
           // 	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('carteVoeux', 'checkbox', array(
        		'required' => false,
  				'label' => 'Carte de voeux'
        	))
            ->add('newsletter', 'checkbox', array(
        		'required' => false,
  				'label' => 'Newsletter'
        	))
			
			->add('compte_name', 'text', array(
				'required' => true,
				'mapped' => false,
				'label' => 'Organisation',
				'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off' )
			))

			->add('compte', 'hidden', array(
				'required' => true,
				'attr' => array('class' => 'entity-compte'),
			))
            
			->add('reseau', 'entity', array(
            		'class'=>'AppBundle:Settings',
            		'property' => 'valeur',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.parametre = :parametre')
            			->andWhere('s.company = :company')
            			->setParameter('parametre', 'RESEAU')
            			->setParameter('company', $this->companyId);
            		},
            		'required' => false,
            		'label' => 'Réseau'
            ))
             ->add('origine', 'entity', array(
            		'class'=>'AppBundle:Settings',
            		'property' => 'valeur',
            		'query_builder' => function (EntityRepository $er) {
            			return $er->createQueryBuilder('s')
            			->where('s.parametre = :parametre')
            			->andWhere('s.company = :company')
            			->setParameter('parametre', 'ORIGINE')
            			->setParameter('company', $this->companyId);
            		},
            		'required' => false,
            		'label' => 'Origine'
            ))
            ->add('userGestion', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label' => 'Gestionnaire du contact',
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
           				'label' => 'Veuillez saisir l\'adresse ici',
           				'mapped' => false,
           				'required' => false
           	));
                         
             $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
             $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Contact'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_contact';
    }
    
    public function onPreSetData(FormEvent $event){
    	$contact = $event->getData();
    	$form = $event->getForm();
    	
    	$arr_themes = array();
    	$arr_services = array();
    	$arr_types = null;
        $arr_secteurs = array();
    	foreach($contact->getSettings() as $setting){
    		if($setting->getModule() == 'CRM' && $setting->getParametre() == 'THEME_INTERET'){
    			$arr_themes[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SERVICE_INTERET'){
    			$arr_services[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'TYPE'){
    			$arr_types[] = $setting;
    		}
            else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SECTEUR'){
                $arr_secteurs[] = $setting;
            }
    	}

    	$form->add('themes_interet', 'entity', array(
             		'class'=>'AppBundle:Settings',
             		'property' => 'valeur',
             		'query_builder' => function (EntityRepository $er) {
             			return $er->createQueryBuilder('s')
             			->where('s.parametre = :parametre')
             			->andWhere('s.module = :module')
             			->andWhere('s.company = :company')
             			->setParameter('parametre', 'THEME_INTERET')
             			->setParameter('module', 'CRM')
             			->setParameter('company', $this->companyId)
             			->orderBy('s.valeur');
             		},
             		'required' => false,
             		'multiple' => true,
             		'label' => 'Thèmes d\'intérêt',
             		'mapped' => false,
             		'data' => $arr_themes
             ));
    	
        $form->add('services_interet', 'entity', array(
                    'class'=>'AppBundle:Settings',
                    'property' => 'valeur',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                        ->where('s.parametre = :parametre')
                        ->andWhere('s.company = :company')
                        ->andWhere('s.module = :module')
                        ->setParameter('parametre', 'SERVICE_INTERET')
                        ->setParameter('module', 'CRM')
                        ->setParameter('company', $this->companyId)
                        ->orderBy('s.valeur');
                    },
                    'required' => false,
                    'multiple' => true,
                    'label' => 'Services d\'intérêt',
                     'empty_data'  => null,
                     'mapped' => false,
                     'data' => $arr_services
            )
        );

        $form->add('secteur', 'entity', array(
            'class'=>'AppBundle:Settings',
            'property' => 'valeur',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->where('s.parametre = :parametre')
                    ->andWhere('s.company = :company')
                    ->andWhere('s.module = :module')
                    ->setParameter('parametre', 'SECTEUR')
                    ->setParameter('module', 'CRM')
                    ->setParameter('company', $this->companyId)
                    ->orderBy('s.valeur');
            },
            'required' => false,
            'multiple' => true,
            'label' => 'Secteur d\'activité',
            'empty_data'  => null,
            'mapped' => false,
            'data' => $arr_secteurs
        ));

    	 $form->add('types', 'entity', array(
    	 		'class'=>'AppBundle:Settings',
    	 		'property' => 'valeur',
    	 		'query_builder' => function (EntityRepository $er) {
    	 			return $er->createQueryBuilder('s')
    	 			->where('s.parametre = :parametre')
    	 			->andWhere('s.module = :module')
    	 			->andWhere('s.company = :company')
    	 			->setParameter('parametre', 'TYPE')
    	 			->setParameter('module', 'CRM')
    	 			->setParameter('company', $this->companyId)
    	 			->orderBy('s.valeur');
    	 		},
    	 		'required' => false,
    	 		'multiple' => true,
    	 		'label' => 'Type de relation commerciale',
    	 		'empty_data'  => null,
    	 		'mapped' => false,
    	 		'data' => $arr_types
    	 ));
    }
    
    public function onPostSubmit(FormEvent $event){
    	
    	$contact = $event->getData();
    	$form = $event->getForm();
    	
    	$contact->removeSettings();
    	//~ var_dump($form->get('themes_interet')->getData());
    	//~ var_dump($form->get('services_interet')->getData());
    	//~ var_dump($form->get('types')->getData());
    	foreach($form->get('themes_interet')->getData() as $theme){
			//~ var_dump($theme);
    		$contact->addSetting($theme);
    	}
    	
    	foreach($form->get('services_interet')->getData() as $service){
			//~ var_dump($service);
    	//~ exit;
    		$contact->addSetting($service);
    	}

        foreach($form->get('secteur')->getData() as $secteur){
            //~ var_dump($service);
            //~ exit;
            $contact->addSetting($secteur);
        }

    	foreach($form->get('types')->getData() as $type){
    		$contact->addSetting($type);
    	}
    }

}
