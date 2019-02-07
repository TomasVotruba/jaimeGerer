<?php

namespace AppBundle\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampagneDateEnvoiType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateEnvoi', 'datetime', array(
                'required' => true,
                'label' => 'Date d\'envoi',
                'input' => 'datetime',
                'widget' => 'choice',
                'minutes' => array(0,15,30,45),
                'years' => range(date('Y'), date('Y')+1),
            ))
        ;

    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Emailing\Campagne'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_emailing_campagne_dateenvoi';
    }

}
