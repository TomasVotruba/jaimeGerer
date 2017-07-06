<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\CallbackTransformer;
use AppBundle\Form\DataTransformer\CompteToIdTransformer;
use AppBundle\Form\DataTransformer\SousTraitanceToIdTransformer;
use Doctrine\Common\Persistence\ObjectManager;

use AppBundle\Form\Compta\LigneDepenseType;
use Symfony\Component\Validator\Constraints\DateTime;

class DepenseType extends AbstractType
{

	protected $companyId;
	private $manager;
	private $arr_opportuniteSousTraitances;

	public function __construct($companyId = null, ObjectManager $manager, $arr_opportuniteSousTraitances = null)
	{
		$this->companyId = $companyId;
		$this->manager = $manager;
		$this->arr_opportuniteSousTraitances = $arr_opportuniteSousTraitances;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
             ->add('compte_name', 'text', array(
             		'required' => true,
             		'mapped' => false,
             		'label' => 'Fournisseur',
             		'attr' => array('class' => 'typeahead-compte', 'autocomplete' => 'off')
             ))
             ->add('compte', 'hidden', array(
             		'required' => false,
             		'attr' => array('class' => 'entity-compte')
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
             			->andWhere('s.valeur is not null')
             			->setParameter('company', $this->companyId)
             			->setParameter('module', 'CRM')
             			->setParameter('parametre', 'ANALYTIQUE');
             		},
             ))
             ->add('date', 'date', array('widget' => 'single_text',
             		'input' => 'datetime',
             		'format' => 'dd/MM/yyyy',
             		'attr' => array('class' => 'dateInput'),
             		'required' => true,
             		'label' => 'Date de la dépense'
             ))
					->add('libelle', 'text', array(
						'required' => true,
						'label' => 'Libellé'
					))
					->add('numFournisseur', 'text', array(
						'required' => false,
						'label' => 'Numéro de facture fournisseur'
					))
					->add('modePaiement', 'choice', array(
						'required' => true,
						'label' => 'Mode de paiement',
						'attr' => array('class' => 'select-mode-paiement'),
						'choices'  => array(
								'Espèce' => 'Espèce',
								'Chèque' => 'Chèque',
								'Virement' => 'Virement',
								'Paypal' => 'Paypal',
								'CB' => 'CB',
								'Prélèvement' => 'Prélèvement'
						),
					))
					->add('conditionReglement','choice', array(
						'required' => true,
						'label' => 'Condition de règlement',
						'attr' => array('class' => 'select-condition-reglement'),
						'choices'  => array(
							'reception' => 'A réception',
							'30' => '30 jours',
							'30finMois' => '30 jours fin de mois',
							'45' => '45 jours',
							'45finMois' => '30 jours fin de mois',
							'60' => '60 jours',
							'60finMois' => '60 jours fin de mois'
						),
					))
					->add('numCheque', 'text', array(
						'required' => false,
						'label' => 'Numéro du chèque',
						'attr' => array('class' => 'input-num-cheque'),
					))
					->add('lignes', 'collection', array(
		             		'type' => new LigneDepenseType($this->companyId),
		             		'allow_add' => true,
		             		'allow_delete' => true,
		             		'by_reference' => false,
		             		'label_attr' => array('class' => 'hidden')
		             ))
					->add('taxe', 'number', array(
						'required' => false,
						'precision' => 2,
						//'label_attr' => array('class' => 'hidden'),
						'attr' => array('class' => 'depense-taxe'),
						'read_only' => true,
						'label' => 'TVA'
					))
					->add('totalHT', 'number', array(
						'required' => false,
						'label' => 'Total HT',
						'precision' => 2,
						'mapped' => false,
						'read_only' => true,
						'attr' => array('class' => 'depense-total-ht')
					))
					->add('totalTTC', 'number', array(
						'required' => false,
						'label' => 'Total TTC',
						'precision' => 2,
						'mapped' => false,
						'read_only' => true,
						'attr' => array('class' => 'depense-total-ttc')
					))
					->add('opportuniteSousTraitances', 'entity', array(
						 'class'=> 'AppBundle\Entity\CRM\opportuniteSousTraitance',
						 'required' => false,
						 'label' => 'Sous-traitance',
						 'property' => 'NomEtMontant',
						 'choices' => $this->arr_opportuniteSousTraitances,
						 'multiple' => true,
						 'expanded' => false,
						 'attr' => array('class' => 'select-sous-traitances'),
						 'mapped' => false

					))
        ;

		$builder->get('compte')
            ->addModelTransformer(new CompteToIdTransformer($this->manager));

		// $builder->get('opportuniteSousTraitance')
		// 	->addModelTransformer(new SousTraitanceToIdTransformer($this->manager));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\Depense'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_depense';
    }
}
