<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class NoteFraisType extends AbstractType
{
	protected $companyId;

	public function __construct ($companyId = null)
	{
		$this->companyId = $companyId;
	}


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
  		$arr_mois = array();
  		for($i=1; $i<=12; $i++){
  			$month = str_pad($i, 2, "0", STR_PAD_LEFT);
  			$arr_mois[$month] = $month;
  		}
  		$arr_annees = array(2016 => 2016, 2017 => 2017);

        $builder
            ->add('month', 'choice', array(
	        	'label' => '',
	            'required' => true,
	            'choices' => $arr_mois,

        	))
           ->add('year', 'choice', array(
        		'label' => '',
            'required' => true,
            'choices' => $arr_annees,

        	))
        	->add('compteComptable', 'entity', array(
        			'class'=>'AppBundle:Compta\CompteComptable',
        			'property' => 'nom',
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
								->leftJoin('AppBundle:Settings', 's', 'WITH', 's.compteComptable = c.id')
        				->where('c.company = :company')
        				->andWhere('c.num LIKE :num')
								->andWhere('s.parametre LIKE :parametre')
        				->setParameter('company', $this->companyId)
        				->setParameter('num', '421%')
						->setParameter('parametre', 'COMPTE_COMPTABLE_NOTE_FRAIS')
        				->orderBy('c.nom');
        			},
        			'required' => true,
        			'label' => 'Compte comptable',
        	))
            ->add('libelle', 'text', array(
        		'required' => true,
            	'label' => 'Nom du salarié',
            	'mapped' => true
       		))
       		->add('file', 'file', array(
       				'label'	=> 'Fichier',
       				'required' => true,
       				'attr' => array('class' => 'file-upload'),
       				'mapped' => false
       		))
            ->add('submit','submit', array(
            		'label' => 'Enregistrer',
            		'attr' => array('class' => 'btn btn-success')
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\NDF\NoteFrais'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_notefrais';
    }
}
