<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrevTreso
 *
 * @ORM\Table(name="prev_treso")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\PrevTresoRepository")
 */
class PrevTreso
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
     * @ORM\Column(name="mois", type="integer", nullable=false)
     */
    private $mois;

    /**
     * @var integer
     *
     * @ORM\Column(name="annee", type="integer", nullable=false)
     */
    private $annee;

    /**
     * @var float
     *
     * @ORM\Column(name="montant", type="float", nullable=false)
     */
    private $montant;
    
    /**
     * @var string
     *
     * @ORM\Column(name="poste", type="string", nullable=false)
     */
    private $poste;
    
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
     * Set mois
     *
     * @param integer $mois
     * @return PrevTreso
     */
    public function setMois($mois)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get mois
     *
     * @return integer 
     */
    public function getMois()
    {
        return $this->mois;
    }

    /**
     * Set annee
     *
     * @param integer $annee
     * @return PrevTreso
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return integer 
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set montant
     *
     * @param float $montant
     * @return PrevTreso
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
     * @return PrevTreso
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

    /**
     * Set poste
     *
     * @param string $poste
     * @return PrevTreso
     */
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste
     *
     * @return string 
     */
    public function getPoste()
    {
        return $this->poste;
    }
}
