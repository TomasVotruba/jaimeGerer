<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * OpportuniteSousTraitance
 *
 * @ORM\Table(name="opportunite_sous_traitance")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\OpportuniteSousTraitanceRepository")
 */
class OpportuniteSousTraitance
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
     * @ORM\Column(name="sousTraitant", type="string", length=255)
     */
    private $sousTraitant;

    /**
     * @var string
     *
     * @ORM\Column(name="typeForfait", type="string", length=255)
     */
    private $typeForfait;

    /**
     * @var integer
     *
     * @ORM\Column(name="montantGlobal", type="integer", nullable=true)
     */
    private $montantGlobal = null;

    /**
     * @var string
     *
     * @ORM\Column(name="tarifJour", type="integer", nullable=true)
     */
    private $tarifJour = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbJours", type="integer", nullable=true)
     */
    private $nbJours = null;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="opportuniteSousTraitances")
     * @ORM\JoinColumn(nullable=false)
     */
    private $opportunite;

    /**
    * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Compta\Depense")
    */
    private $depenses;

    /**
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\SousTraitanceRepartition", mappedBy="opportuniteSousTraitance", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $repartitions;

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
     * Set sousTraitant
     *
     * @param string $sousTraitant
     * @return OpportuniteSousTraitance
     */
    public function setSousTraitant($sousTraitant)
    {
        $this->sousTraitant = $sousTraitant;

        return $this;
    }

    /**
     * Get sousTraitant
     *
     * @return string
     */
    public function getSousTraitant()
    {
        return $this->sousTraitant;
    }

    /**
     * Set montantGlobal
     *
     * @param integer $montantGlobal
     * @return OpportuniteSousTraitance
     */
    public function setMontantGlobal($montantGlobal)
    {
        $this->montantGlobal = $montantGlobal;

        return $this;
    }

    /**
     * Get montantGlobal
     *
     * @return integer
     */
    public function getMontantGlobal()
    {
        return $this->montantGlobal;
    }

    /**
     * Set tarifJour
     *
     * @param integer $tarifJour
     * @return OpportuniteSousTraitance
     */
    public function setTarifJour($tarifJour)
    {
        $this->tarifJour = $tarifJour;

        return $this;
    }

    /**
     * Get tarifJour
     *
     * @return integer
     */
    public function getTarifJour()
    {
        return $this->tarifJour;
    }

    /**
     * Set nbJours
     *
     * @param integer $nbJours
     * @return OpportuniteSousTraitance
     */
    public function setNbJours($nbJours)
    {
        $this->nbJours = $nbJours;

        return $this;
    }

    /**
     * Get nbJours
     *
     * @return integer
     */
    public function getNbJours()
    {
        return $this->nbJours;
    }

    /**
     * Set opportunite
     *
     * @param \AppBundle\Entity\CRM\Opportunite $opportunite
     * @return OpportuniteSousTraitance
     */
    public function setOpportunite(\AppBundle\Entity\CRM\Opportunite $opportunite)
    {
        $this->opportunite = $opportunite;

        return $this;
    }

    /**
     * Get opportunite
     *
     * @return \AppBundle\Entity\CRM\Opportunite
     */
    public function getOpportunite()
    {
        return $this->opportunite;
    }

    /**
     * Set typeForfait
     *
     * @param string $typeForfait
     * @return OpportuniteSousTraitance
     */
    public function setTypeForfait($typeForfait)
    {
        $this->typeForfait = $typeForfait;

        return $this;
    }

    /**
     * Get typeForfait
     *
     * @return string
     */
    public function getTypeForfait()
    {
        return $this->typeForfait;
    }

    /**
     * Get montant monetaire
     *
     * @return float
     */
    public function getMontantMonetaire()
    {
      if($this->typeForfait == "GLOBAL"){
          return $this->montantGlobal/100;
      }
      return $this->nbJours*$this->tarifJour/100;
    }

    /**
     * Get montant montaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
      if($this->typeForfait == "GLOBAL"){
        return $this->montantGlobal = $montant*100;
      }
      return $this->tarifJour = $montant*100;
    }

    /**
     * Get montant global monetaire
     *
     * @return float
     */
    public function getMontantGlobalMonetaire()
    {
        return $this->montantGlobal/100;
    }

    /**
     * Get montant global montaire
     *
     * @return integer
     */
    public function setMontantGlobalMonetaire($montant)
    {
      return $this->montantGlobal = $montant*100;
    }

    /**
     * Get tarif jour monetaire monetaire
     *
     * @return float
     */
    public function getTarifJourMonetaire()
    {
        return $this->tarifJour/100;
    }

    /**
     * Get tarif jour montaire
     *
     * @return integer
     */
    public function setTarifJourMonetaire($montant)
    {
      return $this->tarifJour = $montant*100;
    }


    public function __toString(){
      return $this->opportunite->getNom().' - '.$this->sousTraitant;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depenses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add depenses
     *
     * @param \AppBundle\Entity\Compta\Depense $depenses
     * @return OpportuniteSousTraitance
     */
    public function addDepense(\AppBundle\Entity\Compta\Depense $depenses)
    {
        $this->depenses[] = $depenses;

        return $this;
    }

    /**
     * Remove depenses
     *
     * @param \AppBundle\Entity\Compta\Depense $depenses
     */
    public function removeDepense(\AppBundle\Entity\Compta\Depense $depenses)
    {
        $this->depenses->removeElement($depenses);
    }

    /**
     * Get depenses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepenses()
    {
        return $this->depenses;
    }

    public function getTotalFacture(){
      $totalFacture = 0;
      foreach($this->depenses as $depense){
        $totalFacture+=$depense->getTotalHT();
      }
      return $totalFacture;
    }

    public function getResteAFacturer(){
      $reste = $this->getMontantMonetaire()-$this->getTotalFacture();
      return $reste;
    }

    public function getNomEtMontant(){
      return  $this->sousTraitant.' - '.
              $this->opportunite->getCompte()->getNom().' : '.
              $this->getMontantMonetaire().'€ (reste à facturer : '.
              $this->getResteAFacturer().' €)';
    }

    /**
     * Add repartitions
     *
     * @param \AppBundle\Entity\CRM\SousTraitanceRepartition $repartitions
     * @return OpportuniteSousTraitance
     */
    public function addRepartition(\AppBundle\Entity\CRM\SousTraitanceRepartition $repartitions)
    {
        $this->repartitions[] = $repartitions;
        $repartitions->setOpportuniteSousTraitance($this);

        return $this;
    }

    /**
     * Remove repartitions
     *
     * @param \AppBundle\Entity\CRM\SousTraitanceRepartition $repartitions
     */
    public function removeRepartition(\AppBundle\Entity\CRM\SousTraitanceRepartition $repartitions)
    {
        $this->repartitions->removeElement($repartitions);
    }

    /**
     * Get repartitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRepartitions()
    {
        return $this->repartitions;
    }
}
