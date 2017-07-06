<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use AppBundle\Form\Compta\LigneAvoirType;
use AppBundle\Entity\CRM\DocumentPrixRepository;

class AvoirType extends AbstractType
{

	protected $userGestionId;
	protected $companyId;
	protected $type;

	public function __construct ($userGestionId = null, $companyId = null, $type = null)
	{
		$this->userGestionId = $userGestionId;
		$this->companyId = $companyId;
		$this->type = $type;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
              ->add('objet', 'text', array(
        		'required' => true,
            	'label' => 'Objet'
        	))
            ->add('dateValidite', 'date', array('widget' => 'single_text',
        			'input' => 'datetime',
        			'format' => 'dd/MM/yyyy',
        			'attr' => array('class' => 'dateInput'),
        			'required' => true,
        			'label' => 'Date de validité'
        	))
             ->add('lignes', 'collection', array(
             		'type' => new LigneAvoirType($this->companyId, $this->type),
             		'allow_add' => true,
             		'allow_delete' => true,
             		'by_reference' => false,
             		'label_attr' => array('class' => 'hidden')
             ))
           ->add('userGestion', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label' => 'Gestionnaire de l\'avoir',
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
           	));

             if($this->type == 'CLIENT'){
             	$builder->add('facture', 'entity', array(
           			'class'=> 'AppBundle\Entity\CRM\DocumentPrix',
           			'required' => true,
           			'label' => 'Facture',
           			'attr' => array('class' => 'select-piece'),
           			'query_builder' => function(EntityRepository $er) {
										return $er->findNoRapprochement($this->companyId,true);
           			}
							));
             } else {
             	$builder->add('depense', 'entity', array(
             			'class'=> 'AppBundle\Entity\Compta\Depense',
             			'required' => true,
             			'label' => 'Dépense',
             			'attr' => array('class' => 'select-piece'),
             			'query_builder' => function(EntityRepository $er) {
										return $er->findNoRapprochement($this->companyId);
             			}
             	));
             }

        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Compta\Avoir'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_compta_avoir';
    }
}
