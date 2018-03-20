<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonCommande
 *
 * @ORM\Table(name="bon_commande")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\BonCommandeRepository")
 */
class BonCommande
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
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="bonsCommande")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionCommerciale;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\DocumentPrix", mappedBy="bonCommande")
     * @ORM\JoinColumn(nullable=true)
     */
    private $factures;

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
     * Set num
     *
     * @param string $num
     * @return BonCommande
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return string 
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     * @return BonCommande
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
     * Set actionCommerciale
     *
     * @param \AppBundle\Entity\CRM\Opportunite $actionCommerciale
     * @return BonCommande
     */
    public function setActionCommerciale(\AppBundle\Entity\CRM\Opportunite $actionCommerciale)
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
     * Get total facture
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getTotalFacture()
    {
        $montant = 0;
        foreach($this->factures as $facture){
            $montant+= intval($facture->getTotalHT()*100);
        }
        return $montant;
       
    }

    /**
     * Get total facture monetaire
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getTotalFactureMonetaire()
    {
        $montant = 0;
        foreach($this->factures as $facture){
           $montant+= $facture->getTotalHT();
        }
        return $montant;
       
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->factures = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add factures
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $factures
     * @return BonCommande
     */
    public function addFacture(\AppBundle\Entity\CRM\DocumentPrix $factures)
    {
        $this->factures[] = $factures;

        return $this;
    }

    /**
     * Remove factures
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $factures
     */
    public function removeFacture(\AppBundle\Entity\CRM\DocumentPrix $factures)
    {
        $this->factures->removeElement($factures);
    }

    /**
     * Get factures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFactures()
    {
        return $this->factures;
    }
}
