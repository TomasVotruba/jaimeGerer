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
        $arr_mois = array(
          1 => 'Janvier',
          2 => 'Février',
          3 => 'Mars',
          4 => 'Avril',
          5 => 'Mai',
          6 => 'Juin',
          7 => 'Juillet',
          8 => 'Août',
          9 => 'Septembre',
          10 => 'Octobre',
          11 => 'Novembre',
          12 => 'Décembre'
        );
        $arr_annees = array(2016 => 2016, 2017 => 2017);

        $builder
          ->add('month', 'choice', array(
            'label' => '',
            'required' => true,
            'choices' => $arr_mois,
            'data' => $ndf->getMonth() ? $ndf->getMonth() : date('m', mktime(0, 0, 0, date('m')-1, 1, date('Y')))
          ))
         ->add('year', 'choice', array(
            'label' => '',
            'required' => true,
            'choices' => $arr_annees,
            'data' => date('Y')
          ))
          ->add('recus', 'choice', array(
            'mapped' => false,
            'label' => 'Choisir les reçus',
            'choices' => $this->arr_recus,
            'multiple' => true,
            'attr' => array('class' => 'select-recus'),
            'data' => $ndf->getRecusId()
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
