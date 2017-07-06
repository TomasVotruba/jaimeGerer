<?php

namespace AppBundle\Form\Compta;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UploadHistoriqueDepenseType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$arr_formats = array(
    			'd/m/Y' => 'jour/mois/année (4 chiffres)',
    			'd/m/y'=> 'jour/mois/année (2 chiffres)',
    			'd-m-Y' => 'jour-mois-année (4 chiffres)',
    			'd-m-y'=> 'jour-mois-année (2 chiffres)',
    	);
    	 
       	$builder ->add('file', 'file', array(
						'label'	=> 'Fichier',
						'required' => true,
						'attr' => array('class' => 'file-upload')
					))
					->add('dateFormat', 'choice', array(
							'label'	=> 'Format des dates du fichier importé',
							'required' => true,
							'choices' => $arr_formats
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
        return 'appbundle_compta_uploadhistoriquedepense';
    }
}
