<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * PrevTableauBord
 *
 * @ORM\Table(name="prev_tableau_bord")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\PrevTableauBordRepository")
 */
class PrevTableauBord
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
     * @ORM\Column(name="mois", type="integer")
     */
    private $mois;

    /**
     * @var integer
     *
     * @ORM\Column(name="annee", type="integer")
     */
    private $annee;

    /**
     * @var integer
     *
     * @ORM\Column(name="montant", type="integer")
     */
    private $montant;

    /**
     * @var string
     *
     * @ORM\Column(name="poste", type="string", length=255)
     */
    private $poste;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $analytique;

     /**
     * @var boolean
     *
     * @ORM\Column(name="prive_or_public", type="string", length=6, nullable=true)
     */
    private $priveOrPublic;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
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
     * Set mois
     *
     * @param integer $mois
     * @return PrevTableauBord
     */
    public function setMois($mois)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get mois
     *
     * @return integer
     */
    public function getMois()
    {
        return $this->mois;
    }

    /**
     * Set annee
     *
     * @param integer $annee
     * @return PrevTableauBord
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @return integer
     */
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     * @return PrevTableauBord
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return integer
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set poste
     *
     * @param string $poste
     * @return PrevTableauBord
     */
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste
     *
     * @return string
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return PrevTableauBord
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
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     * @return PrevTableauBord
     */
    public function setCompany(\AppBundle\Entity\Company $company = null)
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

    /**
     * Get montant monetaire
     *
     * @return float
     */
    public function getMontantMonetaire()
    {
        return $this->montant/100;
    }

    /**
     * Get montant montaire
     *
     * @return integer
     */
    public function setMontantMonetaire($montant)
    {
        return $this->montant = $montant*100;
    }

    /**
     * Set priveOrPublic
     *
     * @param string $priveOrPublic
     * @return PrevTableauBord
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
}
