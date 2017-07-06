<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Avoir
 *
 * @ORM\Table(name="avoir")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\AvoirRepository")
 */
class Avoir
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateValidite", type="date")
     * @Assert\NotBlank()
     */
    private $dateValidite;

    /**
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=15, nullable=false)
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="objet", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $objet;

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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="avoir")
     */
    private $rapprochements;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\JournalAchat", mappedBy="avoir", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $journalAchats;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="avoirs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Depense", inversedBy="avoirs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $depense;

    /**
     * @var string
     *
     * @ORM\Column(name="mode_paiement", type="string", length=255, nullable=true)
     */
    private $modePaiement;

    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\LigneAvoir", mappedBy="avoir", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     */
    private $lignes;

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
     * Set type
     *
     * @param string $type
     * @return Avoir
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Avoir
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
     * Constructor
     */
    public function __construct($type=null)
    {
        $this->rapprochements = new \Doctrine\Common\Collections\ArrayCollection();

        if($type!= null){
        	$this->type = $type;
        }

        if($this->dateValidite == null){
        	$this->dateValidite = new \DateTime(date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d')))));
        }

    }


    /**
     * Add rapprochement
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochement
     * @return Avoir
     */
    public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
    {
    	$rapprochement->setAvoir($this);
        $this->rapprochements[] = $rapprochement;

        return $this;
    }

    /**
     * Remove rapprochements
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochements
     */
    public function removeRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochements)
    {
        $this->rapprochements->removeElement($rapprochements);
    }

    /**
     * Get rapprochements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRapprochements()
    {
        return $this->rapprochements;
    }

    public function getTotalRapproche(){

    	$total = 0;
    	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	if($this->type == "CLIENT"){
    		$total = -$total;
    	}
    	return $total;
    }

	public function getTotaux(){

		$ht = 0;
		$tva= 0 ;

		if(count($this->lignes) != 0){
			foreach($this->lignes as $ligne){
				$ht+=$ligne->getMontant();
				$tva+=$ligne->getTaxe();
			}
		}

		$ttc = $ht+$tva;

		$arr_totaux = array();
		$arr_totaux['HT'] = $ht;
		$arr_totaux['TTC'] = $ttc;
		$arr_totaux['TVA'] = $tva;

		return $arr_totaux;
	}

  public function getTotalHT(){
    $ht = 0;
    foreach($this->lignes as $ligne){
      $ht+=$ligne->getMontant();
    }

    return $ht;
  }

	public function getTotalTTC(){
		$ht = 0;
		$tva = 0;
		foreach($this->lignes as $ligne){
			$ht+=$ligne->getMontant();
			$tva+=$ligne->getTaxe();
		}

		$ttc = $ht+$tva;
		return $ttc;
	}

    public function __toString(){
  		$s = '';
  		if($this->facture){
  			$s=$this->facture->getCompte()->getNom().' : ';
  		}else if($this->depense){
  			$s=$this->depense->getCompte()->getNom().' : ';
  		}

    	$s.=$this->getTotalTTC().' €';
    	return $s;
    }

    /**
     * Set num
     *
     * @param string $num
     * @return Avoir
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return string
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set objet
     *
     * @param string $objet
     * @return Avoir
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Avoir
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
     * @return Avoir
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
     * @return Avoir
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
     * @return Avoir
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
     * @return Avoir
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
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return Avoir
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
     * Set depense
     *
     * @param \AppBundle\Entity\Compta\Depense $depense
     * @return Avoir
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
     * Set dateValidite
     *
     * @param \DateTime $dateValidite
     * @return Avoir
     */
    public function setDateValidite($dateValidite)
    {
        $this->dateValidite = $dateValidite;

        return $this;
    }

    /**
     * Get dateValidite
     *
     * @return \DateTime
     */
    public function getDateValidite()
    {
        return $this->dateValidite;
    }

    /**
     * Set modePaiement
     *
     * @param string $modePaiement
     * @return Avoir
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
     * Add ligne
     *
     * @param \AppBundle\Entity\Compta\LigneAvoir $ligne
     * @return Avoir
     */
    public function addLigne(\AppBundle\Entity\Compta\LigneAvoir $ligne)
    {
    	$ligne->setAvoir($this);
        $this->lignes[] = $ligne;

        return $this;
    }

    /**
     * Remove lignes
     *
     * @param \AppBundle\Entity\Compta\LigneAvoir $lignes
     */
    public function removeLigne(\AppBundle\Entity\Compta\LigneAvoir $lignes)
    {
        $this->lignes->removeElement($lignes);
    }

    /**
     * Get lignes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLignes()
    {
        return $this->lignes;
    }

    /**
     * Add journalAchats
     *
     * @param \AppBundle\Entity\Compta\JournalAchat $journalAchats
     * @return Avoir
     */
    public function addJournalAchat(\AppBundle\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats[] = $journalAchats;

        return $this;
    }

    /**
     * Remove journalAchats
     *
     * @param \AppBundle\Entity\Compta\JournalAchat $journalAchats
     */
    public function removeJournalAchat(\AppBundle\Entity\Compta\JournalAchat $journalAchats)
    {
        $this->journalAchats->removeElement($journalAchats);
    }

    /**
     * Get journalAchats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getJournalAchats()
    {
        return $this->journalAchats;
    }
}
