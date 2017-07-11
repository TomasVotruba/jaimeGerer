<?php
namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Compta\OpportuniteSousTraitance;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SousTraitanceToIdTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object ($sousTraitance) to a string (id).
     *
     * @param  OpportuniteSousTraitance|null $sousTraitance
     * @return string
     */
    public function transform($sousTraitance)
    {
        if (null === $sousTraitance) {
            return '';
        }

        return $sousTraitance->getId();
    }

    /**
     * Transforms a string (id) to an object (sousTraitance).
     *
     * @param  string $id
     * @return OpportuniteSousTraitance|null
     * @throws TransformationFailedException if object (sousTraitance) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        $sousTraitance = $this->manager
            ->getRepository('AppBundle:Compta\OpportuniteSousTraitance')
            ->find($id)
        ;

        if (null === $sousTraitance) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Aucune OpportuniteSousTraitance pour l\'id "%s"',
                $id
            ));
        }

        return $sousTraitance;
    }
}
