<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanPaiement
 *
 * @ORM\Table(name="plan_paiement")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\PlanPaiementRepository")
 */
class PlanPaiement
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
     * @ORM\Column(name="pourcentage", type="integer", nullable=true)
     */
    private $pourcentage;

    /**
     * @var float
     *
     * @ORM\Column(name="montant", type="float", nullable=true)
     */
    private $montant;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="planPaiements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionCommerciale;

     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $facture;

    /**
     * @var boolean
     *
     * @ORM\Column(name="commande", type="boolean", nullable=false)
     */
    private $commande = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fin_projet", type="boolean", nullable=false)
     */
    private $finProjet = false;

    /**
    * @var string
    *
    * @ORM\Column(name="nom", type="string", length=255, nullable=true)
    */
    private $nom;


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
     * Set pourcentage
     *
     * @param integer $pourcentage
     * @return PlanPaiement
     */
    public function setPourcentage($pourcentage)
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    /**
     * Get pourcentage
     *
     * @return integer 
     */
    public function getPourcentage()
    {
        $montantActionCommerciale = $this->actionCommerciale->getMontant();
        if($this->pourcentage){
            return $this->pourcentage;
        }

        return $this->montant/$montantActionCommerciale*100;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return PlanPaiement
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
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return PlanPaiement
     */
    public function setFacture(\AppBundle\Entity\CRM\DocumentPrix $facture)
    {
        $this->facture = $facture;

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
     * Set actionCommerciale
     *
     * @param \AppBundle\Entity\CRM\Opportunite $actionCommerciale
     * @return PlanPaiement
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

    public function __toString(){
        return $this->nom.' - '.$this->date->format('d/m/Y').' : '.strval($this->pourcentage).'%'.' ('.number_format($this->getMontant(), 2).' €)';
    }

    public function getMontant(){
        $montantActionCommerciale = $this->actionCommerciale->getMontant();

        if($this->montant){
            return $this->montant;
        }

        return $this->pourcentage/100*$montantActionCommerciale;
    }

    /**
     * Set commande
     *
     * @param boolean $commande
     * @return PlanPaiement
     */
    public function setCommande($commande)
    {
        $this->commande = $commande;

        return $this;
    }

    /**
     * Get commande
     *
     * @return boolean 
     */
    public function getCommande()
    {
        return $this->commande;
    }

    /**
     * Set finProjet
     *
     * @param boolean $finProjet
     * @return PlanPaiement
     */
    public function setFinProjet($finProjet)
    {
        $this->finProjet = $finProjet;

        return $this;
    }

    /**
     * Get finProjet
     *
     * @return boolean 
     */
    public function getFinProjet()
    {
        return $this->finProjet;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return PlanPaiement
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

    public function getPourcentageNumerique(){
        return $this->pourcentage/100;
    }

    public function getRetard(){
        $today = new \DateTime('today');
        $diff = $today->diff($this->date);

        if($diff->format("%R%a") > 0){
            return 0;
        }
        return $diff->format("%a");
    }

    /**
     * Set montant
     *
     * @param float $montant
     * @return PlanPaiement
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }
}
