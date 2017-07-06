<?php

namespace AppBundle\Entity\NDF;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recu
 *
 * @ORM\Table(name="recu")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\RecuRepository")
 */
class Recu
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
     * @ORM\Column(name="file", type="string", length=255)
     */
    private $file;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="fournisseur", type="string", length=255, nullable=true)
     */
    private $fournisseur;

    /**
     * @var float
     *
     * @ORM\Column(name="montantHT", type="float", nullable=true)
     */
    private $montantHT;

    /**
     * @var float
     *
     * @ORM\Column(name="tva", type="float", nullable=true)
     */
    private $tva;

    /**
     * @var float
     *
     * @ORM\Column(name="montantTTC", type="float", nullable=true)
     */
    private $montantTTC;

    /**
     * @var string
     *
     * @ORM\Column(name="etat", type="string", length=10, nullable=true)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true)
     */
    private $analytique;

     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteComptable;

    /**
    * @ORM\OneToOne(targetEntity="AppBundle\Entity\Compta\LigneDepense", cascade={"remove"})
    * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
    */
   private $ligneDepense;

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
    private $user;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set file
     *
     * @param string $file
     * @return Recu
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Recu
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
     * Set fournisseur
     *
     * @param string $fournisseur
     * @return Recu
     */
    public function setFournisseur($fournisseur)
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    /**
     * Get fournisseur
     *
     * @return string
     */
    public function getFournisseur()
    {
        return $this->fournisseur;
    }

    /**
     * Set montantHT
     *
     * @param float $montantHT
     * @return Recu
     */
    public function setMontantHT($montantHT)
    {
        $this->montantHT = $montantHT;

        return $this;
    }

    /**
     * Get montantHT
     *
     * @return float
     */
    public function getMontantHT()
    {
        return $this->montantHT;
    }

    /**
     * Set tva
     *
     * @param float $tva
     * @return Recu
     */
    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    /**
     * Get tva
     *
     * @return float
     */
    public function getTva()
    {
        return $this->tva;
    }

    /**
     * Set montantTTC
     *
     * @param float $montantTTC
     * @return Recu
     */
    public function setMontantTTC($montantTTC)
    {
        $this->montantTTC = $montantTTC;

        return $this;
    }

    /**
     * Get montantTTC
     *
     * @return float
     */
    public function getMontantTTC()
    {
        return $this->montantTTC;
    }

    /**
     * Set etat
     *
     * @param string $etat
     * @return Recu
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
     * @return Recu
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
     * @return Recu
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
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return Recu
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
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return Recu
     */
    public function setCompteComptable(\AppBundle\Entity\Compta\CompteComptable $compteComptable = null)
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
     * Set userCreation
     *
     * @param \AppBundle\Entity\User $userCreation
     * @return Recu
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
     * @return Recu
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Recu
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

    public function getPath(){
      return 'web/upload/ndf/'.$this->user->getCompany()->getId().'/'.$this->user->getId();
    }

    public function getFileType(){
		    return pathinfo($this->getPath().'/'.$this->file, PATHINFO_EXTENSION);
    }

    public function __toString(){
      return $this->date->format("d/m/Y").' | '.$this->getFournisseur().' | '.$this->getMontantTTC().' €';
    }


    /**
     * Set ligneDepense
     *
     * @param \AppBundle\Entity\Compta\LigneDepense $ligneDepense
     * @return Recu
     */
    public function setLigneDepense(\AppBundle\Entity\Compta\LigneDepense $ligneDepense = null)
    {
        $this->ligneDepense = $ligneDepense;

        return $this;
    }

    /**
     * Get ligneDepense
     *
     * @return \AppBundle\Entity\Compta\LigneDepense
     */
    public function getLigneDepense()
    {
        return $this->ligneDepense;
    }
}
