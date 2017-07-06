<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Depense
 *
 * @ORM\Table(name="depense")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\DepenseRepository")
 * @UniqueEntity(
 *     fields={"compte", "numFournisseur"},
 *     errorPath="numFournisseur",
 *     message="Ce numéro de facture existe déjà pour ce fournisseur."
 * )
 */
class Depense
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compte;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=20)
     */
    private $etat;

    /**
     * @var string
     *
     * @ORM\Column(name="mode_paiement", type="string", length=255, nullable=true)
     */
    private $modePaiement;

  	/**
  	 * @var string
  	 *
  	 * @ORM\Column(name="condition_reglement", type="string", length=255, nullable=true)
  	 */
  	private $conditionReglement;

  	/**
  	 * @var \DateTime
  	 *
  	 * @ORM\Column(name="dateConditionReglement", type="date", nullable=true)
  	 */
  	private $dateConditionReglement;

  	/**
  	 * @var string
  	 *
  	 * @ORM\Column(name="num_cheque", type="string", length=50, nullable=true)
  	 */
  	private $numCheque;

	   /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $analytique;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\LigneDepense", mappedBy="depense", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $lignes;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="depense")
     */
    private $rapprochements;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Avoir", mappedBy="depense")
     */
    private $avoirs;

    /**
     * @var decimal
     *
     * @ORM\Column(name="taxe", type="decimal", scale=2, nullable=true)
     */
    private $taxe;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="num", type="string", length=50, nullable=false)
	 */
	private $num;

	/**
	 * @var float
	 *
	 * @ORM\Column(name="remise", type="float", nullable=true)
	 */
	private $remise;

	/**
	 * @var decimal
	 *
	 * @ORM\Column(name="taxePercent", type="decimal", scale=4, nullable=true)
	 */
	private $taxePercent;

	/**
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NDF\NoteFrais", inversedBy="depenses")
	 * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
	 */
	private $noteFrais;

  /**
   * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\JournalAchat", mappedBy="depense", cascade={"remove"}, orphanRemoval=true)
   */
  private $journalAchats;

  /**
  * @var string
  *
  * @ORM\Column(name="num_fournisseur", type="string", length=255, nullable=true)
  */
 private $numFournisseur;

  /**
   * Constructor
   */
  public function __construct()
  {
      $this->lignes = new \Doctrine\Common\Collections\ArrayCollection();
      $this->rapprochements = new \Doctrine\Common\Collections\ArrayCollection();

      if($this->date == null){
      	$this->date = new \DateTime(date('Y-m-d'));
      }
  }

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
   * Set etat
   *
   * @param string $etat
   * @return Depense
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
   * Set dateCreation
   *
   * @param \DateTime $dateCreation
   * @return Depense
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
   * @return Depense
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
   * Set compte
   *
   * @param $compte
   * @return Depense
   */
  public function setCompte($compte = null)
  {
      $this->compte = $compte;

      return $this;
  }

  /**
   * Get compte
   *
   * @return \AppBundle\Entity\CRM\Compte
   */
  public function getCompte()
  {
      return $this->compte;
  }

  /**
   * Set userCreation
   *
   * @param \AppBundle\Entity\User $userCreation
   * @return Depense
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
   * @return Depense
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
	 * Add lignes
	 *
	 * @param \AppBundle:Compta\LigneDepense $ligne
	 * @return Depense
	 */
	public function addLigne(\AppBundle\Entity\Compta\LigneDepense $ligne)
	{
		$ligne->setDepense($this);
		$this->lignes[] = $ligne;

		return $this;
	}

	/**
	 * Remove ligne
	 *
	 * @param \AppBundle:Compta\LigneDepense ligne
	 */
	public function removeLigne(\AppBundle\Entity\Compta\LigneDepense $ligne)
	{
		$this->lignes->removeElement($ligne);
	}

	/**
	 * Get lignes
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getLignes()
	{
		return $this->lignes;
	}



	/**
	 * Add rapprochements
	 *
	 * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
	 * @return Depense
	 */
	public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
	{
		$rapprochement->setDepense($this);
		$this->rapprochements[] = $rapprochement;

		return $this;
	}

	/**
	 * Remove rapprochements
	 *
	 * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
	 */
	public function removeRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
	{
		$this->rapprochements->removeElement($rapprochement);
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

    /**
     * Set modePaiement
     *
     * @param string $modePaiement
     * @return Depense
     */
    public function setModePaiement($modePaiement)
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    /**
     * Get modePaiement
     *
     * @return string
     */
    public function getModePaiement()
    {
        return $this->modePaiement;
    }

    /**
     * Set numCheque
     *
     * @param string $numCheque
     * @return Depense
     */
    public function setNumCheque($numCheque)
    {
        $this->numCheque = $numCheque;

        return $this;
    }

    /**
     * Get numCheque
     *
     * @return string
     */
    public function getNumCheque()
    {
        return $this->numCheque;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Depense
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Add avoirs
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoirs
     * @return Depense
     */
    public function addAvoir(\AppBundle\Entity\Compta\Avoir $avoirs)
    {
        $this->avoirs[] = $avoirs;

        return $this;
    }

    /**
     * Remove avoirs
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoirs
     */
    public function removeAvoir(\AppBundle\Entity\Compta\Avoir $avoirs)
    {
        $this->avoirs->removeElement($avoirs);
    }

    /**
     * Get avoirs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAvoirs()
    {
        return $this->avoirs;
    }

    /**
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return Depense
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
     * Set date
     *
     * @param \DateTime $date
     * @return Depense
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
     * Set taxe
     *
     * @param string $taxe
     * @return Depense
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
     * Set num
     *
     * @param string $num
     * @return Depense
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
	 * Set remise
	 *
	 * @param float $remise
	 * @return Depense
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
	 * Set taxePercent
	 *
	 * @param float $taxePercent
	 * @return Depense
	 */
  	public function setTaxePercent($taxePercent)
  	{
  		$this->taxePercent = $taxePercent;

  		return $this;
  	}

  	/**
  	 * Get taxePercent
  	 *
  	 * @return float
  	 */
  	public function getTaxePercent()
  	{
  		return $this->taxePercent;
  	}


    /**
     * Set noteFrais
     *
     * @param \AppBundle\Entity\NDF\NoteFrais $noteFrais
     * @return Depense
     */
    public function setNoteFrais(\AppBundle\Entity\NDF\NoteFrais $noteFrais = null)
    {
        $this->noteFrais = $noteFrais;
        return $this;
    }

    /**
     * Get noteFrais
     *
     * @return \AppBundle\Entity\NDF\NoteFrais
     */
    public function getNoteFrais()
    {
        return $this->noteFrais;
    }

    /**
     * Set conditionReglement
     *
     * @param string $conditionReglement
     * @return Depense
     */
    public function setConditionReglement($conditionReglement)
    {
        $this->conditionReglement = $conditionReglement;

        return $this;
    }

    /**
     * Get conditionReglement
     *
     * @return string
     */
    public function getConditionReglement()
    {
        return $this->conditionReglement;
    }

    /**
     * Set dateConditionReglement
     *
     * @param \DateTime $dateConditionReglement
     * @return Depense
     */
    public function setDateConditionReglement($dateConditionReglement)
    {
        $this->dateConditionReglement = $dateConditionReglement;

        return $this;
    }

    /**
     * Get dateConditionReglement
     *
     * @return \DateTime
     */
    public function getDateConditionReglement()
    {
        return $this->dateConditionReglement;
    }


    /**
     * Add journalAchats
     *
     * @param \AppBundle\Entity\Compta\JournalAchat $journalAchats
     * @return Depense
     */
    public function addJournalAchat(\AppBundle\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats[] = $journalAchats;

        return $this;
    }

    /**
     * Remove journalAchats
     *
     * @param \AppBundle\Entity\Compta\JournalAchat $journalAchats
     */
    public function removeJournalAchat(\AppBundle\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats->removeElement($journalAchats);
    }

    /**
     * Get journalAchats
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJournalAchats()
    {
        return $this->journalAchats;
    }


    /**
     * Set numFournisseur
     *
     * @param string $numFournisseur
     * @return Depense
     */
    public function setNumFournisseur($numFournisseur)
    {
        $this->numFournisseur = $numFournisseur;

        return $this;
    }

    /**
     * Get numFournisseur
     *
     * @return string
     */
    public function getNumFournisseur()
    {
        return $this->numFournisseur;
    }

    public function __toString(){

  		if($this->noteFrais != null){
  			return $this->libelle.' : '.$this->getTotalTTC().'€';
  		} else {
  			return $this->compte->getNom().' : '.$this->getTotalTTC().'€';
  		}

  	}

    /**
  	 * Get formatted etat
  	 *
  	 * @return string
  	 */
  	public function getFormattedEtat()
  	{
  		$s_etat = '';

  		switch($this->etat){

  			case 'ENREGISTRE':
  				$s_etat = 'Enregistré';
  				break;
  			case 'REGLE':
  				$s_etat = 'Reglé';
  				break;
  			case 'RAPPROCHE':
  				$s_etat = 'Rapproché';
  				break;
  			default:
  				$s_etat = 'Inconnu';
  				break;
  		}

  		return $s_etat;
  	}

    public function getTotaux(){

  		$ht = 0;
  		$tva= 0 ;

  		if(count($this->lignes) != 0){
  			foreach($this->lignes as $ligne){
  				$ht+=$ligne->getMontant();
  				$tva+=$ligne->getTaxe();
  			}
  		}

  		if($tva == 0){
  			$tva = $this->taxe;
  		}
  		$ttc = $ht+$tva;

  		$arr_totaux = array();
  		$arr_totaux['HT'] = $ht;
  		$arr_totaux['TTC'] = $ttc;
  		$arr_totaux['TVA'] = $tva;

  		return $arr_totaux;
  	}

  	public function getTotalHT(){

  		$ht = 0;
  		if(count($this->lignes) != 0){
  			foreach($this->lignes as $ligne){
  				$ht+=$ligne->getMontant();
  			}
  		}
  		return $ht;
  	}

  	public function getTotalTTC(){
  		$ht = 0;
  		$tva = 0;
  		foreach($this->lignes as $ligne){
  			$ht+=$ligne->getMontant();
  			$tva+=$ligne->getTaxe();
  		}

  		if($tva == 0){
  			$tva = $this->taxe;
  		}
  		$ttc = $ht+$tva;
  		return $ttc;
  	}

  	public function getTotalTVA(){
  		$tva = 0;
  		foreach($this->lignes as $ligne){
  			$tva+=$ligne->getTaxe();
  		}

  		if($tva == 0){
  			$tva = $this->taxe;
  		}
  		return $tva;
  	}


  	public function getTotalRapproche(){

  		$total = 0;
  		foreach($this->rapprochements as $rapprochement){
  			$total+= $rapprochement->getMouvementBancaire()->getMontant();
  		}
  		return (-$total);
  	}

}
