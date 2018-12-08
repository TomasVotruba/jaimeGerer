<?php
namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\CRM\Compte;
use AppBundle\Entity\Compta\CompteComptable;
use AppBundle\Service\CRM\CompteService;

class CompteFusionnerType extends AbstractType
{

    protected $compteA;
    protected $compteB;

    public function __construct(Compte $compteA, Compte $compteB)
    {
        $this->compteA = $compteA;
        $this->compteB = $compteB;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->builder = $builder;
        // Same fields then into CompteService $fieldsToCheck. However, not got them from there as we not necessary add them all the same way
        $this->addChoicesField('nom');
        $this->addChoicesField('telephone');
        $this->addChoicesField('adresse');
        $this->addChoicesField('ville');
        $this->addChoicesField('codePostal');
        $this->addChoicesField('region');
        $this->addChoicesField('pays');
        $this->addChoicesField('url');
        $this->addChoicesField('fax');
        $this->addChoicesField('codeEvoliz');
        $this->addChoicesField('priveOrPublic', 'priveOrPublicToString');

        if ($this->doDisplayField('compteComptableClient')) {
            $builder->add('compteComptableClientToKeep', EntityType::class, [
                'mapped' => false,
                'class' => CompteComptable::class,
                'choice_label' => 'nom',
                'expanded' => true,
                'constraints' => new NotNull(),
                'choices' => [
                    $this->compteA->getCompteComptableClient(),
                    $this->compteB->getCompteComptableClient(),
                ],
            ]);
        }
        if ($this->doDisplayField('compteComptableFournisseur')) {
            $builder->add('compteComptableFournisseurToKeep', EntityType::class, [
                'mapped' => false,
                'class' => CompteComptable::class,
                'choice_label' => 'nom',
                'expanded' => true,
                'constraints' => new NotNull(),
                'choices' => [
                    $this->compteA->getCompteComptableFournisseur(),
                    $this->compteB->getCompteComptableFournisseur(),
                ],
            ]);
        }
    }

    /**
     * Add a field to the form, if required
     * 
     * @param string $field
     * @param string $keyField
     */
    private function addChoicesField($field, $keyField = null)
    {
        if ($this->doDisplayField($field)) {
            $method = 'get' . ucfirst($field);
            $keyMethod = $keyField ? 'get' . ucfirst($keyField) : null;
            if (!$keyMethod || !method_exists(Compte::class, $keyMethod)) {
                $keyMethod = $method;
            }
            $this->builder->add($field, ChoiceType::class, [
                'choices' => [
                    $this->compteA->$keyMethod() => $this->compteA->$method(),
                    $this->compteB->$keyMethod() => $this->compteB->$method(),
                ],
                'expanded' => true,
                'constraints' => new NotNull(),
            ]);
        }
    }

    /**
     * Return true if a given field must be displayed in the form
     * 
     * @param string $field
     * 
     * @return boolean
     */
    private function doDisplayField($field)
    {
        return CompteService::needToChooseField($this->compteA, $this->compteB, $field);
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
