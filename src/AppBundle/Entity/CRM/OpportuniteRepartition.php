<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * OpportuniteRepartition
 *
 * @ORM\Table(name="opportunite_repartition")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\OpportuniteRepartitionRepository")
 */
class OpportuniteRepartition
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
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="opportuniteRepartitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $opportunite;


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
     * @return OpportuniteRepartition
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
     * @return OpportuniteRepartition
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
     * Set opportunite
     *
     * @param \AppBundle\Entity\CRM\Opportunite $opportunite
     * @return OpportuniteRepartition
     */
    public function setOpportunite(\AppBundle\Entity\CRM\Opportunite $opportunite)
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    /**
     * Get opportunite
     *
     * @return \AppBundle\Entity\CRM\Opportunite
     */
    public function getOpportunite()
    {
        return $this->opportunite;
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
