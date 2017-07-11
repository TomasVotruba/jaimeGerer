<?php
namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Opportunite
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\OpportuniteRepository")
 * @ORM\Table(name="opportunite")
 * @UniqueEntity("id")
 */
class Opportunite
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
   * @ORM\Column(name="montant", type="float")
   * @Assert\NotBlank()
   */
  private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $probabilite;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=20, nullable=true)
     */
    private $etat = "ONGOING";

    /**
     * @var boolean
     *
     * @ORM\Column(name="appel_offre", type="boolean", nullable=false)
     */
    private $appelOffre = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prive_or_public", type="string", length=6)
     */
    private $priveOrPublic;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_creation", type="date")
   */
  private $dateCreation;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date_edition", type="date", nullable=true)
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
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $userGestion;

  /**
   * @var string
   *
   * @ORM\Column(name="type", type="string")
   */
  private $type;

  /**
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
   * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
   */
  private $origine;

  /**
   * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Settings", cascade={"persist"})
   * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
   */
  private $settings;


  /**
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte")
   * @ORM\JoinColumn(nullable=false)
   * @Assert\NotBlank()
   */
  private $compte;

  /**
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact")
   * @ORM\JoinColumn(nullable=true)
   */
  private $contact;

  /**
  *
  * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\OpportuniteRepartition", mappedBy="opportunite", cascade={"persist", "remove"}, orphanRemoval=true)
  * @ORM\OrderBy({"date" = "ASC"})
  *
  */
  private $opportuniteRepartitions;

  /**
  *
  * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\OpportuniteSousTraitance", mappedBy="opportunite", cascade={"persist", "remove"}, orphanRemoval=true)
  *
  */
  private $opportuniteSousTraitances;


  /**
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
   * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
   */
  private $analytique;

  /**
   * @ORM\OneToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="opportunite")
   * @ORM\JoinColumn(nullable=true)
   */
  private $devis;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date", type="date")
   */
  private $date;

  /**
   * Constructor
   */
  public function __construct()
  {
      $this->settings = new \Doctrine\Common\Collections\ArrayCollection();
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
   * Set nom
   *
   * @param string $nom
   * @return Opportunite
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
	 *
	 * @return the string
	 */
	public function getMontant() {
		return $this->montant;
	}

	/**
	 *
	 * @param
	 *        	$montant
	 */
	public function setMontant($montant) {
		$this->montant = $montant;
		return $this;
	}

    /**
     * Set etat
     *
     * @param string $etat
     * @return DocumentPrix
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
     * Set appelOffre
     *
     * @param boolean $appelOffre
     * @return Contact
     */
    public function setAppelOffre($appelOffre)
    {
        $this->appelOffre = $appelOffre;

        return $this;
    }

    /**
     * Get appelOffre
     *
     * @return boolean
     */
    public function getAppelOffre()
    {
        return $this->appelOffre;
    }

	/**
	 * Set dateCreation
	 *
	 * @param \DateTime $dateCreation
	 * @return Compte
	 */
	public function setDateCreation($dateCreation) {
		$this->dateCreation = $dateCreation;

		return $this;
	}

	/**
	 * Get dateCreation
	 *
	 * @return \DateTime
	 */
	public function getDateCreation() {
		return $this->dateCreation;
	}

	/**
	 * Set dateEdition
	 *
	 * @param \DateTime $dateEdition
	 * @return Compte
	 */
	public function setDateEdition($dateEdition) {
		$this->dateEdition = $dateEdition;

		return $this;
	}

	/**
	 * Get dateEdition
	 *
	 * @return \DateTime
	 */
	public function getDateEdition() {
		return $this->dateEdition;
	}

	/**
	 * Set userCreation
	 *
	 * @param \AppBundle\Entity\User $userCreation
	 * @return Compte
	 */
	public function setUserCreation(\AppBundle\Entity\User $userCreation) {
		$this->userCreation = $userCreation;

		return $this;
	}

	/**
	 * Get userCreation
	 *
	 * @return \AppBundle\Entity\User
	 */
	public function getUserCreation() {
		return $this->userCreation;
	}

	/**
	 * Set userEdition
	 *
	 * @param \AppBundle\Entity\User $userEdition
	 * @return Compte
	 */
	public function setUserEdition(\AppBundle\Entity\User $userEdition = null) {
		$this->userEdition = $userEdition;

		return $this;
	}

	/**
	 * Get userEdition
	 *
	 * @return \AppBundle\Entity\User
	 */
	public function getUserEdition() {
		return $this->userEdition;
	}

	/**
	 * Set userGestion
	 *
	 * @param \AppBundle\Entity\User $userGestion
	 * @return Compte
	 */
	public function setUserGestion(\AppBundle\Entity\User $userGestion) {
		$this->userGestion = $userGestion;

		return $this;
	}

	/**
	 * Get userGestion
	 *
	 * @return \AppBundle\Entity\User
	 */
	public function getUserGestion() {
		return $this->userGestion;
	}

	/**
	 *
	 * @return the string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 *
	 * @param
	 *        	$type
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getCompte() {
		return $this->compte;
	}

	/**
	 *
	 * @param unknown_type $compte
	 */
	public function setCompte($compte) {
		$this->compte = $compte;
		return $this;
	}

	/**
	 *
	 * @return the unknown_type
	 */
	public function getContact() {
		return $this->contact;
	}

	/**
	 *
	 * @param unknown_type $contact
	 */
	public function setContact($contact) {
		$this->contact = $contact;
		return $this;
	}


	/**
	 * Add settings
	 *
	 * @param \AppBundle\Entity\Settings $settings
	 * @return Contact
	 */
	public function addSetting(\AppBundle\Entity\Settings $settings)
	{
		$this->settings[] = $settings;

		return $this;
	}

	/**
	 * Remove all settings
	 *
	 * @param \AppBundle\Entity\Settings $settings
	 */
	public function removeSettings()
	{
		$this->settings = null;
	}

	/**
	 * Remove settings
	 *
	 * @param \AppBundle\Entity\Settings $settings
	 */
	public function removeSetting(\AppBundle\Entity\Settings $settings)
	{
		$this->settings->removeElement($settings);
	}

	/**
	 * Get settings
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getSettings()
	{
		return $this->settings;
	}

    /**
     * Get probabilite
     *
     * @return \AppBundle\Entity\Settings
     */
    public function getProbabilite()
    {
        return $this->probabilite;
    }

    /**
     * Set probabilite
     *
     * @param \AppBundle\Entity\Settings $probabilite
     * @return Opportunite
     */
    public function setProbabilite(\AppBundle\Entity\Settings $probabilite = null)
    {
        $this->probabilite = $probabilite;

        return $this;
    }

    /**
     * Set origine
     *
     * @param \AppBundle\Entity\Settings $origine
     * @return Opportunite
     */
    public function setOrigine(\AppBundle\Entity\Settings $origine = null)
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return \AppBundle\Entity\Settings
     */
    public function getOrigine()
    {
        return $this->origine;
    }


    /**
     * Add opportuniteRepartitions
     *
     * @param \AppBundle\Entity\CRM\OpportuniteRepartition $opportuniteRepartitions
     * @return Opportunite
     */
    public function addOpportuniteRepartition(\AppBundle\Entity\CRM\OpportuniteRepartition $opportuniteRepartitions)
    {
        $this->opportuniteRepartitions[] = $opportuniteRepartitions;
        $opportuniteRepartitions->setOpportunite($this);

        return $this;
    }

    /**
     * Remove opportuniteRepartitions
     *
     * @param \AppBundle\Entity\CRM\OpportuniteRepartition $opportuniteRepartitions
     */
    public function removeOpportuniteRepartition(\AppBundle\Entity\CRM\OpportuniteRepartition $opportuniteRepartitions)
    {
        $this->opportuniteRepartitions->removeElement($opportuniteRepartitions);
    }

    /**
     * Get opportuniteRepartitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOpportuniteRepartitions()
    {
        return $this->opportuniteRepartitions;
    }

    public function __toString() {
      return $this->getNom ();
    }

    public function getCa_attendu(){
      $s_proba = $this->getProbabilite()->getValeur();
      $arr_proba = explode('-', $s_proba);
      $proba = str_replace('%', '', $arr_proba[1]);

      return $this->getMontant()*($proba/100);
    }

    public function win(){
      $this->etat = "WON";
    }

    public function isWon(){
      if($this->etat == "WON"){
        return true;
      }
      return false;
    }

    public function lose(){
      $this->etat = "LOST";
    }

    public function isLost(){
      if($this->etat == "LOST"){
        return true;
      }
      return false;
    }


    public function getEtatToString(){
      $toString = "";

      switch($this->etat){
        case 'ONGOING':
          $toString = "En cours";
          break;

        case 'WON':
          $toString = "Gagnée";
          break;

        case 'LOST':
          $toString = "Perdue";
          break;

        default:
          $toString = "Inconnu";
          break;
      }

      return $toString;
    }



    /**
     * Add opportuniteSousTraitances
     *
     * @param \AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitances
     * @return Opportunite
     */
    public function addOpportuniteSousTraitance(\AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitances)
    {
        $this->opportuniteSousTraitances[] = $opportuniteSousTraitances;
        $opportuniteSousTraitances->setOpportunite($this);
        return $this;
    }

    /**
     * Remove opportuniteSousTraitances
     *
     * @param \AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitances
     */
    public function removeOpportuniteSousTraitance(\AppBundle\Entity\CRM\OpportuniteSousTraitance $opportuniteSousTraitances)
    {
        $this->opportuniteSousTraitances->removeElement($opportuniteSousTraitances);
    }

    /**
     * Get opportuniteSousTraitances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOpportuniteSousTraitances()
    {
        return $this->opportuniteSousTraitances;
    }

    /**
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return Opportunite
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

    public function hasSousTraitance(){
      if(count($this->opportuniteSousTraitances) == 0){
        return false;
      }
      return true;
    }

    public function getMontantMonetaireSousTraitance(){
      $total = 0;
      foreach($this->opportuniteSousTraitances as $st){
        $total+=$st->getMontantMonetaire();
      }
      return $total;
    }

    public function getRepartitionStartDate(){
      $startDate = $this->opportuniteRepartitions[0]->getDate();
      foreach($this->opportuniteRepartitions as $repartition){
        if($repartition->getDate() < $startDate ){
          $startDate = $repartition->getDate();
        }
      }
      return $startDate;
    }

    public function getRepartitionEndDate(){
      $startDate = $this->opportuniteRepartitions[0]->getDate();
      foreach($this->opportuniteRepartitions as $repartition){
        if($repartition->getDate() > $startDate ){
          $startDate = $repartition->getDate();
        }
      }
      return $startDate;
    }

    public function getRepartitionMonths(){
      $arr_months = array();
      foreach($this->opportuniteRepartitions as $repartition){
        $month = $repartition->getDate()->format('m');
        if(!in_array($month, $arr_months)){
          $arr_months[] = $month;
        }
      }
      return $arr_months;
    }


    /**
     * Set devis
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $devis
     * @return Opportunite
     */
    public function setDevis(\AppBundle\Entity\CRM\DocumentPrix $devis = null)
    {
        $this->devis = $devis;

        return $this;
    }

    /**
     * Get devis
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix 
     */
    public function getDevis()
    {
        return $this->devis;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Opportunite
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
     * Set priveOrPublic
     *
     * @param string $priveOrPublic
     * @return Opportunite
     */
    public function setPriveOrPublic($priveOrPublic)
    {
        $this->priveOrPublic = $priveOrPublic;

        return $this;
    }

    /**
     * Get priveOrPublic
     *
     * @return string 
     */
    public function getPriveOrPublic()
    {
        return $this->priveOrPublic;
    }

    /**
     * Get priveOrPublic as human readable string
     *
     * @return string 
     */
    public function getPriveOrPublicToString()
    {
        if($this->priveOrPublic == "PRIVE") {
          return 'Privé';
        } else {
          return 'Public';
        }

        return 'N/A';
    }

    public function isSecteurPrive()
    {
        if($this->priveOrPublic == "PRIVE") {
          return true;
        } 

        return false;
    }

    public function isSecteurPublic()
    {
        if($this->priveOrPublic == "PUBLIC") {
          return true;
        } 

        return false;
    }
}
