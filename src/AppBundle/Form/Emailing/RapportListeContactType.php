<?php

namespace AppBundle\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use AppBundle\Form\CRM\RapportFilterType;
use AppBundle\Entity\CRM\RapportFilter;

class RapportListeContactType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
        		'label' => 'Nom de la liste',
            	'required' => true,
            	'attr' => array(
            		'class' => 'input-xxl'
           	 	)
        	))
            ->add('description', 'textarea', array(
        		'label' => 'Description',
            	'required' => true,
            	'attr' => array(
            		'class' => 'textarea-xxl'
            	)
        	))
        	->add('filtres', 'collection', array(
				'type' => new RapportFilterType('contact'),
				'label' => 'Filtres',
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
				'label_attr' => array('class' => 'hidden'),
				'mapped' => false
			));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Rapport'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_rapport';
    }
}
