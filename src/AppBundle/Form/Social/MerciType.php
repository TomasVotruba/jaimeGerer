<?php

namespace AppBundle\Form\Social;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class MerciType extends AbstractType
{
    protected $companyId;
    public function __construct ($companyId = null)
    {
        $this->companyId = $companyId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $merci = $builder->getData();

        if(strtolower($merci->getType()) == "externe"){
            $builder
                ->add('fromText', 'text', array(
                    'label' => 'J\'ai été remercié par',
                    'required' => true
                ));
        } else {
             $builder
                ->add('to', 'entity', array(
                    'class'=>'AppBundle:User',
                    'required' => true,
                    'label' => 'Je dis merci à',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                        ->where('u.company = :company')
                        ->andWhere('u.enabled = :enabled')
                        ->andWhere('u.competCom = true')
                        ->orderBy('u.firstname', 'ASC')
                        ->setParameter('company', $this->companyId)
                        ->setParameter('enabled', 1);
                    },
                ));
        }

        $builder
            ->add('text', 'text', array(
                'label' => 'Pour',
                'required' => true
            ))
             ->add('submit', 'submit', array(
                'label' => 'Youpi !',
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
            'data_class' => 'AppBundle\Entity\Social\Merci'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_social_merci';
    }
}
