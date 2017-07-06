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

class ContactFusionnerType extends AbstractType
{
	
    /**
     * @var Router
     */
    private $router;
	protected $contactId;
	protected $id_contact_fusion;
	
	public function __construct ($contactId = null, Router $router, $id_contact_fusion)
	{
		$this->contactId = $contactId;
		$this->router = $router;
		$this->id_contact_fusion = $id_contact_fusion;
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
            //~ ->add('contact', 'shtumi_ajax_autocomplete', array(
            		//~ 'entity_alias'=>'contacts',
            		//~ 'required' => true,
            		//~ 'label' => 'Choisir un contact',
            		//~ 'mapped' => false
           	//~ ))
           	->add('contact_name', 'text', array(
           			'required' => true,
           			'mapped' => false,
           			'label' => 'Choisir un contact',
           			'attr' => array('class' => 'typeahead-contact')
           	))
           	->add('contact', 'hidden', array(
           			'required' => true,
           			'mapped' => false,
           			'attr' => array('class' => 'entity-contact')
           	))
           	->setAction($this->router->generate('crm_contact_fusionner_etape2', array('id' => $this->id_contact_fusion) ));
	
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\CRM\Contact'
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
