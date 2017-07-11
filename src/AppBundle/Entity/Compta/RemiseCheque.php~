<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * RemiseCheque
 *
 * @ORM\Table(name="remise_cheque")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\RemiseChequeRepository")
 */
class RemiseCheque
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
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteBancaire")
     * @ORM\JoinColumn(nullable=false)
     */
    private $compteBancaire;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Cheque", mappedBy="remiseCheque", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $cheques;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\Rapprochement", mappedBy="remiseCheque")
     */
    private $rapprochements;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set num
     *
     * @param string $num
     * @return RemiseCheque
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
     * Set date
     *
     * @param \DateTime $date
     * @return RemiseCheque
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
    public function __construct()
    {
        $this->rapprochements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set compteBancaire
     *
     * @param \AppBundle\Entity\Compta\CompteBancaire $compteBancaire
     * @return RemiseCheque
     */
    public function setCompteBancaire(\AppBundle\Entity\Compta\CompteBancaire $compteBancaire)
    {
        $this->compteBancaire = $compteBancaire;

        return $this;
    }

    /**
     * Get compteBancaire
     *
     * @return \AppBundle\Entity\Compta\CompteBancaire
     */
    public function getCompteBancaire()
    {
        return $this->compteBancaire;
    }

    /**
     * Add rapprochement
     *
     * @param \AppBundle\Entity\Compta\Rapprochement $rapprochement
     * @return RemiseCheque
     */
    public function addRapprochement(\AppBundle\Entity\Compta\Rapprochement $rapprochement)
    {
    	$rapprochement->setRemiseCheque($this);
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

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        $total = 0;
    	foreach($this->cheques as $cheque){
    		$total+= $cheque->getMontant();
    	}
    	return $total;
    }

    public function getTotalTTC()
    {
    	return $this->getTotal();
    }

    public function getTotalRapproche(){

    	$total = 0;
    	foreach($this->rapprochements as $rapprochement){
    		$total+= $rapprochement->getMouvementBancaire()->getMontant();
    	}
    	return $total;
    }

    public function __toString(){
    	return $this->num.' : '.$this->getTotal().' €';
    }

    /**
     * Add cheque
     *
     * @param \AppBundle\Entity\Compta\Cheque $cheque
     * @return RemiseCheque
     */
    public function addCheque(\AppBundle\Entity\Compta\Cheque $cheque)
    {
        $cheque->setRemiseCheque($this);
        $this->cheques[] = $cheque;

        return $this;
    }

    /**
     * Remove cheque
     *
     * @param \AppBundle\Entity\Compta\Cheque $cheque
     */
    public function removeCheque(\AppBundle\Entity\Compta\Cheque $cheque)
    {
        $this->cheques->removeElement($cheque);
    }

    /**
     * Get cheques
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCheques()
    {
        return $this->cheques;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return RemiseCheque
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
     * @return RemiseCheque
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
     * @return RemiseCheque
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
     * @return RemiseCheque
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
}
