<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactWebForm
 *
 * @ORM\Table(name="contact_web_form")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\ContactWebFormRepository")
 */
class ContactWebForm
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
     * @ORM\Column(name="nom_formulaire", type="string", length=255)
     */
    private $nomFormulaire;
    
    /**
     * @var string
     *
     * @ORM\Column(name="return_url", type="string", length=255)
     */
    private $returnUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_compte", type="string", length=255, nullable=true)
     */
    private $nomCompte;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom_contact", type="string", length=255, nullable=true)
     */
    private $prenomContact;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_contact", type="string", length=255, nullable=true)
     */
    private $nomContact;

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
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userGestion;

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
     * @var boolean
     *
     * @ORM\Column(name="carte_voeux", type="boolean")
     */
    private $carteVoeux;

    /**
     * @var boolean
     *
     * @ORM\Column(name="envoyer_email", type="boolean", nullable=true)
     */
    private $envoyerEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="copie_email", type="boolean", nullable=true)
     */
    private $copieEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="expediteur", type="string", length=255, nullable=true)
     */
    private $expediteur;

    /**
     * @var string
     *
     * @ORM\Column(name="objet_email", type="string", length=255, nullable=true)
     */
    private $objetEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="corps_email", type="text", nullable=true)
     */
    private $corpsEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="newsletter", type="boolean")
     */
    private $newsletter;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $gestionnaireSuivi;

    /**
     * @var integer
     *
     * @ORM\Column(name="delaiNum", type="integer", nullable=true)
     */
    private $delaiNum;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="delaiUnit", type="string", length=10, nullable=true)
     */
    private $delaiUnit;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

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
     * Set nomFormulaire
     *
     * @param string $nomFormulaire
     * @return ContactWebForm
     */
    public function setNomFormulaire($nomFormulaire)
    {
        $this->nomFormulaire = $nomFormulaire;

        return $this;
    }

    /**
     * Get nomFormulaire
     *
     * @return string 
     */
    public function getNomFormulaire()
    {
        return $this->nomFormulaire;
    }

    /**
     * Set nomCompte
     *
     * @param string $nomCompte
     * @return ContactWebForm
     */
    public function setNomCompte($nomCompte)
    {
        $this->nomCompte = $nomCompte;

        return $this;
    }

    /**
     * Get nomCompte
     *
     * @return string 
     */
    public function getNomCompte()
    {
        return $this->nomCompte;
    }

    /**
     * Set prenomContact
     *
     * @param string $prenomContact
     * @return ContactWebForm
     */
    public function setPrenomContact($prenomContact)
    {
        $this->prenomContact = $prenomContact;

        return $this;
    }

    /**
     * Get prenomContact
     *
     * @return string 
     */
    public function getPrenomContact()
    {
        return $this->prenomContact;
    }

    /**
     * Set nomContact
     *
     * @param string $nomContact
     * @return ContactWebForm
     */
    public function setNomContact($nomContact)
    {
        $this->nomContact = $nomContact;

        return $this;
    }

    /**
     * Get nomContact
     *
     * @return string 
     */
    public function getNomContact()
    {
        return $this->nomContact;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return ContactWebForm
     */
    public function setAdresse($addresse)
    {
        $this->adresse = $addresse;

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
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * Set telephoneFixe
     *
     * @param string $telephoneFixe
     * @return ContactWebForm
     */
    public function setTelephoneFixe($telephoneFixe)
    {
        $this->telephoneFixe = $telephoneFixe;

        return $this;
    }

    /**
     * Get telephoneFixe
     *
     * @return string 
     */
    public function getTelephoneFixe()
    {
        return $this->telephoneFixe;
    }

    /**
     * Set telephonePortable
     *
     * @param string $telephonePortable
     * @return ContactWebForm
     */
    public function setTelephonePortable($telephonePortable)
    {
        $this->telephonePortable = $telephonePortable;

        return $this;
    }

    /**
     * Get telephonePortable
     *
     * @return string 
     */
    public function getTelephonePortable()
    {
        return $this->telephonePortable;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return ContactWebForm
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
     * Set fax
     *
     * @param string $fax
     * @return ContactWebForm
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return ContactWebForm
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set typeRelationCommerciale
     *
     * @param string $typeRelationCommerciale
     * @return ContactWebForm
     */
    public function setTypeRelationCommerciale($typeRelationCommerciale)
    {
        $this->typeRelationCommerciale = $typeRelationCommerciale;

        return $this;
    }

    /**
     * Get typeRelationCommerciale
     *
     * @return string 
     */
    public function getTypeRelationCommerciale()
    {
        return $this->typeRelationCommerciale;
    }

    /**
     * Set reseau
     *
     * @param string $reseau
     * @return ContactWebForm
     */
    public function setReseau($reseau)
    {
        $this->reseau = $reseau;

        return $this;
    }

    /**
     * Get reseau
     *
     * @return string 
     */
    public function getReseau()
    {
        return $this->reseau;
    }

    /**
     * Set origine
     *
     * @param string $origine
     * @return ContactWebForm
     */
    public function setOrigine($origine)
    {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return string 
     */
    public function getOrigine()
    {
        return $this->origine;
    }

    /**
     * Set carteVoeux
     *
     * @param boolean $carteVoeux
     * @return ContactWebForm
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
     * Set envoyerEmail
     *
     * @param boolean $envoyerEmail
     * @return ContactWebForm
     */
    public function setEnvoyerEmail($envoyerEmail)
    {
        $this->envoyerEmail = $envoyerEmail;

        return $this;
    }

    /**
     * Get envoyerMail
     *
     * @return boolean
     */
    public function getEnvoyerEmail()
    {
        return $this->envoyerEmail;
    }

    /**
     * Set copieEmail
     *
     * @param boolean $copieEmail
     * @return ContactWebForm
     */
    public function setCopieEmail($copieEmail)
    {
        $this->copieEmail = $copieEmail;

        return $this;
    }

    /**
     * Get copieEmail
     *
     * @return boolean
     */
    public function getCopieEmail()
    {
        return $this->copieEmail;
    }

    /**
     * Set corpsEmail
     *
     * @param boolean $corpsEmail
     * @return ContactWebForm
     */
    public function setCorpsEmail($corpsEmail)
    {
        $this->corpsEmail = $corpsEmail;

        return $this;
    }

    /**
     * Get corpsEmail
     *
     * @return boolean
     */
    public function getCorpsEmail()
    {
        return $this->corpsEmail;
    }

    /**
     * Set objetEmail
     *
     * @param boolean $objetEmail
     * @return ContactWebForm
     */
    public function setObjetEmail($objetEmail)
    {
        $this->objetEmail = $objetEmail;

        return $this;
    }

    /**
     * Get $objetEmail
     *
     * @return boolean
     */
    public function getObjetEmail()
    {
        return $this->objetEmail;
    }

    /**
     * Set expediteur
     *
     * @param boolean $expediteur
     * @return ContactWebForm
     */
    public function setExpediteur($expediteur)
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    /**
     * Get expediteur
     *
     * @return boolean
     */
    public function getExpediteur()
    {
        return $this->expediteur;
    }

    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     * @return ContactWebForm
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return boolean 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set themeInteret
     *
     * @param string $themeInteret
     * @return ContactWebForm
     */
    public function setThemeInteret($themeInteret)
    {
        $this->themeInteret = $themeInteret;

        return $this;
    }

    /**
     * Get themeInteret
     *
     * @return string 
     */
    public function getThemeInteret()
    {
        return $this->themeInteret;
    }

    /**
     * Set servicesInteret
     *
     * @param string $servicesInteret
     * @return ContactWebForm
     */
    public function setServicesInteret($servicesInteret)
    {
        $this->servicesInteret = $servicesInteret;

        return $this;
    }

    /**
     * Get servicesInteret
     *
     * @return string 
     */
    public function getServicesInteret()
    {
        return $this->servicesInteret;
    }

   /**
     * Set gestionnaireSuivi
     *
     * @param \AppBundle\Entity\User $gestionnaireSuivi
     * @return ContactWebForm
     */
    public function setGestionnaireSuivi($gestionnaireSuivi)
    {
        $this->gestionnaireSuivi = $gestionnaireSuivi;

        return $this;
    }

    /**
     * Get gestionnaireSuivi
     *
     * @return \AppBundle\Entity\User  
     */
    public function getGestionnaireSuivi()
    {
        return $this->gestionnaireSuivi;
    }

    /**
     * Set delaiNum
     *
     * @param integer $delaiNum
     * @return ContactWebForm
     */
    public function setDelaiNum($delaiNum)
    {
        $this->delaiNum = $delaiNum;

        return $this;
    }

    /**
     * Get delaiNum
     *
     * @return integer 
     */
    public function getDelaiNum()
    {
        return $this->delaiNum;
    }

    /**
     * Set delaiUnit
     *
     * @param string $delaiUnit
     * @return ContactWebForm
     */
    public function setDelaiUnit($delaiUnit)
    {
        $this->delaiUnit = $delaiUnit;

        return $this;
    }

    /**
     * Get delaiUnit
     *
     * @return string 
     */
    public function getDelaiUnit()
    {
        return $this->delaiUnit;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * @return ContactWebForm
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
     * @return ContactWebForm
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






    /**
     * Add settings
     *
     * @param \AppBundle\Entity\Settings $settings
     * @return ContactWebForm
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
     * Constructor
     */
    public function __construct()
    {
        $this->settings = new \Doctrine\Common\Collections\ArrayCollection();
    }
	public function getReturnUrl() {
		return $this->returnUrl;
	}
	public function setReturnUrl($returnUrl) {
		$this->returnUrl = $returnUrl;
		return $this;
	}
	



    /**
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     * @return ContactWebForm
     */
    public function setCompany(\AppBundle\Entity\Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }
}
