<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * SousTraitanceRepartition
 *
 * @ORM\Table(name="sous_traitance_repartition")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\SousTraitanceRepartitionRepository")
 */
class SousTraitanceRepartition
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\OpportuniteSousTraitance", inversedBy="repartitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $opportuniteSousTraitance;


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
     * Set date
     *
     * @param \DateTime $date
     * @return SousTraitanceRepartition
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     * @return SousTraitanceRepartition
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
     * Set opportuniteSousTraitance
     *
     * @param \AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitance
     * @return SousTraitanceRepartition
     */
    public function setOpportuniteSousTraitance(\AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitance)
    {
        $this->opportuniteSousTraitance = $opportuniteSousTraitance;

        return $this;
    }

    /**
     * Get opportuniteSousTraitance
     *
     * @return \AppBundle\Entity\CRM\OpportuniteSousTraitance
     */
    public function getOpportuniteSousTraitance()
    {
        return $this->opportuniteSousTraitance;
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
}
