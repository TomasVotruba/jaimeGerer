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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="bonsCommande")
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

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
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return BonCommande
     */
    public function setFacture(\AppBundle\Entity\CRM\DocumentPrix $facture)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Set facture as null
     *
     * @return BonCommande
     */
    public function setFactureNull()
    {
        $this->facture = null;

        return $this;
    }

    /**
     * Get facture
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Get total facture
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getTotalFacture()
    {
        if($this->facture){
            return intval($this->facture->getTotalHT()*100);
        }
        return 0;
       
    }

    /**
     * Get total facture monetaire
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getTotalFactureMonetaire()
    {
        if($this->facture){
            return $this->facture->getTotalHT();
        }
        return 0;
       
    }
}
