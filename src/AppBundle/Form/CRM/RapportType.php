<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use AppBundle\Form\CRM\RapportFilterType;

class RapportType extends AbstractType
{

    protected $type;

    public function __construct ($type)
    {
        $this->type = $type;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
        		'label' => 'Nom du rapport',
            	'required' => true,
            	'attr' => array(
            		'class' => 'input-xxl'
           	 	)
        	))
            ->add('description', 'textarea', array(
        		'label' => 'Description',
            	'required' => false,
            	'attr' => array(
            		'class' => 'textarea-xxl'
            	)
        	))
            ->add('filtres', 'collection', array(
                'type' => new RapportFilterType($this->type),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label_attr' => array('class' => 'hidden'),
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
