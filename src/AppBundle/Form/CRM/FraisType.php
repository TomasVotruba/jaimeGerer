<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class FraisType extends AbstractType
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
        $builder
           ->add('type', 'entity', array(
                'class'=>'AppBundle:Settings',
                'property' => 'valeur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                    ->where('s.parametre = :parametre')
                    ->andWhere('s.company = :company')
                    ->andWhere('s.module = :module')
                    ->setParameter('parametre', 'TYPE_PRODUIT')
                    ->setParameter('company', $this->companyId)
                    ->setParameter('module', 'CRM');
                },
                'required' => false,
                'label' => 'Type',
                'attr' => array('class' => 'produit-type')
            ))
            ->add('nom', 'text', array(
        		'required' => true,
            	'label' => 'Nom',
        	))
            ->add('description', 'textarea', array(
    			'required' => 'true',
            	'label' => 'Description'
        	))
            ->add('montantHT', 'number', array(
               'required' => true,
               'label' => 'Montant HT (€)',
               'precision' => 2,
               'attr' => array('class' => 'montant-ht')
             ))
           ->add('tva', 'number', array(
                'required' => true,
                'label' => 'TVA (€)',
                'precision' => 2,
                'attr' => array('class' => 'montant-tva')
            ))
            ->add('montantTTC', 'number', array(
                'required' => true,
                'label' => 'Montant TTC (€)',
                'precision' => 2,
                'attr' => array('class' => 'montant-ttc')
             ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Frais',
            'type' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_frais';
    }
}
