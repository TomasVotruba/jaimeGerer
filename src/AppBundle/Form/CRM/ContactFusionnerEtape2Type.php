<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;
//~ use Symfony\Component\HttpFoundation\Request;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityRepository;

use libphonenumber\PhoneNumberFormat;

class ContactFusionnerEtape2Type extends AbstractType
{
	
	protected $first_contact;
	protected $second_contact;
	
	public function __construct ($first_contact, $second_contact, Router $router)
	{
		$this->first_contact = $first_contact;
		$this->second_contact = $second_contact;
		$this->router = $router;
		//~ var_dump($this->first_contact);
		//~ var_dump($this->second_contact);
		//~ exit;
		//~ $this->id_contact_fusion = $id_contact_fusion;
		//~ var_dump($this->request); exit;
		//~ var_dump($this->id_contact_fusion); exit;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//~ var_dump($this->first_contact->getSettings());
//~ var_dump($this->first_contact->getPriseContacts());
//~ print_r($this->second_contact->getSettings());
$theme_interet_choices = array();
$service_interet_choices = array();
$secteur_choices = array();
$type_choices = array();
foreach($this->second_contact->getSettings() as $setting){
	switch($setting->getParametre()){
		case 'THEME_INTERET':
			$theme_interet_choices['second_contact'][$setting->getId()] = $setting->getValeur();
			break;
		case 'SERVICE_INTERET':
			$service_interet_choices['second_contact'][$setting->getId()] = $setting->getValeur();
			break;
        case 'SECTEUR':
            $secteur_choices['second_contact'][$setting->getId()] = $setting->getValeur();
            break;
		case 'TYPE':
			$type_choices['second_contact'][$setting->getId()] = $setting->getValeur();
			break;
	}
}
foreach($this->first_contact->getSettings() as $setting){
	switch($setting->getParametre()){
		case 'THEME_INTERET':
			$theme_interet_choices['first_contact'][$setting->getId()] = $setting->getValeur();
			break;
		case 'SERVICE_INTERET':
			$service_interet_choices['first_contact'][$setting->getId()] = $setting->getValeur();
			break;
        case 'SECTEUR':
            $secteur_choices['second_contact'][$setting->getId()] = $setting->getValeur();
            break;
		case 'TYPE':
			$type_choices['first_contact'][$setting->getId()] = $setting->getValeur();
			break;
	}
}
if( !isset($theme_interet_choices['first_contact']) ) $theme_interet_choices['first_contact'] = array();
if( !isset($service_interet_choices['first_contact']) ) $service_interet_choices['first_contact'] = array();
if( !isset($type_choices['first_contact']) ) $type_choices['first_contact'] = array();
if( !isset($secteur_choices['first_contact']) ) $secteur_choices['first_contact'] = array();
if( !isset($theme_interet_choices['second_contact']) ) $theme_interet_choices['second_contact'] = array();
if( !isset($service_interet_choices['second_contact']) ) $service_interet_choices['second_contact'] = array();
if( !isset($secteur_choices['second_contact']) ) $secteur_choices['second_contact'] = array();
if( !isset($type_choices['second_contact']) ) $type_choices['second_contact'] = array();
//~ var_dump($theme_interet_choices);
	//~ echo "<br><br><br>";
//~ var_dump($service_interet_choices);
	//~ echo "<br><br><br>";
//~ var_dump($type_choices);
	//~ echo "<br><br><br>";
//~ var_dump($this->second_contact->getPriseContacts());
//~ exit;
        $builder
            ->add('prenom', 'choice', array(
				 'choices' => array( 'prenom1' => $this->first_contact->getPrenom(), 'prenom2' => $this->second_contact->getPrenom()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
				'required' => true,
            	'label' => 'Prénom'
        	))
            ->add('nom', 'choice', array(
				 'choices' => array( 'nom1' => $this->first_contact->getNom(), 'nom2' => $this->second_contact->getNom()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
        		'required' => true,
            	'label' => 'Nom'
        	));
        if( $this->second_contact->getTelephoneFixe() != '' )
			$builder
            ->add('telephoneFixe', 'choice', array(
            	'required' => true,
				 'choices' => array( 'telephoneFixe1' => $this->first_contact->getTelephoneFixe(), 'telephoneFixe2' => $this->second_contact->getTelephoneFixe()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	));
        if( $this->second_contact->getTelephonePortable() != '' )
			$builder
            ->add('telephonePortable','choice', array(
            	'required' => true,
				 'choices' => array( 'telephonePortable1' => $this->first_contact->getTelephonePortable(), 'telephonePortable2' => $this->second_contact->getTelephonePortable()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Tél. portable pro'
        	));
        if( $this->second_contact->getEmail() != '' )
			$builder
            ->add('email', 'choice', array(
            	'required' => true,
				 'choices' => array( 'email1' => $this->first_contact->getEmail(), 'email2' => $this->second_contact->getEmail()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Email'
        	));
        if( $this->second_contact->getAdresse() != '' )
			$builder
            ->add('adresse', 'choice', array(
            	'required' => true,
				 'choices' => array( 'adresse1' => $this->first_contact->getAdresse()." ".$this->first_contact->getCodePostal().' '.$this->first_contact->getVille(), 'adresse2' => $this->second_contact->getAdresse().' '.$this->second_contact->getCodePostal().' '.$this->second_contact->getVille()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Adresse'
        	));
        if( $this->second_contact->getDescription() != '' )
			$builder
            ->add('description', 'choice', array(
            	'required' => true,
				 'choices' => array( 'description1' => substr($this->first_contact->getDescription(),0,50), 'description2' => substr($this->second_contact->getDescription(),0,50) ),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Description'
        	));
        if( $this->second_contact->getTitre() != '' )
			$builder
            ->add('titre', 'choice', array(
            	'required' => true,
				 'choices' => array( 'titre1' => $this->first_contact->getTitre(), 'titre2' => $this->second_contact->getTitre()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Titre'
        	));
        if( $this->second_contact->getFax() != '' )
			$builder
            ->add('fax', 'choice', array(
            	'required' => true,
				 'choices' => array( 'fax1' => $this->first_contact->getFax(), 'fax2' => $this->second_contact->getFax()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
           // 	'default_region' => 'FR',
           // 	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	));
        if( $this->first_contact->getReseau() && $this->second_contact->getReseau() && $this->first_contact->getReseau()->getId() != $this->second_contact->getReseau()->getId() )
			$builder
            ->add('reseau', 'choice', array(
            	'required' => true,
				 //~ 'choices' => array( 'userGestion1' => $this->first_contact->getUserGestion() , 'userGestion2' => $this->second_contact->getUserGestion() ),
				 'choices' => array( $this->first_contact->getReseau()->getId() => $this->first_contact->getReseau() , $this->second_contact->getReseau()->getId() => $this->second_contact->getReseau() ),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            		'label' => 'Réseau'
            ));

        if( $this->first_contact->getUserGestion()->getId() != $this->second_contact->getUserGestion()->getId() )
			$builder
            ->add('userGestion', 'choice', array(
            	'required' => true,
				 //~ 'choices' => array( 'userGestion1' => $this->first_contact->getUserGestion() , 'userGestion2' => $this->second_contact->getUserGestion() ),
				 'choices' => array( $this->first_contact->getUserGestion()->getId() => $this->first_contact->getUserGestion() , $this->second_contact->getUserGestion()->getId() => $this->second_contact->getUserGestion() ),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            		'label' => 'Gestionnaire du contact'
            ));

        if( count($theme_interet_choices) > 0 && (count($theme_interet_choices['second_contact']) > 0 || count($theme_interet_choices['first_contact']) > 0) )
        {
			$DefaultData = array();
			foreach( $service_interet_choices as $k=>$v )
			{
				foreach( $v as $key=>$value )
				{
					$DefaultData[] = $key;
				}
			}
			$DataInNextField = array( $this->first_contact->getPrenom().' '. $this->first_contact->getNom() 	=> $service_interet_choices['first_contact'],
									  $this->second_contact->getPrenom().' '. $this->second_contact->getNom() 	=> $service_interet_choices['second_contact']
									);
			$DataInNextField = array( $this->first_contact->getPrenom().' '. $this->first_contact->getNom() 	=> $theme_interet_choices['first_contact'],
									  $this->second_contact->getPrenom().' '. $this->second_contact->getNom() 	=> $theme_interet_choices['second_contact']
									);
			$builder
			->add('themes_interet', 'choice', array(
             		'label' => 'Thèmes d\'intérêt',
					'choices' => $DataInNextField,
					'mapped' => false,
             		'multiple' => true,
             		'data' => $DefaultData,
			));
        }       

        if( count($service_interet_choices) > 0 && (count($service_interet_choices['second_contact']) > 0 || count($service_interet_choices['first_contact']) > 0) )
        {
			$DefaultData = array();
			foreach( $service_interet_choices as $k=>$v )
			{
				foreach( $v as $key=>$value )
				{
					$DefaultData[] = $key;
				}
			}
			$DataInNextField = array( $this->first_contact->getPrenom().' '. $this->first_contact->getNom() 	=> $service_interet_choices['first_contact'],
									  $this->second_contact->getPrenom().' '. $this->second_contact->getNom() 	=> $service_interet_choices['second_contact']
									);
			$builder
			->add('services_interet', 'choice', array(
             		'label' => 'Services d\'intérêt',
					'choices' => $DataInNextField,
					'mapped' => false,
             		'multiple' => true,
             		//~ 'preferred_choices' => array_keys($service_interet_choices['second_contact']),
             		'data' => $DefaultData,
			));
        }

        if( count($secteur_choices) > 0 && (count($secteur_choices['second_contact']) > 0 || count($secteur_choices['first_contact']) > 0) )
        {
            $DefaultData = array();
            foreach( $secteur_choices as $k=>$v )
            {
                foreach( $v as $key=>$value )
                {
                    $DefaultData[] = $key;
                }
            }
            $DataInNextField = array( $this->first_contact->getPrenom().' '. $this->first_contact->getNom() 	=> $secteur_choices['first_contact'],
                $this->second_contact->getPrenom().' '. $this->second_contact->getNom() 	=> $secteur_choices['second_contact']
            );
            $builder
                ->add('secteur', 'choice', array(
                    'label' => 'Secteur d\'activité',
                    'choices' => $DataInNextField,
                    'mapped' => false,
                    'multiple' => true,
                    //~ 'preferred_choices' => array_keys($service_interet_choices['second_contact']),
                    'data' => $DefaultData,
                ));
        }


        if( count($type_choices) > 0 && (count($type_choices['second_contact']) > 0 || count($type_choices['first_contact']) > 0) )
        {
			$DefaultData = array();
			foreach( $type_choices as $k=>$v )
			{
				foreach( $v as $key=>$value )
				{
					$DefaultData[] = $key;
				}
			}
			$DataInNextField = array( $this->first_contact->getPrenom().' '. $this->first_contact->getNom() 	=> $type_choices['first_contact'],
									  $this->second_contact->getPrenom().' '. $this->second_contact->getNom() 	=> $type_choices['second_contact']
									);

			$builder
			->add('types', 'choice', array(
					'label' => 'Type de relation commerciale',
					'choices' => $DataInNextField,
					'mapped' => false,
             		'multiple' => true,
             		'data' => $DefaultData,
			));
        }       
                        
			$builder
            ->add('newsletter', 'checkbox', array(
        		'required' => false,
   				'mapped' => false,
  				'label' => 'Newsletter',
  				'attr'     => array( 'checked'   => $this->first_contact->getNewsletter() ),
        	))
            ->add('carteVoeux', 'checkbox', array(
        		'required' => false,
   				'mapped' => false,
  				'label' => 'Carte de voeux',
  				'attr'     => array( 'checked'   => $this->first_contact->getCarteVoeux() ),
        	))
            ->add('carteVoeux', 'checkbox', array(
        		'required' => false,
   				'mapped' => false,
 				'label' => 'Carte de voeux',
  				'attr'     => array( 'checked'   => $this->first_contact->getCarteVoeux() ),
        	))
            ->add('second_contact_id', 'hidden', array(
            	'required' => true,
            	'mapped' => false,
				 'data' => $this->second_contact->getId(),
            ));

			$builder
           	->setAction($this->router->generate('crm_contact_fusionner_execution', array('id' => $this->first_contact->getId()) ));
	
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
        return 'appbundle_crm_compte_fusionner_etape2';
    }
}
