<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LigneDepense
 *
 * @ORM\Table(name="ligne_avoir")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\LigneAvoirRepository")
 */
class LigneAvoir
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
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var float
     *
     * @ORM\Column(name="montant", type="float")
     */
    private $montant;
    
  	 /**
     * @var float
     *
     * @ORM\Column(name="taxe", type="float", nullable=true)
     */
    private $taxe;
    

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Avoir", inversedBy="lignes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $avoir;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;
    

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
     * Set nom
     *
     * @param string $nom
     * @return LigneAvoir
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set montant
     *
     * @param float $montant
     * @return LigneAvoir
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
     * Set taxe
     *
     * @param float $taxe
     * @return LigneAvoir
     */
    public function setTaxe($taxe)
    {
        $this->taxe = $taxe;

        return $this;
    }

    /**
     * Get taxe
     *
     * @return float 
     */
    public function getTaxe()
    {
        return $this->taxe;
    }

    /**
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return LigneAvoir
     */
    public function setAvoir(\AppBundle\Entity\Compta\Avoir $avoir)
    {
        $this->avoir = $avoir;

        return $this;
    }

    /**
     * Get avoir
     *
     * @return \AppBundle\Entity\Compta\Avoir 
     */
    public function getAvoir()
    {
        return $this->avoir;
    }



    /**
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return LigneAvoir
     */
    public function setCompteComptable(\AppBundle\Entity\Compta\CompteComptable $compteComptable)
    {
        $this->compteComptable = $compteComptable;

        return $this;
    }

    /**
     * Get compteComptable
     *
     * @return \AppBundle\Entity\Compta\CompteComptable 
     */
    public function getCompteComptable()
    {
        return $this->compteComptable;
    }
    
    public function getTotal(){
    	return $this->montant+$this->taxe;
    }
}
