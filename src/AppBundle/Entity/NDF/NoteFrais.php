<?php

namespace AppBundle\Entity\NDF;

use Doctrine\ORM\Mapping as ORM;

/**
 * NoteFrais
 *
 * @ORM\Table(name="note_frais")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\NDF\NoteFraisRepository")
 */
class NoteFrais
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
     * @ORM\Column(name="month", type="integer", nullable=false)
     */
    private $month;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", nullable=false)
     */
    private $year;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="date")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEdition", type="date", nullable=true)
     */
    private $dateEdition;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userCreation;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $userEdition;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Depense", mappedBy="noteFrais", cascade={"remove"}, orphanRemoval=true)
     */
    private $depenses;

     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="noteFrais")
     */
    private $rapprochements;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=20)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * Set month
     *
     * @param integer $month
     * @return NoteDeFrais
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return NoteDeFrais
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->depenses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return 'NDF '.$this->getUser().' - '.$this->getMonth().'/'.$this->getYear();
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return NoteDeFrais
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateEdition
     *
     * @param \DateTime $dateEdition
     * @return NoteDeFrais
     */
    public function setDateEdition($dateEdition)
    {
        $this->dateEdition = $dateEdition;

        return $this;
    }

    /**
     * Get dateEdition
     *
     * @return \DateTime
     */
    public function getDateEdition()
    {
        return $this->dateEdition;
    }

    /**
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return NoteDeFrais
     */
    public function setUserCreation(\AppBundle\Entity\User $userCreation)
    {
        $this->userCreation = $userCreation;

        return $this;
    }

    /**
     * Get userCreation
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserCreation()
    {
        return $this->userCreation;
    }

    /**
     * Set userEdition
     *
     * @param \AppBundle\Entity\User $userEdition
     * @return NoteDeFrais
     */
    public function setUserEdition(\AppBundle\Entity\User $userEdition = null)
    {
        $this->userEdition = $userEdition;

        return $this;
    }

    /**
     * Get userEdition
     *
     * @return \AppBundle\Entity\User
     */
    public function getUserEdition()
    {
        return $this->userEdition;
    }

    /**
     * Add depenses
     *
     * @param \AppBundle\Entity\Compta\Depense $depenses
     * @return NoteDeFrais
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

    public function getTotal(){
    	$total = 0;
    	foreach($this->depenses as $depense){
    		$total+=$depense->getTotalTTC();
    	}
    	return $total;

    }

    public function __toString(){
    	return 'NDF '.$this->user->__toString().' '. $this->month.'/'.$this->year.' : '.$this->getTotal().' €';
    }

    /**
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return NoteFrais
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

    /**
     * Set etat
     *
     * @param string $etat
     * @return NoteFrais
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return string
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Add rapprochements
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
     * @return NoteFrais
     */
    public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochements)
    {
        $this->rapprochements[] = $rapprochements;

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

    public function getTotalTTC()
    {
    	return $this->getTotal();
    }

    public function getTotalRapproche(){

    	$total = 0;
    	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	return $total;
    }

    public function getTotalHT(){

    	$ht = 0;
    	if(count($this->depenses) != 0){
    		foreach($this->depenses as $depense){
    			$ht+=$depense->getTotalHT();
    		}
    	}
    	return $ht;
    }


    public function getTotalTVA(){
    	$tva = 0;
    	if(count($this->depenses) != 0){
    		foreach($this->depenses as $depense){
    			$tva+=$depense->getTotalTVA();
    		}
    	}
    	return $tva;
    }


    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return NoteFrais
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getNbRecus(){
      $nb  = 0;
      foreach($this->depenses as $depense){
        $nb+=count($depense->getLignes());
      }

      return $nb;
    }

    public function getRecus(){
      $arr_recus  = array();
      foreach($this->depenses as $depense){
        foreach($depense->getLignes() as $ligne){
          $arr_recus[] = $ligne->getRecu();
        }
      }

      return $arr_recus;
    }

    public function getRecusId(){
      $arr_recus_id  = array();
      foreach($this->depenses as $depense){
        foreach($depense->getLignes() as $ligne){
          $arr_recus_id[] = $ligne->getRecu()->getId();
        }
      }

      return $arr_recus_id;
    }

    public function getEtatPourUser(){

      $etat = '';
      switch(strtoupper($this->etat)){
        case 'DRAFT':
          $etat = 'Brouillon';
          break;

        case 'ENREGISTRE':
          $etat = 'Transmise à la compta';
          break;

        case 'RAPPROCHE':
          $etat = 'Payée';
          break;

      }

      return $etat;
    }
}
