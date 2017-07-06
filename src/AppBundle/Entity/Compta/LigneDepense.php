<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LigneDepense
 *
 * @ORM\Table(name="ligne_depense")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\LigneDepenseRepository")
 */
class LigneDepense
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", inversedBy="lignes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\NDF\Recu")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $recu;

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
     * @return LigneDepense
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
     * @return LigneDepense
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

  	public function getTVA(){
  		return $this->taxe;
  	}


    /**
     * Set type
     *
     * @param \AppBundle\Entity\Settings $type
     * @return LigneDepense
     */
    public function setType(\AppBundle\Entity\Settings $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \AppBundle\Entity\Settings
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return LigneDepense
     */
    public function setAnalytique(\AppBundle\Entity\Settings $analytique = null)
    {
        $this->analytique = $analytique;

        return $this;
    }

    /**
     * Get analytique
     *
     * @return \AppBundle\Entity\Settings
     */
    public function getAnalytique()
    {
        return $this->analytique;
    }


    /**
     * Set taxe
     *
     * @param float $taxe
     * @return LigneDepense
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
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return LigneDepense
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
    	return $this->getMontant() + $this->getTVA();
    }


    public function __toString(){
      return $this->nom.' '.$this->montant;
    }

    /**
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return LigneDepense
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
     * Set recu
     *
     * @param \AppBundle\Entity\NDF\Recu $recu
     * @return LigneDepense
     */
    public function setRecu(\AppBundle\Entity\NDF\Recu $recu = null)
    {
        $this->recu = $recu;
        $recu->setLigneDepense($this);

        return $this;
    }

    /**
     * Get recu
     *
     * @return \AppBundle\Entity\NDF\Recu
     */
    public function getRecu()
    {
        return $this->recu;
    }
}
