<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * JournalVente
 *
 * @ORM\Table("journal_vente")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\JournalVenteRepository")
 */
class JournalVente
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
     * @var string
     *
     * @ORM\Column(name="debit", type="float", nullable=true)
     */
    private $debit;

    /**
     * @var string
     *
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit;

    /**
     * @var string
     *
     * @ORM\Column(name="modePaiement", type="string", length=255, nullable=true)
     */
    private $modePaiement;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable", inversedBy="journalVentes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteComptable;

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
     * @return JournalVente
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
     * @param string $debit
     * @return JournalVente
     */
    public function setDebit($debit)
    {
        $this->debit = $debit;

        return $this;
    }

    /**
     * Get debit
     *
     * @return string
     */
    public function getDebit()
    {
        return $this->debit;
    }

    /**
     * Set credit
     *
     * @param string $credit
     * @return JournalVente
     */
    public function setCredit($credit)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit
     *
     * @return string
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set modePaiement
     *
     * @param string $modePaiement
     * @return JournalVente
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
	public function getFacture() {
		return $this->facture;
	}
	public function setFacture($facture) {
		$this->facture = $facture;
		return $this;
	}
	public function getCompteComptable() {
		return $this->compteComptable;
	}
	public function setCompteComptable($compteComptable) {
		$this->compteComptable = $compteComptable;
		return $this;
	}
	public function getAnalytique() {
		return $this->analytique;
	}
	public function setAnalytique($analytique) {
		$this->analytique = $analytique;
		return $this;
	}



    /**
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return JournalVente
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
      if($this->facture){
        return $this->facture->getDateCreation();
      } else if($this->avoir) {
        return $this->avoir->getDateCreation();
      }
      return null;
    }

    public function getLibelle(){
      if($this->facture){
        return $this->facture->getNum().' : '.$this->facture->getObjet();
      } else if($this->avoir) {
        return $this->avoir->getNum().' : '.$this->avoir->getObjet();
      }
      return null;
    }

    public function getPiece(){
      if($this->facture){
        return $this->facture->getNum();
      } else if($this->avoir) {
        return $this->avoir->getNum();
      }
      return null;
    }
}
