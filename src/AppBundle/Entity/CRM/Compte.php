<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * Compte
 *
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\CompteRepository")
 * @ORM\Table(name="compte")
 */
class Compte
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
     * @ORM\Column(name="nom", type="string", length=255, unique=false)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", nullable=true, unique=false)
     */
    private $telephone;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", nullable=true, unique=false)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255, nullable=true)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="code_postal", type="string", length=255, nullable=true)
     */
    private $codePostal;

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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Assert\Url()
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Compte", inversedBy="compteEnfants")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteParent;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\Compte", mappedBy="compteParent")
     */
    private $compteEnfants;

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
     * @ORM\Column(name="code_evoliz", type="string", nullable=true)
     */
    private $codeEvoliz;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @var boolean
     *
     * @ORM\Column(name="client", type="boolean", nullable=true)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $compteComptableClient;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fournisseur", type="boolean", nullable=true)
     */
    private $fournisseur;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $compteComptableFournisseur;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $secteurActivite;

    /**
     * @var boolean
     *
     * @ORM\Column(name="prive_or_public", type="string", nullable=true)
     */
    private $priveOrPublic;
    
    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\DocumentPrix", mappedBy="compte", cascade={"persist", "remove"})
    */    
    private $documentPrixs;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\Opportunite", mappedBy="compte", cascade={"persist", "remove"})
    */    
    private $opportunites;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\CRM\Contact", mappedBy="compte", cascade={"persist", "remove"})
    */    
    private $contacts;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Depense", mappedBy="compte", cascade={"persist", "remove"})
    */ 
    private $depenses;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->documentPrixs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->opportunites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
    }    
    
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
     * Set nom
     *
     * @param string $nom
     * @return Compte
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
     * Set telephone
     *
     * @param string $telephone
     * @return Compte
     */
    public function setTelephone($telephone)
    {
        $this->telephone = preg_replace('/[^0-9\/+\ ]/', '', $telephone);

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->telephone);
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return Compte
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
     * Set adresse
     *
     * @param string $adresse
     * @return Compte
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
     * Set ville
     *
     * @param string $ville
     * @return Compte
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
     * Set codePostal
     *
     * @param string $codePostal
     * @return Compte
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
     * Set region
     *
     * @param string $region
     * @return Compte
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
     * @return Compte
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Compte
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
     * @return Compte
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
     * Set description
     *
     * @param string $description
     * @return Compte
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
     * Set url
     *
     * @param string $url
     * @return Compte
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
     * Get compte_parent
     *
     * @return Compte
     */
    public function getCompteParent()
    {
        return $this->compteParent;
    }

    /**
     * Set compte_parent
     *
     * @param Compte $compteParent
     * @return Compte
     */
    public function setCompteParent($compteParent)
    {
        $this->compteParent = $compteParent;
        return $this;
    }


    public function __toString()
    {
        return $this->getNom();
    }

    /**
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return Compte
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
     * @return Compte
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
     * @return Compte
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
    public function getCodeEvoliz() {
        return $this->codeEvoliz;
    }
    public function setCodeEvoliz($codeEvoliz) {
        $this->codeEvoliz = $codeEvoliz;
        return $this;
    }
    public function getCompany() {
        return $this->company;
    }
    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    /**
     * Set client
     *
     * @param boolean $client
     * @return Compte
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return boolean
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set fournisseur
     *
     * @param boolean $fournisseur
     * @return Compte
     */
    public function setFournisseur($fournisseur)
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    /**
     * Get fournisseur
     *
     * @return boolean
     */
    public function getFournisseur()
    {
        return $this->fournisseur;
    }

    /**
     * Set compteComptableClient
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptableClient
     * @return Compte
     */
    public function setCompteComptableClient(\AppBundle\Entity\Compta\CompteComptable $compteComptableClient)
    {
        $this->compteComptableClient = $compteComptableClient;

        return $this;
    }

    /**
     * Get compteComptableClient
     *
     * @return \AppBundle\Entity\Compta\CompteComptable
     */
    public function getCompteComptableClient()
    {
        return $this->compteComptableClient;
    }

    /**
     * Set compteComptableFournisseur
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptableFournisseur
     * @return Compte
     */
    public function setCompteComptableFournisseur(\AppBundle\Entity\Compta\CompteComptable $compteComptableFournisseur)
    {
        $this->compteComptableFournisseur = $compteComptableFournisseur;

        return $this;
    }

    /**
     * Get compteComptableFournisseur
     *
     * @return \AppBundle\Entity\Compta\CompteComptable
     */
    public function getCompteComptableFournisseur()
    {
        return $this->compteComptableFournisseur;
    }

    /**
     * Set secteurActivite
     *
     * @param \AppBundle\Entity\Settings $secteurActivite
     * @return Compte
     */
    public function setSecteurActivite(\AppBundle\Entity\Settings $secteurActivite = null)
    {
        $this->secteurActivite = $secteurActivite;

        return $this;
    }

    /**
     * Get secteurActivite
     *
     * @return \AppBundle\Entity\Settings 
     */
    public function getSecteurActivite()
    {
        return $this->secteurActivite;
    }

    /**
     * Set priveOrPublic
     *
     * @param string $priveOrPublic
     * @return Compte
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
        } elseif($this->priveOrPublic == "PUBLIC")  {
          return 'Public';
        }

        return 'N/A';
    }
    
    /**
     * Add documentPrix
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $documentPrix
     * @return Compte
     */
    public function addDocumentPrix(\AppBundle\Entity\CRM\DocumentPrix $documentPrix)
    {
    	$documentPrix->setCompte($this);
        $this->documentPrixs[] = $documentPrix;

        return $this;
    }

    /**
     * Remove documentPrix
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $documentPrix
     */
    public function removeDocumentPrix(\AppBundle\Entity\CRM\DocumentPrix $documentPrix)
    {
        $this->documentPrixs->removeElement($documentPrix);
    }

    /**
     * Get documentPrix
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocumentPrixs()
    {
        return $this->documentPrixs;
    }    

    /**
     * Add opportunites
     *
     * @param \AppBundle\Entity\CRM\Opportunite $opportunite
     * @return Compte
     */
    public function addOpportunite(\AppBundle\Entity\CRM\Opportunite $opportunite)
    {
        $opportunite->setCompte($this);
        $this->opportunites[] = $opportunite;

        return $this;
    }

    /**
     * Remove opportunites
     *
     * @param \AppBundle\Entity\CRM\Opportunite $opportunite
     */
    public function removeOpportunite(\AppBundle\Entity\CRM\Opportunite $opportunite)
    {
        $this->opportunites->removeElement($opportunite);
    }

    /**
     * Get opportunites
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpportunites()
    {
        return $this->opportunites;
    }

    /**
     * Add contact
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     * @return Compte
     */
    public function addContact(\AppBundle\Entity\CRM\Contact $contact)
    {
        $contact->setCompte($this);
        $this->contacts[] = $contact;

        return $this;
    }

    /**
     * Remove contacts
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     */
    public function removeContact(\AppBundle\Entity\CRM\Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add depenses
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return Compte
     */
    public function addDepense(\AppBundle\Entity\Compta\Depense $depense)
    {
        $depense->setCompte($this);
        $this->depenses[] = $depense;

        return $this;
    }

    /**
     * Remove depenses
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     */
    public function removeDepense(\AppBundle\Entity\Compta\Depense $depense)
    {
        $this->depenses->removeElement($depense);
    }

    /**
     * Get depense
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepenses()
    {
        return $this->depenses;
    }

    /**
     * Add compteEnfants
     *
     * @param \AppBundle\Entity\CRM\Compte $compteEnfants
     * @return Compte
     */
    public function addCompteEnfant(\AppBundle\Entity\CRM\Compte $compteEnfants)
    {
        $this->compteEnfants[] = $compteEnfants;

        return $this;
    }

    /**
     * Remove compteEnfants
     *
     * @param \AppBundle\Entity\CRM\Compte $compteEnfants
     */
    public function removeCompteEnfant(\AppBundle\Entity\CRM\Compte $compteEnfants)
    {
        $this->compteEnfants->removeElement($compteEnfants);
    }

    /**
     * Get compteEnfants
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompteEnfants()
    {
        return $this->compteEnfants;
    }
}
