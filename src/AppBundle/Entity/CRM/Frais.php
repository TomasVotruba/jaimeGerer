<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Frais
 *
 * @ORM\Table(name="frais")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\FraisRepository")
 */
class Frais
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="frais")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $actionCommerciale;

    /**
     * @var float
     *
     * @ORM\Column(name="montantHT", type="float", nullable=true)
     */
    private $montantHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tva", type="float", nullable=true)
     */
    private $tva;

    /**
     * @var float
     *
     * @ORM\Column(name="montantTTC", type="float", nullable=true)
     */
    private $montantTTC;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CRM\Produit", mappedBy="frais")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $produit;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;


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
     * @return Frais
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
     * Set description
     *
     * @param string $description
     * @return Frais
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Settings $type
     * @return Frais
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
     * Set actionCommerciale
     *
     * @param \AppBundle\Entity\CRM\Opportunite $actionCommerciale
     * @return Frais
     */
    public function setActionCommerciale(\AppBundle\Entity\CRM\Opportunite $actionCommerciale = null)
    {
        $this->actionCommerciale = $actionCommerciale;

        return $this;
    }

    /**
     * Get actionCommerciale
     *
     * @return \AppBundle\Entity\CRM\Opportunite 
     */
    public function getActionCommerciale()
    {
        return $this->actionCommerciale;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return Frais
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;

        return $this;
    }

    /**
     * Get montantHT
     *
     * @return float 
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set tva
     *
     * @param float $tva
     * @return Frais
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return float 
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set montantTTC
     *
     * @param float $montantTTC
     * @return Frais
     */
    public function setMontantTTC($montantTTC)
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }

    /**
     * Get montantTTC
     *
     * @return float 
     */
    public function getMontantTTC()
    {
        return $this->montantTTC;
    }

    public function __toString(){
        return $this->nom.' - '.$this->montantHT.' € HT';
    }

    /**
     * Set produit
     *
     * @param \AppBundle\Entity\CRM\Produit $produit
     * @return Frais
     */
    public function setProduit(\AppBundle\Entity\CRM\Produit $produit = null)
    {
        $this->produit = $produit;

        return $this;
    }

    /**
     * Get produit
     *
     * @return \AppBundle\Entity\CRM\Produit 
     */
    public function getProduit()
    {
        return $this->produit;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Frais
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
}
