<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * SoldeCompteBancaire
 *
 * @ORM\Table(name="solde_compte_bancaire")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\SoldeCompteBancaireRepository")
 */
class SoldeCompteBancaire
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
     * @var float
     *
     * @ORM\Column(name="montant", type="float")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteBancaire")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteBancaire;

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
     * @return SoldeCompteBancaire
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
     * @param float $montant
     * @return SoldeCompteBancaire
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return float 
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set compteBancaire
     *
     * @param \AppBundle\Entity\Compta\CompteBancaire $compteBancaire
     * @return SoldeCompteBancaire
     */
    public function setCompteBancaire(\AppBundle\Entity\Compta\CompteBancaire $compteBancaire)
    {
        $this->compteBancaire = $compteBancaire;

        return $this;
    }

    /**
     * Get compteBancaire
     *
     * @return \AppBundle\Entity\Compta\CompteBancaire 
     */
    public function getCompteBancaire()
    {
        return $this->compteBancaire;
    }
    
    public function __toString(){
    	return $this->getMontant();
    }
    
}
