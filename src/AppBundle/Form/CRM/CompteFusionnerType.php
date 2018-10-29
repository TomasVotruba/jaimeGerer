<?php
namespace AppBundle\Form\CRM;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Entity\CRM\Compte;

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
        $a = $this->compteA;
        $b = $this->compteB;

        $builder->add('nom', ChoiceType::class, [
            'choices' => [$a->getNom(), $b->getNom()],
            'expanded' => true,
        ]);
        if ($this->doDisplayField($a, $b, 'telephone')) {
            $builder->add('telephone', ChoiceType::class, [
                'choices' => [$this->compteA->getTelephone(), $this->compteB->getTelephone()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'adresse')) {
            $builder->add('adresse', ChoiceType::class, [
                'choices' => [$this->compteA->getAdresse(), $this->compteB->getAdresse()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'ville')) {
            $builder->add('ville', ChoiceType::class, [
                'choices' => [$this->compteA->getVille(), $this->compteB->getVille()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'codePostal')) {
            $builder->add('codePostal', ChoiceType::class, [
                'choices' => [$this->compteA->getCodePostal(), $this->compteB->getCodePostal()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'region')) {
            $builder->add('region', ChoiceType::class, [
                'choices' => [$this->compteA->getRegion(), $this->compteB->getRegion()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'pays')) {
            $builder->add('pays', ChoiceType::class, [
                'choices' => [$this->compteA->getPays(), $this->compteB->getPays()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'url')) {
            $builder->add('url', ChoiceType::class, [
                'choices' => [$this->compteA->getUrl(), $this->compteB->getUrl()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'fax')) {
            $builder->add('fax', ChoiceType::class, [
                'choices' => [$this->compteA->getFax(), $this->compteB->getFax()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'codeEvoliz')) {
            $builder->add('codeEvoliz', ChoiceType::class, [
                'choices' => [$this->compteA->getCodeEvoliz(), $this->compteB->getCodeEvoliz()],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'priveOrPublic')) {
            $builder->add('priveOrPublic', ChoiceType::class, [
                'choices' => [
                    $this->compteA->getPriveOrPublicToString() => $this->compteA->getPriveOrPublic(),
                    $this->compteB->getPriveOrPublicToString() => $this->compteB->getPriveOrPublic()
                ],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'compteComptableClient')) {
            $builder->add('compteComptableClient', ChoiceType::class, [
                'choices' => [
                    $this->compteA->getCompteComptableClient()->getNom() => $this->compteA->getCompteComptableClient(),
                    $this->compteB->getCompteComptableClient()->getNom() => $this->compteB->getCompteComptableClient()
                ],
                'expanded' => true,
            ]);
        }
        if ($this->doDisplayField($a, $b, 'compteComptableFournisseur')) {
            $builder->add('compteComptableFournisseur', ChoiceType::class, [
                'choices' => [
                    $this->compteA->getCompteComptableFournisseur()->getNom() => $this->compteA->getCompteComptableFournisseur(),
                    $this->compteB->getCompteComptableFournisseur()->getNom() => $this->compteB->getCompteComptableFournisseur()
                ],
                'expanded' => true,
            ]);
        }
    }

    /**
     * Return true if a given field must be displayed in the form
     * 
     * @param Compte $a
     * @param Compte $b
     * @param type $field
     * 
     * @return boolean
     */
    private function doDisplayField(Compte $a, Compte $b, $field){
        $method = 'get' . ucfirst($field);
        if(method_exists(Compte::class, $method) && $a->$method() && $b->$method() && $a->$method() !== $b->$method()){
            
            return true;
        }
        
        return false;
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
