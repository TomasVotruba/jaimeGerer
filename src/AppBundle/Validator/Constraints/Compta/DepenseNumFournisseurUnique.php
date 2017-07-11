<?php
namespace AppBundle\Validator\Constraints\Compta;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DepenseNumFournisseurUnique extends Constraint
{
    public $message = 'Ce numéro de facture fournisseur existe déjà.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'depense.num.fournisseur.unique.validator';
    }
}
