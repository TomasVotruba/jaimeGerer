<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prospection
 *
 * @ORM\Table(name="prospection")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\ProspectionRepository")
 */
class Prospection
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
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbre_jour", type="integer", length=255, nullable=true)
     */
    private $nbreJour;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbre_affichage", type="integer", length=255, nullable=true)
     */
    private $nbreAffichage;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\CRM\Rapport")
     * @ORM\JoinColumn(nullable=true)
     */
    private $rapport;
    
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
     * @var \DateTime
     *
     * @ORM\Column(name="date_last_open", type="date", nullable=true)
     */
    private $dateLastOpen;

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
     * Set nom
     *
     * @param string $nom
     * @return Prospection
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
     * Set nbreAffichage
     *
     * @param string $nbreAffichage
     * @return Prospection
     */
    public function setNbreAffichage($nbreAffichage = 20)
    {
        $this->nbreAffichage = $nbreAffichage;

        return $this;
    }

    /**
     * Get nbreAffichage
     *
     * @return string 
     */
    public function getNbreAffichage()
    {
        return $this->nbreAffichage;
    }

    /**
     * Set nbreJour
     *
     * @param string $nbreJour
     * @return Prospection
     */
    public function setNbreJour($nbreJour = 0)
    {
        $this->nbreJour = $nbreJour;

        return $this;
    }

    /**
     * Get nbreJour
     *
     * @return string 
     */
    public function getNbreJour()
    {
        return $this->nbreJour;
    }

    /**
     * Set rapport
     *
     * @param \AppBundle\Entity\CRM\Rapport $rapport
     * @return Prospection
     */
    public function setRapport(\AppBundle\Entity\CRM\Rapport $rapport)
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get rapport
     *
     * @return \AppBundle\Entity\CRM\Rapport
     */
    public function getRapport()
    {
        return $this->rapport;
    }

    /**
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return Prospection
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
     * @return Prospection
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Prospection
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
     * @return Prospection
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
     * Set dateLastOpen
     *
     * @param \DateTime $dateLastOpen
     * @return Prospection
     */
    public function setDateLastOpen($dateLastOpen)
    {
        $this->dateLastOpen = $dateLastOpen;

        return $this;
    }

    /**
     * Get dateLastOpen
     *
     * @return \DateTime 
     */
    public function getDateLastOpen()
    {
        return $this->dateLastOpen;
    }

	public function getCompany() {
		return $this->company;
	}

	public function setCompany($company) {
		$this->company = $company;
		return $this;
	}
}
