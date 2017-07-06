<?php
namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\CRM\Compte;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CompteToIdTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (compte) to a string (id).
     *
     * @param  compte|null $compte
     * @return string
     */
    public function transform($compte)
    {
        if (null === $compte) {
            return '';
        }

        return $compte->getId();
    }

    /**
     * Transforms a string (id) to an object (compte).
     *
     * @param  string $id
     * @return compte|null
     * @throws TransformationFailedException if object (compte) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        $compte = $this->manager
            ->getRepository('AppBundle:CRM\Compte')
            ->find($id)
        ;

        if (null === $compte) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Aucun compte pour l\'id "%s"',
                $id
            ));
        }

        return $compte;
    }
}
