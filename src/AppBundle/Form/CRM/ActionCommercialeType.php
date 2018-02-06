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

class ActionCommercialeType extends AbstractType
{
	protected $userGestionId;
    protected $companyId;
    protected $devis;
	protected $compte;

	public function __construct ($userGestionId = null, $companyId = null, $devis = null, $compte = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
        $this->devis = $devis;
        $this->compte = $compte;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nom', 'text', array(
        		'label' => 'Objet'
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
            ->add('date', 'date', array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date',
            ))
            ->add('dateValidite', 'date', array('widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
                'label' => 'Date de validité',
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getDateValidite() : new \DateTime(date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d')))))
            ))
            ->add('compte_name', 'text', array(
                'required' => true,
                'mapped' => false,
                'label' => 'Organisation',
                'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
            ))
            ->add('compte', 'hidden', array(
                'required' => true,
                'attr' => array('class' => 'entity-compte'),
            ))
            ->add('contact_name', 'text', array(
                'required' => false,
                'mapped' => false,
                'label' => 'Contact',
                'attr' => array('class' => 'typeahead-contact', 'autocomplete' => 'off')
            ))
            ->add('contact', 'hidden', array(
                'required' => false,
                'attr' => array('class' => 'entity-contact'),
            ))
             ->add('adresse', 'text', array(
                'required' => true,
                'label' => 'Adresse',
                'attr' => array('class' => 'input-adresse'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getAdresse() : null
            ))
            ->add('codePostal', 'text', array(
                'required' => true,
                'label' => 'Code postal',
                'attr' => array('class' => 'input-codepostal'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getCodePostal() : null
            ))
            ->add('ville', 'text', array(
                'required' => true,
                'label' => 'Ville',
                'attr' => array('class' => 'input-ville'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getVille() : null
            ))
            ->add('region', 'text', array(
                'required' => true,
                'label' => 'Région',
                'attr' => array('class' => 'input-region'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getRegion() : null
            ))
            ->add('pays', 'text', array(
                'required' => true,
                'label' => 'Pays',
                'attr' => array('class' => 'input-pays'),
                'mapped' => false,
                'data' => $this->compte ? $this->compte->getPays() : null
            ))
             ->add('type', 'choice', array(
                'label' => 'Type',
                'choices' => array(
                        'Existing Business' => 'Compte existant',
                        'New Business' => 'Nouveau compte',
                ),
                'required' => true
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
                'attr' => array('class' => 'devis-analytique'),
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
            ->add('appelOffre', 'checkbox', array(
                'label' => 'Appel d\'offre',
                'required' => false,
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
            ->add('produits', 'collection', array(
                'type' => new ProduitType($this->companyId),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden'),
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getProduits() : null
            ))
            ->add('sousTotal', 'number', array(
                'required' => false,
                'label' => 'Sous total',
                'precision' => 2,
                'mapped' => false,
                'read_only' => true,
                'attr' => array('class' => 'devis-sous-total')
            ))
            ->add('remise', 'number', array(
                'required' => false,
                'label' => 'Remise',
                'precision' => 2,
                'attr' => array('class' => 'devis-remise'),
                'mapped' => false,
            ))
            ->add('taxe', 'number', array(
                'required' => false,
                'precision' => 2,
                'label_attr' => array('class' => 'hidden'),
                'attr' => array('class' => 'devis-taxe'),
                'read_only' => true,
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getTaxe() : null
            ))
            ->add('taxePercent', 'percent', array(
                'required' => false,
                'precision' => 2,
                'label' => 'TVA',
                'attr' => array('class' => 'devis-taxe-percent'),
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getTaxePercent() : null
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
            ->add('cgv', 'textarea', array(
                'required' => false,
                'label' => 'Conditions d\'utilisation',
                'mapped' => false,
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'label' => 'Commentaire',
                'mapped' => false,
                'data' => $this->devis ? $this->devis->getDescription() : null
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
