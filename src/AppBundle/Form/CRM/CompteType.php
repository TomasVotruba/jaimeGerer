<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use libphonenumber\PhoneNumberFormat;

class CompteType extends AbstractType
{
	
	protected $userGestionId;
	protected $companyId;
	protected $formAction;
	
	public function __construct ($userGestionId = null, $companyId = null, $formAction = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
		$this->formAction = $formAction;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $compte = $builder->getData();

        $builder
            ->add('nom', 'text', array(
        		'label' => 'Nom de l\'organisation'
        	))
            ->add('telephone','text', array(
            	'required' => false,
            	'label' => 'Téléphone'
        	))
            ->add('fax', 'text', array(
            	'required' => false,
            	'label' => 'Fax'
        	))
            ->add('url', 'url', array(
            	'required' => false,
            	'label' => 'URL du site web',
                'attr'   =>  array(
                    'class' => "urlId",
                )
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
            ->add('nomFacturation', 'text', array(
                'required' => false,
                'label' => 'Nom (facturation)',
            ))
            ->add('adresseFacturation', 'text', array(
                'required' => false,
                'label' => 'Adresse (facturation)',
            ))
            ->add('adresseFacturationLigne2', 'text', array(
                'required' => false,
                'label' => 'Adresse suite (facturation)',
            ))
            ->add('codePostalFacturation', 'text', array(
                'required' => false,
                'label' => 'Code postal (facturation)',
            ))
            ->add('villeFacturation', 'text', array(
                'required' => false,
                'label' => 'Ville (facturation)',
            ))
            ->add('paysFacturation', 'text', array(
                'required' => false,
                'label' => 'Pays (facturation)' ,        
            ))
            ->add('description', 'textarea', array(
            	'required' => false,
            	'label' => 'Description'
        	))
            ->add('compteParent', 'shtumi_ajax_autocomplete', array(
            		'entity_alias'=>'comptes',
            		'required' => false,
            		'label' => 'Organisation parente'
           	))
            ->add('secteurActivite', 'entity', array(
                'class'=>'AppBundle:Settings',
                'property' => 'valeur',
                'required' => true,
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
            ->add('updateContacts', 'checkbox', array(
                'label' => 'Mettre à jour les contacts avec la nouvelle adresse',
                'mapped' => false,
                'required' => false
            ))
            ->add('adresseFacturationDifferente', 'checkbox', array(
                'label' => 'Adresse de facturation différente',
                'mapped' => false,
                'required' => false,
                'attr' => array('class' => 'checkbox-adresse-facturation'),
                'data' => $compte->getAdresseFacturation()?true:false
            ))
            ->add('priveOrPublic', 'choice', array(
                'label' => 'Privé ou public ?',
                'required' => false,
                'choices' => array(
                    'PUBLIC' => 'Public',
                    'PRIVE' => 'Privé'
                )
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
