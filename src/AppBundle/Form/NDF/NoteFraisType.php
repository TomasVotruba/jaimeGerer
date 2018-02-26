<?php

namespace AppBundle\Form\NDF;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class NoteFraisType extends AbstractType
{
    protected $arr_recus;

    public function __construct ($arr_recus = null)
    {
      $this->arr_recus = $arr_recus;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ndf = $builder->getData();

        $builder
          ->add('recus', 'choice', array(
            'mapped' => false,
            'label' => 'Choisir les reçus',
            'choices' => $this->arr_recus,
            'multiple' => true,
            'attr' => array('class' => 'select-recus'),
            'data' => $ndf->getRecusId()
          ))
          ->add('signatureEmploye', 'checkbox', array(
              'label'    => 'Je certifie que ces informations sont exactes et signe la note de frais.',
              'required' => false,
          ))
          ->add('draft', 'submit', array(
            'label' => 'Enregistrer comme brouillon'
          ))
          ->add('validate', 'submit', array(
            'label' => 'Enregistrer et transmettre à la compta'
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
        return 'appbundle_ndf_notefrais';
    }
}
