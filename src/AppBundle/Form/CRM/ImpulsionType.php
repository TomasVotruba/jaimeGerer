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
        $builder
            ->add('user', 'entity', array(
           			'class'=>'AppBundle:User',
           			'required' => true,
           			'label_attr' => array('class' => 'hidden'),
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
             ->add('contact_name', 'text', array(
                    'required' => true,
                    'mapped' => false,
                    'label' => 'doit contacter',
                    'attr' => array('class' => 'typeahead-contact'),
            ))
           	->add('contact', 'hidden', array(
           			'required' => true,
           			'attr' => array('class' => 'entity-contact'),
           	))
           	->add('date', 'date', array(
                'widget' => 'single_text',
                'label' => 'Le',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'dateInput'),
                'required' => true,
            ))
           	->add('methodeContact', 'text', array(
                'label' => 'par',
                'required' => true
            ))
            ->add('infos', 'textarea', array(
                'label' => 'au sujet de',
                'required' => true
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
