<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * JournalAchat
 *
 * @ORM\Table(name="journal_achat")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\JournalAchatRepository")
 */
class JournalAchat
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
     * @ORM\Column(name="codeJournal", type="string", length=255)
     */
    private $codeJournal;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable", inversedBy="journalAchats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", inversedBy="journalAchats", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $depense;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Avoir", inversedBy="journalAchats", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, unique=false)
     */
    private $avoir;

    /**
     * @var string
     *
     * @ORM\Column(name="modePaiement", type="string", length=255, nullable=true)
     */
    private $modePaiement;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Settings")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $analytique;

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
     * Set codeJournal
     *
     * @param string $codeJournal
     * @return JournalAchat
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
     * Set debit
     *
     * @param float $debit
     * @return JournalAchat
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
     * @return JournalAchat
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
     * Set compteComptable
     *
     * @param \AppBundle\Entity\Compta\CompteComptable $compteComptable
     * @return JournalAchat
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
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return JournalAchat
     */
    public function setDepense(\AppBundle\Entity\Compta\Depense $depense)
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
     * Set modePaiement
     *
     * @param string $modePaiement
     * @return JournalAchat
     */
    public function setModePaiement($modePaiement)
    {
        $this->modePaiement = $modePaiement;

        return $this;
    }

    /**
     * Get modePaiement
     *
     * @return string
     */
    public function getModePaiement()
    {
        return $this->modePaiement;
    }

    /**
     * Set analytique
     *
     * @param \AppBundle\Entity\Settings $analytique
     * @return JournalAchat
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
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return JournalAchat
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

    public function getDate(){
      if($this->depense){
        return $this->depense->getDate();
      } else if($this->avoir) {
        return $this->avoir->getDateCreation();
      }
      return null;
    }

    public function getLibelle(){
      if($this->depense){
        return $this->depense->getNum().' : '.$this->depense->getLibelle();
      } else if($this->avoir) {
        return $this->avoir->getNum().' : '.$this->avoir->getObjet();
      }
      return null;
    }

    public function getLibelleWithoutNum(){
      if($this->depense){
        return $this->depense->getLibelle();
      } else if($this->avoir) {
        return $this->avoir->getObjet();
      }
      return null;
    }

    public function getPiece(){
      if($this->depense){
        return $this->depense->getNum();
      } else if($this->avoir) {
        return $this->avoir->getNum();
      }
      return null;
    }
}
