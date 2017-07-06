<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;

class SettingsAssociationType extends AbstractType
{
	protected $num;
	protected $companyId;
	
	public function __construct ($companyId = null, $num = null)
	{
		$this->companyId = $companyId;
		$this->num = $num;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        	->add('parametre', 'text', array(
        			'required' => true,
        			'label' => 'Parametre',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	))
        	->add('module', 'text', array(
        			'required' => true,
        			'label' => 'Module',
        			'attr' => array('class' => 'hidden'),
        			'label_attr' => array('class' => 'hidden')
        	))
        	->add('compteComptable', 'entity', array(
        			'required' => false,
        			'class' => 'AppBundle:Compta\CompteComptable',
        			'label' => 'Compte comptable',
        			'label_attr' => array('class' => 'hidden'),
        			'query_builder' => function (EntityRepository $er) {
        				return $er->createQueryBuilder('c')
        				->where('c.num LIKE :num')
        				->andWhere('c.company = :company')
        				->setParameter('num', $this->num.'%')
        				->setParameter('company', $this->companyId)
        				->orderBy('c.num', 'ASC');
        			},
        	));
        	$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }
    
    public function onPreSetData(FormEvent $event){
    	$settings = $event->getData();
    	$form = $event->getForm();
    	
    	if($settings->getType() == 'TEXTE'){
    		$form->add('valeur', 'textarea', array(
    				'required' => true,
    				'label' => 'Valeur',
    				'label_attr' => array('class' => 'hidden')
    		));
    	} else if($settings->getType() == 'IMAGE'){
    		$form->add('file', 'file', array(
    				'label'	=> 'Image',
    				'required' => true
    		));
    	}
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Settings'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_settings';
    }
}
