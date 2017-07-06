<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Produit
 *
 * @ORM\Table(name="produit")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\ProduitRepository")
 */
class Produit
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
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var float
     *
     * @ORM\Column(name="tarifUnitaire", type="float")
     * @Assert\NotBlank()
     */
    private $tarifUnitaire;

    /**
     * @var float
     *
     * @ORM\Column(name="quantite", type="float")
     * @Assert\NotBlank()
     */
    private $quantite;

    /**
     * @var float
     *
     * @ORM\Column(name="remise", type="float", nullable=true)
     */
    private $remise;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="produits")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank()
     */
    private $documentPrix;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $type;


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
     * @return Produit
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
     * @return Produit
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
     * Set tarifUnitaire
     *
     * @param float $tarifUnitaire
     * @return Produit
     */
    public function setTarifUnitaire($tarifUnitaire)
    {
        $this->tarifUnitaire = $tarifUnitaire;

        return $this;
    }

    /**
     * Get tarifUnitaire
     *
     * @return float 
     */
    public function getTarifUnitaire()
    {
        return $this->tarifUnitaire;
    }

    /**
     * Set quantite
     *
     * @param float $quantite
     * @return Produit
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Get quantite
     *
     * @return float 
     */
    public function getQuantite()
    {
        return $this->quantite;
    }

    /**
     * Set remise
     *
     * @param float $remise
     * @return Produit
     */
    public function setRemise($remise)
    {
        $this->remise = $remise;

        return $this;
    }

    /**
     * Get remise
     *
     * @return float 
     */
    public function getRemise()
    {
        return $this->remise;
    }

    /**
     * Set type
     *
     * @param \AppBundle\Entity\Settings $type
     * @return Produit
     */
    public function setType(\AppBundle\Entity\Settings $type)
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
    
    public function getMontant(){
    	return round($this->tarifUnitaire*$this->quantite, 2);
    }
    
    public function getTotal(){
    	return round($this->tarifUnitaire*$this->quantite-$this->remise, 2);
    }

    /**
     * Set documentPrix
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $documentPrix
     * @return Produit
     */
    public function setDocumentPrix(\AppBundle\Entity\CRM\DocumentPrix $documentPrix)
    {
        $this->documentPrix = $documentPrix;
        return $this;
    }

    /**
     * Get documentPrix
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getDocumentPrix()
    {
        return $this->documentPrix;
    }
    
    public function __clone() {
    	if ($this->id) {
    		$this->setId(null);
    	}
    }
    
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
}
