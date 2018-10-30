<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperationDiverse
 *
 * @ORM\Table(name="operation_diverse")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\OperationDiverseRepository")
 */
class OperationDiverse
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var float
     *
     * @ORM\Column(name="debit", type="float", nullable=true)
     */
    private $debit;

    /**
     * @var float
     *
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit;

    /**
     * @var string
     *
     * @ORM\Column(name="codeJournal", type="string", length=5)
     */
    private $codeJournal;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable", inversedBy="operationsDiverses", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", cascade={ "persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Avoir", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $avoir;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NDF\NoteFrais", cascade={ "persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $noteFrais;

    /**
     * @var string
     *
     * @ORM\Column(name="lettrage", type="string", length=100, nullable=true)
     */
    private $lettrage;

    /**
     * @var string
     *
     * @ORM\Column(name="num_ecriture", type="string", length=50, nullable=true)
     */
    private $numEcriture;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=255, nullable=true)
     */
    private $commentaire;


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
     * Set date
     *
     * @param \DateTime $date
     * @return OperationDiverse
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
     * Get date
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return OperationDiverse
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set debit
     *
     * @param float $debit
     * @return OperationDiverse
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;

        return $this;
    }

    /**
     * Get debit
     *
     * @return float
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * Set credit
     *
     * @param float $credit
     * @return OperationDiverse
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit
     *
     * @return float
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set codeJournal
     *
     * @param string $codeJournal
     * @return OperationDiverse
     */
    public function setCodeJournal($codeJournal)
    {
        $this->codeJournal = $codeJournal;

        return $this;
    }

    /**
     * Get codeJournal
     *
     * @return string
     */
    public function getCodeJournal()
    {
        return $this->codeJournal;
    }

    /**
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return OperationDiverse
     */
    public function setCompteComptable(\AppBundle\Entity\Compta\CompteComptable $compteComptable)
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
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return OperationDiverse
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
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return OperationDiverse
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
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return OperationDiverse
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

    public function getPiece(){
      if($this->depense){
        return $this->depense->getNum();
      } else if($this->avoir) {
        return $this->avoir->getNum();
      } else if($this->facture){
        return $this->facture->getNum();
      } else {
        return $this->getLibelle();
      }
      return null;
    }

    public function getPieceId(){
      if($this->depense){
        return $this->depense->getId();
      } else if($this->avoir) {
        return $this->avoir->getId();
      } else if($this->facture){
        return $this->facture->getId();
      }
      return null;
    }

     public function getDatePiece(){
      if($this->depense){
        return $this->depense->getDate();
      } else if($this->avoir) {
        return $this->avoir->getDateCreation();
      } else if($this->facture){
        return $this->facture->getDate();
      } else {
        return $this->getDate();
      }
      return null;
    }

    /**
     * Set lettrage
     *
     * @param string $lettrage
     * @return OperationDiverse
     */
    public function setLettrage($lettrage)
    {
        $this->lettrage = $lettrage;

        return $this;
    }

    /**
     * Get lettrage
     *
     * @return string 
     */
    public function getLettrage()
    {
        return $this->lettrage;
    }

    /**
     * Set numEcriture
     *
     * @param string $numEcriture
     * @return OperationDiverse
     */
    public function setNumEcriture($numEcriture)
    {
        $this->numEcriture = $numEcriture;

        return $this;
    }

    /**
     * Get numEcriture
     *
     * @return string 
     */
    public function getNumEcriture()
    {
        return $this->numEcriture;
    }

    public function getMontant(){
        if($this->debit != null & $this->debit != 0){
            return $this->debit;
        }
        return $this->credit;
    }

    /**
     * Set noteFrais
     *
     * @param \AppBundle\Entity\NDF\NoteFrais $noteFrais
     * @return OperationDiverse
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

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return OperationDiverse
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }
}
