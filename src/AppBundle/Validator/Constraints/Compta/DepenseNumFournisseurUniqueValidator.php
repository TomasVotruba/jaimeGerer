<?php
namespace AppBundle\Validator\Constraints\Compta;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\ORM\EntityManager;

class DepenseNumFournisseurUniqueValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
  	{
  	    $this->entityManager = $entityManager;
  	}

    public function validate($depense, Constraint $constraint)
    {
      if($depense->getNumFournisseur()){
        $repo = $this->entityManager->getRepository('AppBundle:Compta\Depense');
        $arr_existing = $repo->findByNumFournisseurAndCompany($depense->getNumFournisseur(), $depense->getUserCreation()->getCompany());

        if(count($arr_existing) > 0) {
          $this->context
            ->buildViolation($constraint->message)
            ->atPath('foo')
            ->addViolation();
        }
      }
  	}

}
