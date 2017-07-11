<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\EntityRepository;

class ContactImporterMappingType extends AbstractType
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

		$nom = 'A';
		$prenom = 'B';
		$titre = 'C';
        $reseau = 'D';
        $compte = 'E';
        $telephone_fixe = 'F';
        $telephone_portable = 'G';
        $telephoneAutres = 'H';
        $fax = 'I';
        $email = 'J';
        $email2 = 'K';
        $adresse = 'L';
        $codePostal = 'M';
        $ville = 'N';
        $region = 'O';
        $pays = 'P';
        $description = 'Q';
        $carteVoeux = 'R';
        $newsLetter = 'S';
        $origine = 'T';
        $serviceInteret = 'U';
        $themeInteret = 'V';
        $secteurActivite = 'w';


		$arr_formats = array(
				'd/m/Y' => 'jour/mois/année (4 chiffres)',
				'd/m/y'=> 'jour/mois/année (2 chiffres)',
				'd-m-Y' => 'jour-mois-année (4 chiffres)',
				'd-m-y'=> 'jour-mois-année (2 chiffres)',
		);
 
		foreach( $this->filename as $k=>$v )
		{
        $builder
			->add('dateFormat'.$k, 'choice', array(
					'label'	=> 'Format des dates',
					'required' => true,
					'choices' => $arr_formats
			))
            ->add('prenom'.$k, 'choice', array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Prénom',
				'choices' => $this->arr_headers[$k],
				'data' => $prenom
        	))
            ->add('nom'.$k, 'choice', array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Nom',
				'choices' => $this->arr_headers[$k],
				'data' => $nom
        	))
            ->add('pays'.$k, 'choice', array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Pays',
				'choices' => $this->arr_headers[$k],
				'data' => $pays
        	))
            ->add('reseau'.$k, 'choice', array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Réseau',
				'choices' => $this->arr_headers[$k],
				'data' => $reseau
        	))
            ->add('compte'.$k, 'choice', array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Nom de l\'organisation',
				'choices' => $this->arr_headers[$k],
				'data' => $compte
        	))
            ->add('titre'.$k, 'choice', array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Titre/fonction',
				'choices' => $this->arr_headers[$k],
				'data' => $titre
        	))
            ->add('description'.$k, 'choice', array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Description',
				'choices' => $this->arr_headers[$k],
				'data' => $description
        	))
            ->add('email'.$k, 'choice', array(
        		'required' => true,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Email',
				'choices' => $this->arr_headers[$k],
				'data' => $email
        	))
            ->add('telephoneFixe'.$k, 'choice', array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Téléphone fixe',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_fixe
        	))
            ->add('telephonePortable'.$k, 'choice', array(
        		'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
            	'label' => 'Tél. portable pro',
				'choices' => $this->arr_headers[$k],
				'data' => $telephone_portable
        	))
            ->add('adresse'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Adresse',
                'choices' => $this->arr_headers[$k],
                'data' => $adresse
            ))
            ->add('telephoneAutres'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Tel. (autre)',
                'choices' => $this->arr_headers[$k],
                'data' => $telephoneAutres
            ))
            ->add('fax'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Fax',
                'choices' => $this->arr_headers[$k],
                'data' => $fax
            ))
            ->add('email2'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Mail(2)',
                'choices' => $this->arr_headers[$k],
                'data' => $email2
            ))
            ->add('codePostal'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Code postal',
                'choices' => $this->arr_headers[$k],
                'data' => $codePostal
            ))
            ->add('ville'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Ville',
                'choices' => $this->arr_headers[$k],
                'data' => $ville
            ))
            ->add('region'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Region',
                'choices' => $this->arr_headers[$k],
                'data' => $region
            ))
            ->add('carteVoeux'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Carte de voeux',
                'choices' => $this->arr_headers[$k],
                'data' => $carteVoeux
            ))
            ->add('newsletter'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'News letter',
                'choices' => $this->arr_headers[$k],
                'data' => $newsLetter
            ))


            ->add('origine'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Origine',
                'choices' => $this->arr_headers[$k],
                'data' => $origine
            ))
            ->add('serviceInteret'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Service d\'interêt',
                'choices' => $this->arr_headers[$k],
                'data' => $serviceInteret
            ))
            ->add('themeInteret'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Thème d\'interêt',
                'choices' => $this->arr_headers[$k],
                'data' => $themeInteret
            ))
            ->add('secteurActivite'.$k, 'choice', array(
                'required' => false,
                'placeholder' => 'Choisir la colonne correspondante',
                'label' => 'Secteur d\'activité',
                'choices' => $this->arr_headers[$k],
                'data' => $secteurActivite
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
        return 'appbundle_crm_importcontactemapping';
    }
}
