<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Doctrine\Common\Collections\Criteria;

/**
 * Contact
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\ContactRepository")
 * @ORM\Table(name="contact") 
 * 
 */
class Contact
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
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="civilite", type="string", length=5, nullable=true)
     */
    private $civilite;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="telephone_fixe", type="string", length=255, nullable=true)
     */
    private $telephoneFixe;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone_portable", type="string", length=255, nullable=true)
     */
    private $telephonePortable;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone_autres", type="string", length=255, nullable=true)
     */
    private $telephoneAutres;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email2", type="string", length=255, nullable=true)
     * @Assert\Email()
     */
    private $email2;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=255, nullable=true)
     */
    private $codePostal;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=255, nullable=true)
     */
    private $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, nullable=true)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     */
    private $fax;

    /**
     * @var boolean
     *
     * @ORM\Column(name="blackliste", type="boolean")
     */
    private $blackliste = false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="carte_voeux", type="boolean")
     */
    private $carteVoeux = false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="rejet_email", type="boolean")
     */
    private $rejetEmail = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="newsletter", type="boolean")
     */
    private $newsletter = false;
    
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte")
     * @ORM\JoinColumn(nullable=true)
     * @Assert\NotBlank()
     */
    private $compte;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $reseau;
    
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
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\PriseContact", mappedBy="contact", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     */
    private $priseContacts;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_only_prospect", type="boolean")
     */
    private $isOnlyProspect = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="import_mautic_status", type="boolean")
     */
    private $importMauticStatus = false;


    /**
     * Set id
     *
     * @param int $id
     * @return Compte
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
     * Set prenom
     *
     * @param string $prenom
     * @return Contact
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Contact
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
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return Contact
     */
    public function setTelephoneFixe($telephoneFixe)
    {
        $this->telephoneFixe = preg_replace('/[^0-9\/+\ ]/', '', $telephoneFixe);

        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string 
     */
    public function getTelephoneFixe()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->telephoneFixe);
    }

    /**
     * Set telephonePortable
     *
     * @param string $telephonePortable
     * @return Contact
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephonePortable = preg_replace('/[^0-9\/+\ ]/', '', $telephonePortable);

        return $this;
    }

    /**
     * Get telephonePortable
     *
     * @return string 
     */
    public function getTelephonePortable()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->telephonePortable);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return Contact
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return Contact
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string 
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Contact
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Contact
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return Contact
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return string 
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Contact
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
     * Set titre
     *
     * @param string $titre
     * @return Contact
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Contact
     */
    public function setFax($fax)
    {
        $this->fax = preg_replace('/[^0-9\/+\ ]/', '', $fax);

        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->fax);
    }

    /**
     * Set blackliste
     *
     * @param boolean $blackliste
     * @return Contact
     */
    public function setBlackliste($blackliste)
    {
        $this->blackliste = $blackliste;

        return $this;
    }

    /**
     * Get blackliste
     *
     * @return boolean 
     */
    public function getBlackliste()
    {
        return $this->blackliste;
    }

    /**
     * Set carteVoeux
     *
     * @param boolean $carteVoeux
     * @return Contact
     */
    public function setCarteVoeux($carteVoeux)
    {
        $this->carteVoeux = $carteVoeux;

        return $this;
    }

    /**
     * Get carteVoeux
     *
     * @return boolean 
     */
    public function getCarteVoeux()
    {
        return $this->carteVoeux;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Contact
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
     * @return Contact
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
     * Get compte
     *
     * @return Compte
     */
    public function getCompte()
    {
    	return $this->compte;
    }
    
    /**
     * Set compte
     *
     * @param Compte $compte
     * @return Compte
     */
    public function setCompte($compte)
    {
    	$this->compte = $compte;
    	return $this;
    }
    

    /**
     * Set reseau
     *
     * @param \AppBundle\Entity\Settings $reseau
     * @return Contact
     */
    public function setReseau(\AppBundle\Entity\Settings $reseau)
    {
        $this->reseau = $reseau;

        return $this;
    }

    /**
     * Get reseau
     *
     * @return \AppBundle\Entity\Settings 
     */
    public function getReseau()
    {
        return $this->reseau;
    }

    /**
     * Set origine
     *
     * @param \AppBundle\Entity\Settings $origine
     * @return Contact
     */
    public function setOrigine(\AppBundle\Entity\Settings $origine)
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
     * Add settings
     *
     * @param \AppBundle\Entity\Settings $setting
     * @return Contact
     */
    public function addSetting(\AppBundle\Entity\Settings $setting)
    {
    	$found = false;

    	if( isset($this->settings) ){
			foreach($this->settings as $old_setting){
				if($old_setting->getId() == $setting->getId()){
					$found = true;
				}
			}
    	}
    	
    	if(!$found){
       		$this->settings[] = $setting;
    	}
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
     * Constructor
     */
    public function __construct()
    {
        $this->settings = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return Contact
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
     * @return Contact
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
     * Set userGestion
     *
     * @param \AppBundle\Entity\User $userGestion
     * @return Contact
     */
    public function setUserGestion(\AppBundle\Entity\User $userGestion)
    {
        $this->userGestion = $userGestion;

        return $this;
    }

    /**
     * Get userGestion
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUserGestion()
    {
        return $this->userGestion;
    }
    
    public function __toString()
    {
    	return $this->getPrenom().' '.$this->getNom();
    }
    
    public function getFullName()
    {
    	return $this->getPrenom().' '.$this->getNom();
    }
      

    /**
     * Add priseContacts
     *
     * @param \AppBundle\Entity\CRM\PriseContact $priseContacts
     * @return Contact
     */
    public function addPriseContact(\AppBundle\Entity\CRM\PriseContact $priseContacts)
    {
        $this->priseContacts[] = $priseContacts;

        return $this;
    }

    /**
     * Remove priseContacts
     *
     * @param \AppBundle\Entity\CRM\PriseContact $priseContacts
     */
    public function removePriseContact(\AppBundle\Entity\CRM\PriseContact $priseContacts)
    {
        $this->priseContacts->removeElement($priseContacts);
    }

    /**
     * Get priseContacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPriseContacts()
    {
         $criteria = Criteria::create()
	      ->orderBy(array("date" => Criteria::DESC));
	
	    return $this->priseContacts->matching($criteria);
    }
	public function getRejetEmail() {
		return $this->rejetEmail;
	}
	public function setRejetEmail($rejetEmail) {
		$this->rejetEmail = $rejetEmail;
		return $this;
	}
	public function getNewsletter() {
		return $this->newsletter;
	}
	public function setNewsletter($newsletter) {
		$this->newsletter = $newsletter;
		return $this;
	}
	public function getCivilite() {
		return $this->civilite;
	}
	public function setCivilite($civilite) {
		$this->civilite = $civilite;
		return $this;
	}
	
	
	

    /**
     * Set telephoneAutres
     *
     * @param string $telephoneAutres
     * @return Contact
     */
    public function setTelephoneAutres($telephoneAutres)
    {
        $this->telephoneAutres = $telephoneAutres;

        return $this;
    }

    /**
     * Get telephoneAutres
     *
     * @return string 
     */
    public function getTelephoneAutres()
    {
        return $this->telephoneAutres;
    }

    /**
     * Set email2
     *
     * @param string $email2
     * @return Contact
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;

        return $this;
    }

    /**
     * Get email2
     *
     * @return string 
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * Set isOnlyProspect
     *
     * @param boolean $isOnlyProspect
     * @return Contact
     */
    public function setIsOnlyProspect($isOnlyProspect)
    {
        $this->isOnlyProspect = $isOnlyProspect;

        return $this;
    }

    /**
     * Get isOnlyProspect
     *
     * @return boolean 
     */
    public function getIsOnlyProspect()
    {
        return $this->isOnlyProspect;
    }

    /**
     * Set importMauticStatus
     *
     * @param boolean $importMauticStatus
     * @return Contact
     */
    public function setImportMauticStatus($importMauticStatus)
    {
        $this->importMauticStatus = $importMauticStatus;

        return $this;
    }

    /**
     * Get importMauticStatus
     *
     * @return boolean 
     */
    public function getImportMauticStatus()
    {
        return $this->importMauticStatus;
    }
}
