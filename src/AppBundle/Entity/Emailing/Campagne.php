<?php

namespace AppBundle\Entity\Emailing;

use Doctrine\ORM\Mapping as ORM;

/**
 * Campagne
 *
 * @ORM\Table(name="campagne")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Emailing\CampagneRepository")
 */
class Campagne
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
     * @ORM\Column(name="nom", type="string", length=50)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="objet", type="string", length=255)
     */
    private $objet;

    /**
     * @var string
     *
     * @ORM\Column(name="html", type="text")
     */
    private $html;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_expediteur", type="string", length=100)
     */
    private $nomExpediteur;

    /**
     * @var string
     *
     * @ORM\Column(name="email_expediteur", type="string", length=100)
     */
    private $emailExpediteur;

     /**
     * @var boolean
     *
     * @ORM\Column(name="envoyee", type="boolean")
     */
    private $envoyee = false;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_envoi", type="datetime", nullable=true)
     */
    private $dateEnvoi;

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
    *
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Emailing\CampagneContact", mappedBy="campagne", cascade={"persist", "remove"}, orphanRemoval=true)
    *
    */
    private $campagneContacts;
 

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
     * @return Campagne
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
     * Set objet
     *
     * @param string $objet
     * @return Campagne
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string 
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Set html
     *
     * @param string $html
     * @return Campagne
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string 
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set nomExpediteur
     *
     * @param string $nomExpediteur
     * @return Campagne
     */
    public function setNomExpediteur($nomExpediteur)
    {
        $this->nomExpediteur = $nomExpediteur;

        return $this;
    }

    /**
     * Get nomExpediteur
     *
     * @return string 
     */
    public function getNomExpediteur()
    {
        return $this->nomExpediteur;
    }

    /**
     * Set emailExpediteur
     *
     * @param string $emailExpediteur
     * @return Campagne
     */
    public function setEmailExpediteur($emailExpediteur)
    {
        $this->emailExpediteur = $emailExpediteur;

        return $this;
    }

    /**
     * Get emailExpediteur
     *
     * @return string 
     */
    public function getEmailExpediteur()
    {
        return $this->emailExpediteur;
    }

    /**
     * Set envoyee
     *
     * @param boolean $envoyee
     * @return Campagne
     */
    public function setEnvoyee($envoyee)
    {
        $this->envoyee = $envoyee;

        return $this;
    }

    /**
     * Get envoyee
     *
     * @return boolean 
     */
    public function getEnvoyee()
    {
        return $this->envoyee;
    }

    /**
     * Set dateEnvoi
     *
     * @param \DateTime $dateEnvoi
     * @return Campagne
     */
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * Get dateEnvoi
     *
     * @return \DateTime 
     */
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Campagne
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
     * @return Campagne
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
     * @return Campagne
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
     * @return Campagne
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
     * Constructor
     */
    public function __construct()
    {
        $this->campagneContacts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add campagneContacts
     *
     * @param \AppBundle\Entity\Emailing\CampagneContact $campagneContacts
     * @return Campagne
     */
    public function addCampagneContact(\AppBundle\Entity\Emailing\CampagneContact $campagneContacts)
    {
        $campagneContacts->setCampagne($this);
        $this->campagneContacts[] = $campagneContacts;

        return $this;
    }

    /**
     * Remove campagneContacts
     *
     * @param \AppBundle\Entity\Emailing\CampagneContact $campagneContacts
     */
    public function removeCampagneContact(\AppBundle\Entity\Emailing\CampagneContact $campagneContacts)
    {
        $this->campagneContacts->removeElement($campagneContacts);
    }

    /**
     * Get campagneContacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampagneContacts()
    {
        return $this->campagneContacts;
    }

    public function getDestinataires(){

        $arr_destinataires = array();
        foreach($this->campagneContacts as $campagneContact){
            $arr_destinataires[] = $campagneContact->getContact()->getEmail();
        }
        return $arr_destinataires;
    }
}
