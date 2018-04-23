<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ActionCommercialeUserCompetComType extends AbstractType
{

    private $actionCommerciale;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->actionCommerciale = $builder->getData();

        $builder
            ->add('userCompetCom', 'entity', array(
                'class'=>'AppBundle:User',
                'required' => true,
                'label' => 'A qui revient cette affaire ?',
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u')
                    ->where('u.company = :company')
                    ->andWhere('u.enabled = :enabled')
                    ->andWhere('u.competCom = true')
                    ->orWhere('u.id = :id')
                    ->orderBy('u.firstname', 'ASC')
                    ->setParameter('company', $this->actionCommerciale->getUserCreation()->getCompany())
                    ->setParameter('enabled', 1)
                    ->setParameter('id', $this->actionCommerciale->getUserCompetCom());
                },
            ))
             ->add('submit', 'submit', array(
                'label' => 'Valider',
                'attr' => array('class' => 'btn btn-success'),
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Opportunite'
        ));

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_opportunite';
    }
}
