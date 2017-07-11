<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompteImportType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       	$builder ->add('file', 'file', array(
						'label'	=> 'Fichier',
						'required' => true,
						'attr' => array('class' => 'file-upload')
					))
					->add('submit','submit', array(
						'label' => 'Suite',
						'attr' => array('class' => 'btn btn-success')
					));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_compteimport';
    }
}
