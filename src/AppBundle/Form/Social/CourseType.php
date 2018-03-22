<?php

namespace AppBundle\Form\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CourseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text', array(
                'label' => 'J\'ai besoin de ',
                'required' => true
            ))
            ->add('quantite', 'text', array(
                'label' => 'Quantité',
                'required' => true
            ))
            ->add('submit', 'submit', array(
                'label' => 'Valider',
                'attr' => array('class' => 'btn btn-success'),
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Social\Course'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_social_course';
    }
}
