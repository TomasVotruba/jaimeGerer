<?php

namespace AppBundle\Form\Emailing;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CampagneDestinatairesType extends AbstractType
{
    private $company;
    public function __construct($company){
        $this->company = $company;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rapport', 'entity', array(
                'class'=>'AppBundle:CRM\Rapport',
                'property' => 'nom',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                    ->where('r.type = :type')
                    ->andWhere('r.module = :module')
                    ->andWhere('r.company = :company')
                    ->orderBy('r.nom', 'ASC')
                    ->setParameter('type', 'contact')
                    ->setParameter('module', 'CRM')
                    ->setParameter('company', $this->company);
                },
                'required' => true,
                'label' => 'Rapport',
                'mapped' => false,
                'attr' => array('class' => 'rapport-select')
            ))
            ->add('submit', 'submit', array(
                'label' => 'Suite',
                'attr' => array('class' => 'btn btn-success submit', 'disabled' => true)
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
        return 'appbundle_emailing_campagne_destinataires';
    }

}
