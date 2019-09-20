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
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
    * @ORM\JoinColumn(nullable=true)
    */
    private $userCompetCom = null;

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
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte", inversedBy="opportunites" )
    * @ORM\JoinColumn(nullable=false)
    * @Assert\NotBlank()
    */
    private $compte;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact", inversedBy="opportunites")
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
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\BonCommande", mappedBy="actionCommerciale", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $bonsCommande;

    /**
    * @var boolean
    *
    * @ORM\Column(name="prescription", type="boolean", nullable=false)
    */
    private $prescription = false;

    /**
    * @var boolean
    *
    * @ORM\Column(name="nouveau_compte", type="boolean", nullable=true)
    */
    private $nouveauCompte = false;

    /**
    * @var \DateTime
    *
    * @ORM\Column(name="date_won", type="date", nullable=true)
    */
    private $dateWon;

   /**
    * @var float
    *
    * @ORM\Column(name="remise", type="float", nullable=true)
    */
    private $remise;

    /**
    * @var float
    *
    * @ORM\Column(name="temps_commercial", type="float", nullable=true)
    */
    private $tempsCommercial;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fichier;

     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\PlanPaiement", mappedBy="actionCommerciale", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true)
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $planPaiements;

    /**
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\Frais", mappedBy="actionCommerciale", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $frais;

    /**
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\NDF\Recu", mappedBy="actionCommerciale", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $recus;

    /**
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\TimeTracker\Temps", mappedBy="actionCommerciale", cascade={"persist", "remove"}, orphanRemoval=true)
    * @ORM\OrderBy({"date" = "ASC"})
    *
    */
    private $temps;

    /**
    * @var boolean
    *
    * @ORM\Column(name="termine", type="boolean", nullable=false)
    */
    private $termine = false;


    /**
    * Constructor
    */
    public function __construct()
    {
        $this->settings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->planPaiements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->frais = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fichier = null;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return Contact
     */
    public function setId($id)
    {
      $this->id = $id;
       
      return $this;
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
	 * @return
	 */
	public function getMontant() {
		return $this->montant;
	}

	/**
	 *
	 * @param $montant
	 */
	public function setMontant($montant) {
		$this->montant = $montant;
		return $this;
	}

    /**
     *
     * @return 
     */
    public function getTotal() {
        return $this->montant+$this->getTotalFrais();
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
		$this->settings->clear();
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
        return 0;
    }

    public function win(){
      $this->dateWon = new \DateTime(date('Y-m-d'));
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

        if(0 == count($this->opportuniteRepartitions)){
            return null;
        }

        $startDate = $this->opportuniteRepartitions[0]->getDate();
        foreach($this->opportuniteRepartitions as $repartition){
            if($repartition->getDate() < $startDate ){
                $startDate = $repartition->getDate();
            }
        }
        return $startDate;
    }

    public function getRepartitionEndDate(){
        if(0 == count($this->opportuniteRepartitions)){
            return null;
        }

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

    /**
     * Add bonsCommande
     *
     * @param \AppBundle\Entity\CRM\BonCommande $bonsCommande
     * @return Opportunite
     */
    public function addBonsCommande(\AppBundle\Entity\CRM\BonCommande $bonsCommande)
    {
        $this->bonsCommande[] = $bonsCommande;
        $bonsCommande->setActionCommerciale($this);

        return $this;
    }

    /**
     * Remove bonsCommande
     *
     * @param \AppBundle\Entity\CRM\BonCommande $bonsCommande
     */
    public function removeBonsCommande(\AppBundle\Entity\CRM\BonCommande $bonsCommande)
    {
        $this->bonsCommande->removeElement($bonsCommande);
    }

    /**
     * Get bonsCommande
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBonsCommande()
    {
        return $this->bonsCommande;
    }

    public function getTotalBonsCommande(){
        $total = 0;
        foreach($this->bonsCommande as $bc){
            $total+= $bc->getMontant();
        }
        return $total;
    }

    public function getMontantInt(){
        return intval(strval($this->montant*100));
    }

    /**
     * Set prescription
     *
     * @param boolean $prescription
     * @return Opportunite
     */
    public function setPrescription($prescription)
    {
        $this->prescription = $prescription;

        return $this;
    }

    /**
     * Get prescription
     *
     * @return boolean 
     */
    public function getPrescription()
    {
        return $this->prescription;
    }

    /**
     * Set dateWon
     *
     * @param \DateTime $dateWon
     * @return Opportunite
     */
    public function setDateWon($dateWon)
    {
        $this->dateWon = $dateWon;

        return $this;
    }

    /**
     * Get dateWon
     *
     * @return \DateTime 
     */
    public function getDateWon()
    {
        return $this->dateWon;
    }

    /**
     * Set userCompetCom
     *
     * @param \AppBundle\Entity\User $userCompetCom
     * @return Opportunite
     */
    public function setUserCompetCom(\AppBundle\Entity\User $userCompetCom)
    {
        $this->userCompetCom = $userCompetCom;

        return $this;
    }

    /**
     * Get userCompetCom
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUserCompetCom()
    {
        return $this->userCompetCom;
    }

    /**
     * Set remise
     *
     * @param float $remise
     * @return Opportunite
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
     * Set tempsCommercial
     *
     * @param float $tempsCommercial
     * @return Opportunite
     */
    public function setTempsCommercial($tempsCommercial)
    {
        $this->tempsCommercial = $tempsCommercial;

        return $this;
    }

    /**
     * Get tempsCommercial
     *
     * @return float 
     */
    public function getTempsCommercial()
    {
        return $this->tempsCommercial;
    }



    /**
     * Set fichier
     *
     * @param string $fichier
     * @return Opportunite
     */
    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    /**
     * Get fichier
     *
     * @return string 
     */
    public function getFichier()
    {
        return $this->fichier;
    }

    /**
     * Add planPaiements
     *
     * @param \AppBundle\Entity\CRM\PlanPaiement $planPaiements
     * @return Opportunite
     */
    public function addPlanPaiement(\AppBundle\Entity\CRM\PlanPaiement $planPaiements)
    {
        $this->planPaiements[] = $planPaiements;
        $planPaiements->setActionCommerciale($this);

        return $this;
    }

    /**
     * Remove planPaiements
     *
     * @param \AppBundle\Entity\CRM\PlanPaiement $planPaiements
     */
    public function removePlanPaiement(\AppBundle\Entity\CRM\PlanPaiement $planPaiements)
    {
        $this->planPaiements->removeElement($planPaiements);
    }

    /**
     * Clear planPaiements
     */
    public function clearPlanPaiements()
    {
        $this->planPaiements->clear();
    }

    /**
     * Get planPaiements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlanPaiements()
    {
        return $this->planPaiements;
    }

    public function getPlansPaiementsCustom(){
        $custom = array();
        foreach($this->planPaiements as $plan){
            if(false == $plan->getCommande() && false == $plan->getFinProjet()){
                $custom[] = $plan;
            }
        }
        return $custom;
    }

    public function getModePaiement(){
        if(count($this->planPaiements)){
            if(1 == count($this->planPaiements) && true == $this->planPaiements[0]->getCommande()){
                return 'COMMANDE';
            } else if (1 == count($this->planPaiements) && true == $this->planPaiements[0]->getFinProjet()){
                return 'FIN';
            } else if(null == $this->planPaiements[0]->getPourcentage()) {
                 return 'CUSTOM_MONTANT';
            } else {
                return 'CUSTOM_PERCENT';
            }

        }
        return null;
    }


    /**
     * Add frais
     *
     * @param \AppBundle\Entity\CRM\Frais $frais
     * @return Opportunite
     */
    public function addFrai(\AppBundle\Entity\CRM\Frais $frais)
    {
        $frais->setActionCommerciale($this);
        $this->frais[] = $frais;

        return $this;
    }

    /**
     * Remove frais
     *
     * @param \AppBundle\Entity\CRM\Frais $frais
     */
    public function removeFrai(\AppBundle\Entity\CRM\Frais $frais)
    {
        $this->frais->removeElement($frais);
    }

    /**
     * Get frais
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFrais()
    {
        return $this->frais;
    }

    public function hasFraisRefacturables(){
        foreach($this->bonsCommande as $bc){
            if(true === $bc->getFraisRefacturables()){
                return true;
            }
        }
        return false;
    }

    /**
     * Add recus
     *
     * @param \AppBundle\Entity\NDF\Recu $recus
     * @return Opportunite
     */
    public function addRecus(\AppBundle\Entity\NDF\Recu $recus)
    {
        $this->recus[] = $recus;

        return $this;
    }

    /**
     * Remove recus
     *
     * @param \AppBundle\Entity\NDF\Recu $recus
     */
    public function removeRecus(\AppBundle\Entity\NDF\Recu $recus)
    {
        $this->recus->removeElement($recus);
    }

    /**
     * Get recus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecus()
    {
        return $this->recus;
    }

    public function getFraisNonFactures(){
        $arr_frais = array();
        foreach($this->frais as $frais){
            if(null == $frais->getProduit()){
                $arr_frais[] = $frais;
            }
        }

        return $arr_frais;
    }

    public function getRecusValides(){
        $arr_valides = array();
        foreach($this->recus as $recu){
            if($recu->getLigneDepense()){
                if('VALIDE' == $recu->getLigneDepense()->getDepense()->getNoteFrais()->getEtat() || 'RAPPROCHE' == $recu->getLigneDepense()->getDepense()->getNoteFrais()->getEtat()){
                    $arr_valides[] = $recu;
                }
            }
        }

        return $arr_valides;
    }

    public function getRecusValidesNonFactures(){
        $arr_valides = array();
        foreach($this->recus as $recu){
            if(null == $recu->getProduit()){
                $arr_valides[] = $recu;
            }
        }

        return $arr_valides;
    }

    public function getTotalFraisNDF(){
        $total = 0;
        foreach($this->getRecus() as $recu){
            $total+= $recu->getMontantHT();
        }

        return $total;
    }

    public function getFraisSousTraitants(){
        $arr_frais = array();
        foreach($this->opportuniteSousTraitances as $sousTraitance){

            if(true === $sousTraitance->getFraisRefacturables()){

                foreach($sousTraitance->getRepartitions() as $repartition){
                    if($repartition->getFrais()){
                        $arr_frais[] = $repartition;
                    }                
                }
            }
        }

        return $arr_frais;
    }

    public function getFraisSousTraitantsNonFactures(){
        $arr_frais = array();
        foreach($this->opportuniteSousTraitances as $sousTraitance){

            if(true === $sousTraitance->getFraisRefacturables()){

                foreach($sousTraitance->getRepartitions() as $repartition){
                    if($repartition->getFrais() && null == $repartition->getProduit()){
                        $arr_frais[] = $repartition;
                    }                
                }
            }
        }

        return $arr_frais;
    }

    public function getTotalFraisSousTraitants(){
        $total = 0;
        foreach($this->getFraisSousTraitants() as $repartition ){
            $total+= $repartition->getFraisMonetaire();
        }
        return $total;
    }


    public function getTotalFraisManuels()
    {
        $total = 0;
        foreach($this->frais as $frais){
            $total+= $frais->getMontantHT();
        }
        return $total;
    }

    public function getTotalFrais(){
        return $this->getTotalFraisManuels()+$this->getTotalFraisNDF()+$this->getTotalFraisSousTraitants();
    }

    public function getTotalFraisFactures(){
        $totalFacture = 0;
        foreach($this->frais as $frais){
            if(null != $frais->getProduit()){
                $totalFacture+= $frais->getMontantHT();
            }
        }
        foreach($this->getRecusValides() as $recu){
            if(null != $recu->getProduit()){
                $totalFacture+= $recu->getMontantHT();
            }
        }
        foreach($this->getFraisSousTraitants() as $repartitionSousTraitance){
            if(null != $repartitionSousTraitance->getProduit()){
                $totalFacture+= $repartitionSousTraitance->getFraisMonetaire();
            }
        }

        return $totalFacture;
    }

    public function getTotalFraisNonFactures(){
        return $this->getTotalFrais()-$this->getTotalFraisFactures();
    }

    /**
     * Add temps
     *
     * @param \AppBundle\Entity\TimeTracker\Temps $temps
     * @return Opportunite
     */
    public function addTemp(\AppBundle\Entity\TimeTracker\Temps $temps)
    {
        $this->temps[] = $temps;

        return $this;
    }

    /**
     * Remove temps
     *
     * @param \AppBundle\Entity\TimeTracker\Temps $temps
     */
    public function removeTemp(\AppBundle\Entity\TimeTracker\Temps $temps)
    {
        $this->temps->removeElement($temps);
    }

    /**
     * Get temps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTemps()
    {
        return $this->temps;
    }

    public function getTempsTotal(){
        $total = 0;
        $total+= $this->tempsCommercial;
        foreach($this->getTemps() as $temps){
            $total+= $temps->getDuree();
        }

        return $total;
    }

    public function getTempsTotalSansCommercial(){
        $total = 0;
        foreach($this->getTemps() as $temps){
            $total+= $temps->getDuree();
        }

        return $total;
    }

    public function getTempsTotalAsString(){
        $total = $this->getTempsTotal();
        $hours = floor($total);
        $minutesDec = $total-$hours;
        $minutes = $minutesDec*60;
        
        return str_pad($hours, 2, 0, STR_PAD_LEFT).'h'.str_pad($minutes, 2, 0, STR_PAD_LEFT);
    }

    public function getTempsCommercialAsString(){
        $total = $this->tempsCommercial;
        $hours = floor($total);
        $minutesDec = $total-$hours;
        $minutes = $minutesDec*60;
        
        return str_pad($hours, 2, 0, STR_PAD_LEFT).'h'.str_pad($minutes, 2, 0, STR_PAD_LEFT);
    }

     public function getTempsSansCommercialAsString(){
        $total = $this->getTempsTotalSansCommercial();
        $hours = floor($total);
        $minutesDec = $total-$hours;
        $minutes = $minutesDec*60;
        
        return str_pad($hours, 2, 0, STR_PAD_LEFT).'h'.str_pad($minutes, 2, 0, STR_PAD_LEFT);
    }

    public function getTempsTotalMontant(){
        $montant = 0;
        foreach($this->getTemps() as $temps){
            if($temps->getUser()->getTauxHoraire()){
                $montant+= $temps->getDuree()*$temps->getUser()->getTauxHoraire();
            }
        }

        return $montant;
    }

    public function usersHaveTauxHoraires(){
        $ok = true;
        foreach($this->getTemps() as $temps){
             if(!$temps->getUser()->getTauxHoraire()){
                $ok = false;
            }
        }
        return $ok;
    }

    /**
     * Set termine
     *
     * @param boolean $termine
     * @return Opportunite
     */
    public function setTermine($termine)
    {
        $this->termine = $termine;

        return $this;
    }

    /**
     * Get termine
     *
     * @return boolean 
     */
    public function getTermine()
    {
        return $this->termine;
    }

    /**
     * Le montant réel rapport par une action commerciale 
     * en otant la sous-traitance et les frais si non refacturables
     **/ 
    public function getGainReel(){
        $gain = $this->getMontant();
        $gain-= $this->getMontantMonetaireSousTraitance();

        if(false == $this->hasFraisRefacturables()){
            $gain-= $this->getTotalFrais();
        }

        return $gain;

    }


    /**
     * Get devis
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDevis()
    {
        return $this->devis;
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
     * Set nouveauCompte
     *
     * @param boolean $nouveauCompte
     * @return Opportunite
     */
    public function setNouveauCompte($nouveauCompte)
    {
        $this->nouveauCompte = $nouveauCompte;

        return $this;
    }

    /**
     * Get nouveauCompte
     *
     * @return boolean 
     */
    public function getNouveauCompte()
    {
        return $this->nouveauCompte;
    }
}
