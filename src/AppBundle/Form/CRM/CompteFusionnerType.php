<?php

namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Router;
//~ use Symfony\Component\HttpFoundation\Request;
//~ use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityRepository;

use libphonenumber\PhoneNumberFormat;

class CompteFusionnerType extends AbstractType
{
	
    /**
     * @var Router
     */
    private $router;
	protected $compteId;
	protected $id_compte_fusion;
	
	public function __construct ($contactId = null, Router $router, $id_compte_fusion)
	{
		$this->contactId = $contactId;
		$this->router = $router;
		$this->id_compte_fusion = $id_compte_fusion;
		//~ var_dump($this->request); exit;
		//~ var_dump($this->id_contact_fusion); exit;
	}
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           	->add('compte_name', 'text', array(
           			'required' => true,
           			'mapped' => false,
           			'label' => 'Choisir une organisation',
           			'attr' => array('class' => 'typeahead-compte')
           	))
           	->add('compte', 'hidden', array(
           			'required' => true,
           			'mapped' => false,
           			'attr' => array('class' => 'entity-compte')
           	))
           	->setAction($this->router->generate('crm_compte_fusionner_etape2', array('id' => $this->id_compte_fusion) ));
	
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Compte'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_crm_compte_fusionner';
    }
}
