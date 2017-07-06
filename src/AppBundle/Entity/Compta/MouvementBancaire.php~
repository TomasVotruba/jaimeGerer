<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * MouvementBancaire
 *
 * @ORM\Table(name="mouvement_bancaire")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\MouvementBancaireRepository")
 */
class MouvementBancaire
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
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteBancaire")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteBancaire;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="mouvementBancaire")
    */
    private $rapprochements;

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
     * @return MouvementBancaire
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
     * @return MouvementBancaire
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
     * Set type
     *
     * @param string $type
     * @return MouvementBancaire
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
	public function getLibelle() {
		return $this->libelle;
	}
	public function setLibelle($libelle) {
		$this->libelle = $libelle;
		return $this;
	}
	public function getCompteBancaire() {
		return $this->compteBancaire;
	}
	public function setCompteBancaire($compteBancaire) {
		$this->compteBancaire = $compteBancaire;
		return $this;
	}


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rapprochements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add rapprochements
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochement
     * @return MouvementBancaire
     */
    public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
    {
    	$rapprochement->setMouvementBancaire($this);
        $this->rapprochements[] = $rapprochement;

        return $this;
    }

    /**
     * Remove rapprochements
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
     */
    public function removeRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochements)
    {
        $this->rapprochements->removeElement($rapprochements);
    }

    /**
     * Get rapprochements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRapprochements()
    {
        return $this->rapprochements;
    }

    public function getTotalRapproche(){

    	$total = 0;
    	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	return $total;
    }

    public function __toString(){
    	return $this->date->format('d/m/Y').' | '.$this->libelle.' | '.$this->montant.'€';
    }
}
