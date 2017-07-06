<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rapprochement
 *
 * @ORM\Table(name="rapprochement")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\RapprochementRepository")
 */
class Rapprochement
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\MouvementBancaire", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=false, unique=true)
     */
    private $mouvementBancaire;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Accompte", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $accompte;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Avoir", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $avoir;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\RemiseCheque", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $remiseCheque;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\AffectationDiverse", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $affectationDiverse;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NDF\NoteFrais", inversedBy="rapprochements")
     * @ORM\JoinColumn(nullable=true)
     */
    private $noteFrais;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;


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
     * Set mouvementBancaire
     *
     * @param \AppBundle\Entity\Compta\MouvementBancaire $mouvementBancaire
     * @return Rapprochement
     */
    public function setMouvementBancaire(\AppBundle\Entity\Compta\MouvementBancaire $mouvementBancaire)
    {
        $this->mouvementBancaire = $mouvementBancaire;

        return $this;
    }

    /**
     * Get mouvementBancaire
     *
     * @return \AppBundle\Entity\Compta\MouvementBancaire
     */
    public function getMouvementBancaire()
    {
        return $this->mouvementBancaire;
    }

    /**
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return Rapprochement
     */
    public function setDepense(\AppBundle\Entity\Compta\Depense $depense = null)
    {
        $this->depense = $depense;

        return $this;
    }

    /**
     * Get depense
     *
     * @return \AppBundle\Entity\Compta\Depense
     */
    public function getDepense()
    {
        return $this->depense;
    }

    /**
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return Rapprochement
     */
    public function setFacture(\AppBundle\Entity\CRM\DocumentPrix $facture = null)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get facture
     *
     * @return \AppBundle\Entity\CRM\DocumentPrix
     */
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Set accompte
     *
     * @param \AppBundle\Entity\Compta\Accompte $accompte
     * @return Rapprochement
     */
    public function setAccompte(\AppBundle\Entity\Compta\Accompte $accompte = null)
    {
        $this->accompte = $accompte;

        return $this;
    }

    /**
     * Get accompte
     *
     * @return \AppBundle\Entity\Compta\Accompte
     */
    public function getAccompte()
    {
        return $this->accompte;
    }

    /**
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return Rapprochement
     */
    public function setAvoir(\AppBundle\Entity\Compta\Avoir $avoir = null)
    {
        $this->avoir = $avoir;

        return $this;
    }

    /**
     * Get avoir
     *
     * @return \AppBundle\Entity\Compta\Avoir
     */
    public function getAvoir()
    {
        return $this->avoir;
    }

    /**
     * Set remiseCheque
     *
     * @param \AppBundle\Entity\Compta\RemiseCheque $remiseCheque
     * @return Rapprochement
     */
    public function setRemiseCheque(\AppBundle\Entity\Compta\RemiseCheque $remiseCheque = null)
    {
        $this->remiseCheque = $remiseCheque;

        return $this;
    }

    /**
     * Get remiseCheque
     *
     * @return \AppBundle\Entity\Compta\RemiseCheque
     */
    public function getRemiseCheque()
    {
        return $this->remiseCheque;
    }

    /**
     * Set affectationDiverse
     *
     * @param \AppBundle\Entity\Compta\AffectationDiverse $affectationDiverse
     * @return Rapprochement
     */
    public function setAffectationDiverse(\AppBundle\Entity\Compta\AffectationDiverse $affectationDiverse = null)
    {
        $this->affectationDiverse = $affectationDiverse;

        return $this;
    }

    /**
     * Get affectationDiverse
     *
     * @return \AppBundle\Entity\Compta\AffectationDiverse
     */
    public function getAffectationDiverse()
    {
        return $this->affectationDiverse;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Rapprochement
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
     * Set noteFrais
     *
     * @param \AppBundle\Entity\NDF\NoteFrais $noteFrais
     * @return Rapprochement
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
}
