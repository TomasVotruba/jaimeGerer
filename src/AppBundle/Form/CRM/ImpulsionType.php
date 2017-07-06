<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class ImpulsionType extends AbstractType
{
	protected $userId;
	protected $companyId;
	
	public function __construct ($userId = null, $companyId = null)
	{
		$this->userId = $userId;
		$this->companyId = $companyId;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	
    	$arr_delaiNum = array();
    	for($i=1; $i<13; $i++){
    		$arr_delaiNum[$i] = $i;
    	}
    	
    	$arr_delaiUnit= array(
    			'DAY' => 'jours', 
    			'WEEK' => 'semaines', 
    			'MONTH' => 'mois');
    	
        $builder
           	 ->add('contact_name', 'text', array(
           			'required' => true,
           			'mapped' => false,
           			'label' => 'Contact',
           			'attr' => array('class' => 'typeahead-contact'),
           	))
            ->add('user', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label' => 'Gestionnaire du suivi',
           			'query_builder' => function (EntityRepository $er) {
           				return $er->createQueryBuilder('u')
           				->where('u.company = :company')
           				->andWhere('u.enabled = :enabled')
           				->orWhere('u.id = :id')
           				->orderBy('u.firstname', 'ASC')
           				->setParameter('company', $this->companyId)
           				->setParameter('enabled', 1)
           				->setParameter('id', $this->userId);
           			},
           	))
           	->add('contact', 'hidden', array(
           			'required' => true,
           			'attr' => array('class' => 'entity-contact'),
           	))
           	->add('delaiNum', 'integer', array(
           		'label' => 'Contacter tous les',
           		'required' => true,
     		))
           	->add('delaiUnit', 'choice', array(
           			'choices' => $arr_delaiUnit,
           			'label_attr' => array('class' => 'invisible'),
           			'required' => true,
           	));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Impulsion'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_impulsion';
    }
}
