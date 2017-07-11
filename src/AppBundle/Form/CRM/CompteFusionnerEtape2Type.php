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

class CompteFusionnerEtape2Type extends AbstractType
{
	
	protected $first_compte;
	protected $second_compte;
	protected $router;
	
	public function __construct ($first_compte, $second_compte, Router $router)
	{
		$this->first_compte = $first_compte;
		$this->second_compte = $second_compte;
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
		$builder
            ->add('nom', 'choice', array(
				 'choices' => array( 'nom1' => $this->first_compte->getNom(), 'nom2' => $this->second_compte->getNom()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
        		'required' => true,
            	'label' => 'Nom de l\'organisation'
        	));
        if( $this->second_compte->getTelephone() != '' )
			$builder
            ->add('telephoneFixe', 'choice', array(
            	'required' => true,
				 'choices' => array( 'telephoneFixe1' => $this->first_compte->getTelephone(), 'telephoneFixe2' => $this->second_compte->getTelephone()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            //	'default_region' => 'FR',
            //	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Téléphone fixe'
        	));
        if( $this->second_compte->getFax() != '' )
			$builder
            ->add('fax','choice', array(
            	'required' => true,
				 'choices' => array( 'fax1' => $this->first_compte->getFax(), 'fax2' => $this->second_compte->getFax()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
          //  	'default_region' => 'FR',
          //  	'format' => PhoneNumberFormat::INTERNATIONAL,
            	'label' => 'Fax'
        	));
        if( $this->second_compte->getUrl() != '' )
			$builder
            ->add('url', 'choice', array(
            	'required' => true,
				 'choices' => array( 'url1' => $this->first_compte->getUrl(), 'url2' => $this->second_compte->getUrl()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'URL'
        	));
        if( $this->second_compte->getAdresse() != '' )
			$builder
            ->add('adresse', 'choice', array(
            	'required' => true,
				 'choices' => array( 'adresse1' => $this->first_compte->getAdresse()." ".$this->first_compte->getCodePostal().' '.$this->first_compte->getVille().' '.$this->first_compte->getRegion().' '.$this->first_compte->getPays(), 'adresse2' => $this->second_compte->getAdresse().' '.$this->second_compte->getCodePostal().' '.$this->second_compte->getVille().' '.$this->second_compte->getRegion().' '.$this->second_compte->getPays()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Adresse'
        	));
        if( $this->second_compte->getCompteParent() != '' )
			$builder
            ->add('compteParent', 'choice', array(
            	'required' => true,
				 'choices' => array( 'compteParent1' => $this->first_compte->getCompteParent(), 'compteParent2' => $this->second_compte->getCompteParent()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Organisation parente'
        	));
        if( $this->second_compte->getCodeEvoliz() != '' )
			$builder
            ->add('codeEvoliz', 'choice', array(
            	'required' => true,
				 'choices' => array( 'codeEvoliz1' => $this->first_compte->getCodeEvoliz(), 'codeEvoliz2' => $this->second_compte->getCodeEvoliz()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
				 'label' => 'Code Evoliz'
            ));

        if( $this->second_compte->getCompany() != '' && $this->first_compte->getCompany()->getId() != $this->second_compte->getCompany()->getId() )
			$builder
            ->add('company', 'choice', array(
            	'required' => true,
				 'choices' => array( 'company1' => $this->first_compte->getCompany(), 'company2' => $this->second_compte->getCompany()),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
				 'label' => 'Compagnie'
            ));
        if( $this->first_compte->getUserGestion()->getId() != $this->second_compte->getUserGestion()->getId() )
			$builder
            ->add('userGestion', 'choice', array(
            	'required' => true,
				 //~ 'choices' => array( 'userGestion1' => $this->first_compte->getUserGestion() , 'userGestion2' => $this->second_compte->getUserGestion() ),
				 'choices' => array( $this->first_compte->getUserGestion()->getId() => $this->first_compte->getUserGestion() , $this->second_compte->getUserGestion()->getId() => $this->second_compte->getUserGestion() ),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
				 'label' => 'Gestionnaire de l\'organisation'
            ));

        if( $this->first_compte->getSecteurActivite()->getId() != $this->second_compte->getSecteurActivite()->getId() )
            $builder
                ->add('secteurActivite', 'choice', array(
                    'required' => true,
                    //~ 'choices' => array( 'userGestion1' => $this->first_compte->getUserGestion() , 'userGestion2' => $this->second_compte->getUserGestion() ),
                    'choices' => array( $this->first_compte->getSecteurActivite()->getId() => $this->first_compte->getSecteurActivite() , $this->second_compte->getUserGestion()->getId() => $this->second_compte->getUserGestion() ),
                    'expanded' => true,
                    'multiple' => false,
                    'mapped' => false,
                    'label' => 'Secteur d\'activite'
                ));

        if( $this->second_compte->getDescription() != '' )
			$builder
            ->add('description', 'choice', array(
            	'required' => true,
				 'choices' => array( 'description1' => substr($this->first_compte->getDescription(),0,50), 'description2' => substr($this->second_compte->getDescription(),0,50) ),
				 'expanded' => true,
				 'multiple' => false,
				 'mapped' => false,
            	'label' => 'Description'
        	));

                        
			$builder
            ->add('second_compte_id', 'hidden', array(
				  'required' => true,
				  'mapped' => false,
				  'data' => $this->second_compte->getId(),
            ));

			$builder
           	->setAction($this->router->generate('crm_compte_fusionner_execution', array('id' => $this->first_compte->getId()) ));
	
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
        return 'appbundle_crm_compte_fusionner_etape2';
    }
}
