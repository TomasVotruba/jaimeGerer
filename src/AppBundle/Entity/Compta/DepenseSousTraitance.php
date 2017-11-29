<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepenseSousTraitance
 *
 * @ORM\Table(name="depense_sous_traitance")
 * @ORM\Entity
 */
class DepenseSousTraitance
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer", nullable=true)
     */
    private $montant;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", inversedBy="sousTraitances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $depense;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\OpportuniteSousTraitance", inversedBy="depenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sousTraitance;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     * @return DepenseSousTraitance
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return integer 
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Get montant monetaire
     *
     * @return float
     */
    public function getMontantMonetaire()
    {
        return $this->montant/100;
    }

    /**
     * Get montant montaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
        return $this->montant = $montant*100;
    }


    /**
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return DepenseSousTraitance
     */
    public function setDepense(\AppBundle\Entity\Compta\Depense $depense)
    {
        $this->depense = $depense;

        return $this;
    }

    /**
     * Get depense
     *
     * @return \AppBundle\Entity\Compta\Depense 
     */
    public function getDepense()
    {
        return $this->depense;
    }

    /**
     * Set sousTraitance
     *
     * @param \AppBundle\Entity\CRM\OpportuniteSousTraitance $sousTraitance
     * @return DepenseSousTraitance
     */
    public function setSousTraitance(\AppBundle\Entity\CRM\OpportuniteSousTraitance $sousTraitance)
    {
        $this->sousTraitance = $sousTraitance;

        return $this;
    }

    /**
     * Get sousTraitance
     *
     * @return \AppBundle\Entity\CRM\OpportuniteSousTraitance 
     */
    public function getSousTraitance()
    {
        return $this->sousTraitance;
    }
}
