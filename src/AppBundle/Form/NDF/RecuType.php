<?php

namespace AppBundle\Form\NDF;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class RecuType extends AbstractType
{
    protected $companyId;
    protected $fc;
    protected $ccDefaut;
    protected $deplacementVoiture;
    protected $user;

    public function __construct ($companyId = null, $fc = null, $ccDefaut = null, $deplacementVoiture = false, $user = null)
    {
        $this->companyId = $companyId;
        $this->fc = $fc;
        $this->ccDefaut = $ccDefaut;
        $this->deplacementVoiture = $deplacementVoiture;
        $this->user = $user;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = $builder->getData();
     
        $builder
            ->add('projet_name', 'text', array(
                'required' => false,
                'mapped' => false,
                'label' => 'Projet (numéro de bon de commande, nom du projet ou du client)',
                'attr' => array('class' => 'typeahead-projet', 'autocomplete' => 'off')
            ))
            ->add('projet_entity', 'hidden', array(
                'required' => false,
                'attr' => array('class' => 'entity-projet'),
                'mapped' => false
            ))
            ->add('refacturable', 'checkbox', array(
                'label' => ' ',
                'required' => false,
                'attr' => array(
                    'data-toggle' => 'toggle',
                    'data-on' => 'Oui',
                    'data-off' => 'Non',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                    'data-size' => 'small',
                    'class' => 'toggle-refacturable'
                ),
            ))
            ->add('montantHT', 'number', array(
               'required' => true,
               'label' => 'Montant HT (€)',
               'precision' => 2,
               'attr' => array('class' => 'montant-ht')
             ))
           ->add('tva', 'number', array(
              'required' => true,
              'label' => 'TVA (€)',
              'precision' => 2,
              'attr' => array('class' => 'montant-tva')
            ))
            ->add('montantTTC', 'number', array(
               'required' => true,
               'label' => 'Montant TTC (€)',
               'precision' => 2,
               'attr' => array('class' => 'montant-ttc')
             ))
            ->add('analytique', 'entity', array(
       			'class'=>'AppBundle:Settings',
       			'required' => true,
       			'label' => 'Analytique',
       			'query_builder' => function (EntityRepository $er) {
       				return $er->createQueryBuilder('s')
       				     ->where('s.company = :company')
       				     ->andWhere('s.parametre = :parametre')
       				     ->setParameter('company', $this->companyId)
       				     ->setParameter('parametre', 'analytique')
                        ->orderBy('s.valeur', 'ASC');
       			},
                'data' => $this->fc
           	))
            ->add('compteComptable', 'entity', array(
                'class'=>'AppBundle:Compta\CompteComptable',
                'required' => true,
                'label' => 'Compte comptable',
                'property' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                      ->where('c.company = :company')
                      ->andWhere('c.num LIKE :num')
                      ->setParameter('company', $this->companyId)
                      ->setParameter('num', '6%')
                      ->orderBy('c.nom', 'ASC');
                },
                'data' => $data->getId() ? $data->getCompteComptable() : $this->ccDefaut
            ))
            ->add('save', 'submit', array(
              'label' => 'Enregistrer et revenir à la liste des reçus',
            ));

            if($this->deplacementVoiture === false){
                $builder->add('fournisseur', 'text', array(
                    'label' => 'Fournisseur',
                    'required' => true
                ))
                ->add('date', 'date', array('widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'dateInput', 'autocomplete' => 'off'),
                    'required' => true,
                    'label' => 'Date du reçu'
                ));
            } else {
                $builder->add('trajet', 'text', array(
                    'label' => 'Trajet',
                    'required' => true
                ))
                ->add('date', 'date', array('widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'dateInput', 'autocomplete' => 'off'),
                    'required' => true,
                    'label' => 'Date du trajet'
                ))
                ->add('distance', 'integer', array(
                    'label' => 'Distance (km)',
                    'required' => true
                ))
                ->add('marqueVoiture', 'text', array(
                    'label' => 'Marque du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getMarqueVoiture() : $this->user->getMarqueVoiture()
                ))
                ->add('modeleVoiture', 'text', array(
                    'label' => 'Modèle du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getModeleVoiture() : $this->user->getModeleVoiture()
                ))
                ->add('immatriculationVoiture', 'text', array(
                    'label' => 'Immatriculation du véhicule',
                    'required' => true,
                    'data' => $data->getId() ? $data->getImmatriculationVoiture() : $this->user->getImmatriculationVoiture()
                ))
                 ->add('puissanceVoiture', 'choice', array(
                    'required' => true,
                    'label' => 'Puissance fiscale du véhicule',
                    'choices' => array(
                        3 => '3 CV',
                        4 => '4 CV',
                        5 => '5 CV',
                        6 => '6 CV',
                        7 => '7 CV et plus'
                    ),
                    'data' => $data->getId() ? $data->getPuissanceVoiture() : $this->user->getPuissanceVoiture()
                ))
                ;
            }
           
          
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\NDF\Recu'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_ndf_recu';
    }
}
