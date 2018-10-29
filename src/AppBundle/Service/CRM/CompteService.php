<?php
namespace AppBundle\Service\CRM;

use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\CRM\Compte;

/**
 * @copyright  Copyright (c) 2018
 * @author blancsebastien
 * Created on 29 oct. 2018, 10:06:17
 */
class CompteService
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Fusionner 2 comptes ensemble.
     * Le compte A est le compte à garder
     * 
     * @param Compte $compteA
     * @param Compte $compteB
     * @param array $options
     * 
     * @return bool
     */
    public function mergeComptes(Compte $compteA, Compte $compteB, array $options)
    {
        $result = false;

        return $result;
    }
}
