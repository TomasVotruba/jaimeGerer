<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use libphonenumber\PhoneNumberFormat;


class ContactWebFormType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $request;
	
	public function __construct ($userGestionId = null, $companyId = null, $request = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
		$this->request = $request;
		//~ var_dump($this->request); exit;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		//~ var_dump(FormEvent::getData()); exit;
    	$arr_delaiNum = array();
    	for($i=1; $i<13; $i++){
    		$arr_delaiNum[$i] = $i;
    	}
    	
    	$arr_delaiUnit= array(
    			'DAY' => 'jours', 
    			'WEEK' => 'semaines', 
    			'MONTH' => 'mois');

        $builder
            ->add('nom_formulaire', 'text', array(
        		'required' => true,
            	'label' => 'Nom du formulaire'
        	))
        	->add('returnUrl', 'url', array(
        			'required' => true,
        			'label' => 'URL de destination'
        	))
            ->add('nomCompte', 'checkbox', array(
        		'required' => false,
            	'label' => 'Nom de l\'organisation',
        	))
            ->add('prenomContact', 'checkbox', array(
        		'required' => false,
            	'label' => 'Prénom du contact'
        	))
            ->add('nomContact', 'checkbox', array(
        		'required' => false,
            	'label' => 'Nom du Contact'
        	))
            ->add('adresse', 'checkbox', array(
        		'required' => false,
            	'label' => 'Adresse'
        	))
            ->add('codePostal', 'checkbox', array(
        		'required' => false,
            	'label' => 'Code postal'
        	))
            ->add('ville', 'checkbox', array(
        		'required' => false,
            	'label' => 'Ville'
        	))
            ->add('region', 'checkbox', array(
        		'required' => false,
            	'label' => 'Région'
        	))
            ->add('pays', 'checkbox', array(
        		'required' => false,
            	'label' => 'Pays'
        	))
            ->add('telephoneFixe', 'checkbox', array(
            	'required' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	))
            ->add('telephonePortable','checkbox', array(
            	'required' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Tél. portable pro'
        	))
            ->add('email', 'checkbox', array(
        		'required' => false,
            	'label' => 'Email'
        	))
            ->add('fax', 'checkbox', array(
            	'required' => false,
           // 	'default_region' => 'FR',
           // 	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	))
            ->add('url', 'checkbox', array(
        		'required' => false,
            	'label' => 'URL du site web',
        	))

 
 
            ->add('carteVoeux', 'checkbox', array(
        		'required' => false,
  				'label' => 'Carte de voeux'
        	))
            ->add('newsletter', 'checkbox', array(
        		'required' => false,
  				'label' => 'Newsletter'
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
            ->add('gestionnaireSuivi', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => false,
           			'label' => 'Gestionnaire du suivi',
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
			->add('envoyerEmail', 'choice', array(
				'choices' => array(
					'1' => 'Envoyer un email au nouveau contact',
					'0' => 'Ne pas envoyer d\'email au nouveau contact'
				),
				'multiple' => false,
				'expanded' => true,
				'required' => true,
				'data'     => '1',
				'disabled' => true
			))
			->add('expediteur', 'text', array(
				'required' => false,
				'label' => 'Emetteur de l\'email',
				'disabled' => true
			))
			->add('corpsEmail', 'textarea', array(
				'required' => false,
				'label' => 'Corps de l\'email',
				'disabled' => true
			))
			->add('objetEmail', 'text', array(
				'required' => false,
				'label' => 'Objet de l\'email',
				'disabled' => true
			))
			->add('copieEmail', 'checkbox', array(
				'required' => false,
				'label' => 'Envoyer une copie au gestionnaire de contact',
				'disabled' => true
			))
			->add('delaiNum', 'integer', array(
           		'label' => 'Contacter tous les',
           		'required' => false,
     		))
           	->add('delaiUnit', 'choice', array(
           			'choices' => $arr_delaiUnit,
           			'label_attr' => array('class' => 'invisible'),
           			'required' => false,
           	));
           	
                         
             $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
             $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
             $builder->addEventListener(FormEvents::POST_BIND, array($this, 'onPostBind'));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\ContactWebForm',
            'attr' => array('id' => 'ContactWebForm')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_contactwebform';
    }
    
    public function onPreSetData(FormEvent $event){
    	$contact = $event->getData();
    	$form = $event->getForm();
//~ var_dump($form->getChildren()); exit;
//~ var_dump($contact); exit;
    	$arr_themes = array();
    	$arr_services = array();
    	$arr_types = null;
    	foreach($contact->getSettings() as $setting){
    		if($setting->getModule() == 'CRM' && $setting->getParametre() == 'THEME_INTERET'){
    			$arr_themes[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'SERVICE_INTERET'){
    			$arr_services[] = $setting;
    		} else if($setting->getModule() == 'CRM' && $setting->getParametre() == 'TYPE'){
    			$arr_types[] = $setting;
    		}
    	}
    	$posts = $this->request->request->get($form->getName());
		$fieldsToRename = array('nomCompte' => 'Nom de l\'organisation', 
								'prenomContact' => 'Prénom du Contact',  
								'nomContact' => 'Nom du Contact',  
								'adresse' => 'Adresse',  
								'codePostal' => 'Code postal',  
								'ville' => 'Ville',  
								'region' => 'Région',  
								'pays' => 'Pays',  
								'telephoneFixe' => 'Téléphone fixe',  
								'telephonePortable' => 'Tél. portable pro',  
								'email' => 'Email',  
								'fax' => 'Fax',  
								'url' => 'URL du site web');
		if( $contact->getEmail() )
		{
			$form->remove('envoyerEmail');
			$form->remove('expediteur');
			$form->remove('corpsEmail');
			$form->remove('objetEmail');
			$form->remove('copieEmail');
			$form
			->add('envoyerEmail', 'choice', array(
			'choices' => array(
				'1' => 'Envoyer un email au nouveau contact',
				'0' => 'Ne pas envoyer d\'email au nouveau contact'
			),
			'multiple' => false,
			'expanded' => true,
			'required' => true,
			'data'     => '1'
			))
			->add('expediteur', 'text', array(
				'required' => false,
				'label' => 'Emetteur de l\'email'
			))
			->add('corpsEmail', 'textarea', array(
				'required' => false,
				'label' => 'Corps de l\'email'
			))
			->add('objetEmail', 'text', array(
				'required' => false,
				'label' => 'Objet de l\'email'
			))
			->add('copieEmail', 'checkbox', array(
				'required' => false,
				'label' => 'Envoyer une copie au gestionnaire de contact'
			));

		}
		foreach( $fieldsToRename as $k=>$v )
		{
			if( $contact->getId() > 0 )
			{
				//~ var_dump($form[$v]); 
				//~ echo $v."<br>";
				//~ var_dump($posts[$v]); echo "<br><br>";
				//~ $form[$k]['data'] = array($v);
				$methodGet = 'get'.ucfirst($k);
				eval("\$var = \$contact->$methodGet();");
				if( $var != '' )
				{
				//~ var_dump($var);
					$form->remove($k);
					$form->add($k, 'checkbox', array(
								'required' => false,
								'label' => $v,
								'mapped' => false,
								'data' => true,
						));
					//~ $form->add($k, 'choice', array(
								//~ 'required' => false,
								//~ 'label' => false,
								//~ 'expanded' => true,
								//~ 'multiple' => true,
								//~ 'choices' => array($v => $v),
								//~ 'mapped' => false,
								//~ 'data' => array($v),
						//~ ));
					$form->add('new_value_'.$k, 'text', array(
								'required' => false,
								'label' => false,
								'mapped' => false,
								'data' => $var,
								//~ 'attr' => array('class' => ''),
						));
				}
				else
				{
					//~ var_dump(is_null($var)); exit;
					$var = $v;
					$form->remove($k);
					$form->add($k, 'checkbox', array(
								'required' => false,
								'label' => $var,
								'mapped' => false,
								'data' => false,
						));
					$form->add('new_value_'.$k, 'hidden', array(
								'required' => false,
								'label' => false,
								'mapped' => false,
								'data' => $var,
								//~ 'attr' => array('class' => ''),
						));
				}
				//~ $form->remove($v);
				//~ $form->add($v, 'text', array(
						//~ 'required' => false,
						//~ 'label' => false,
						//~ 'data' => is_array($posts[$v]) ? $posts[$v][0] : $posts[$v],
					//~ ));
			}
			else
			{
				$form->add('new_value_'.$k, 'hidden', array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => $v,
							//~ 'attr' => array('class' => ''),
					));
			}
		//~ exit;
		}
    	//~ nomCompte
    	//~ var_dump($this->request->request->get($form->getName())); exit;
    	if( is_array($posts) && count($posts) > 0 )
    	{
			foreach( $fieldsToRename as $k=>$v )
			{
				$form->remove('new_value_'.$k);
				if( isset($posts[$k]) )
				{
					//~ echo $v."<br>";
					//~ var_dump($posts[$v]); echo "<br><br>";
					//~ $form->remove($k);
					//~ $form->add($k, 'text', array(
							//~ 'required' => false,
							//~ 'label' => false,
							//~ 'data' => is_array($posts[$k]) ? $posts[$k][0] : $posts[$k],
						//~ ));
					$form->add('new_value_'.$k, 'text', array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => is_array($posts[$k]) ? $posts[$k][0] : $posts[$k],
						));
				}
				else
				{
					$form->add('new_value_'.$k, 'hidden', array(
							'required' => false,
							'label' => false,
							'mapped' => false,
							'data' => is_array($posts['new_value_'.$k]) ? $posts['new_value_'.$k][0] : $posts['new_value_'.$k],
						));
					
				}
			}
			//~ var_dump($posts); exit;
			//~ exit;
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
    	
    	foreach($form->get('themes_interet')->getData() as $theme){
    		$contact->addSetting($theme);
    	}
    	
    	foreach($form->get('services_interet')->getData() as $service){
    		$contact->addSetting($service);
    	}
    	foreach($form->get('types')->getData() as $type){
    		$contact->addSetting($type);
    	}

    	//~ $form->remove('nomCompte');
    	//~ $form->add('nomCompte', 'text', array(
        		//~ 'required' => false,
            	//~ 'label' => false,
            	//~ 'data' => 'hich',
//~ 
        	//~ ));
		//~ var_dump($contact); exit;
    }

    public function onPostBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        //~ var_dump($form->isValid()); exit;
        
		//~ $fieldsToRename = array('nomCompte', 'prenomContact',  'nomContact',  'adresse',  'codePostal',  'ville',  'region',  'pays',  'telephoneFixe',  'telephonePortable',  'email',  'fax',  'url');
		$fieldsToRename = array('nomCompte' => 'Nom de l\'organisation', 
								'prenomContact' => 'Prénom du Contact',  
								'nomContact' => 'Nom du Contact',  
								'adresse' => 'Adresse',  
								'codePostal' => 'Code postal',  
								'ville' => 'Ville',  
								'region' => 'Région',  
								'pays' => 'Pays',  
								'telephoneFixe' => 'Téléphone fixe',  
								'telephonePortable' => 'Tél. portable pro',  
								'email' => 'Email',  
								'fax' => 'Fax',  
								'url' => 'URL du site web');
    	$envoyerMail = array('envoyerEmail', 'expediteur', 'corpsEmail', 'objetEmail', 'copieEmail');
		$posts = $this->request->request->get($form->getName());
    	//~ var_dump($posts);
		//~ var_dump($form->isValid()); exit;
		if( $form->isValid() )
		{
			if( isset($posts['email']) )
			{
				foreach( $envoyerMail as $v )
				{
					$NewVal = isset($posts[$v]) && $posts[$v] != '' ? $posts[$v] : NULL;
					$methodSet = 'set'.ucfirst($v);
					//~ var_dump($NewVal);
					eval("\$data->$methodSet(\$NewVal);");
				}
			}
			foreach( $fieldsToRename as $k=>$v )
			{
				$NewVal = isset($posts[$k]) ? ( $posts['new_value_'.$k] != '' ? $posts['new_value_'.$k] : $v ) : NULL;
				$methodSet = 'set'.ucfirst($k);
				//~ var_dump($NewVal);
				eval("\$data->$methodSet(\$NewVal);");					
			}
		}
		$event->setData($data);
    }

}
