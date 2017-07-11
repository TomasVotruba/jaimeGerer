<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cheque
 *
 * @ORM\Table(name="cheque")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\ChequeRepository")
 */
class Cheque
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
     * @ORM\Column(name="nomBanque", type="string", length=255, nullable=true)
     */
    private $nomBanque;

    /**
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=255, nullable=true)
     */
    private $num;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\RemiseCheque", inversedBy="cheques")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $remiseCheque;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Compta\ChequePiece", mappedBy="cheque", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $pieces;


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
     * Set banque
     *
     * @param string $banque
     * @return Cheque
     */
    public function setBanque($banque)
    {
        $this->banque = $banque;

        return $this;
    }

    /**
     * Get banque
     *
     * @return string
     */
    public function getBanque()
    {
        return $this->banque;
    }

    /**
     * Set num
     *
     * @param string $num
     * @return Cheque
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
     * Constructor
     */
    public function __construct()
    {
        $this->pieces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set nomBanque
     *
     * @param string $nomBanque
     * @return Cheque
     */
    public function setNomBanque($nomBanque)
    {
        $this->nomBanque = $nomBanque;

        return $this;
    }

    /**
     * Get nomBanque
     *
     * @return string
     */
    public function getNomBanque()
    {
        return $this->nomBanque;
    }

    /**
     * Set remiseCheque
     *
     * @param \AppBundle\Entity\Compta\RemiseCheque $remiseCheque
     * @return Cheque
     */
    public function setRemiseCheque(\AppBundle\Entity\Compta\RemiseCheque $remiseCheque)
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
     * Add pieces
     *
     * @param \AppBundle\Entity\Compta\ChequePiece $pieces
     * @return Cheque
     */
    public function addPiece(\AppBundle\Entity\Compta\ChequePiece $pieces)
    {
        $this->pieces[] = $pieces;
        $pieces->setCheque($this);

        return $this;
    }

    /**
     * Remove pieces
     *
     * @param \AppBundle\Entity\Compta\ChequePiece $pieces
     */
    public function removePiece(\AppBundle\Entity\Compta\ChequePiece $pieces)
    {
        $this->pieces->removeElement($pieces);
    }

    /**
     * Get pieces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPieces()
    {
        return $this->pieces;
    }

    public function getMontant(){
    	$montant = 0;
    	foreach($this->pieces as $piece){
    		if($piece->getFacture() != null){
    			$montant+=$piece->getFacture()->getTotalTTC();
    		} else if($piece->getAvoir() != null){
    			$montant+=$piece->getAvoir()->getTotalTTC();
    		}else if($piece->getOd() != null){
    			$montant+=$piece->getOd()->getTotalTTC();
    		}
    	}
    	return $montant;
    }

    public function getTotalHT(){
    	$montant = 0;
    	foreach($this->pieces as $piece){
    		if($piece->getFacture() != null){
    			$montant+=$piece->getFacture()->getTotalHT();
    		} else if($piece->getAvoir() != null){
    			$montant+=$piece->getAvoir()->getTotalHT();
    		}else if($piece->getOd() != null){
    			$montant+=$piece->getOd()->getTotalHT();
    		}
    	}
    	return $montant;
    }

    public function getTotalTVA(){
    	$montant = 0;
    	foreach($this->pieces as $piece){
    		if($piece->getFacture() != null){
    			$montant+=$piece->getFacture()->getTaxe();
    		} else if($piece->getAvoir() != null){
    			$montant+=$piece->getAvoir()->getTaxe();
    		}else if($piece->getOd() != null){
    			$montant+=$piece->getOd()->getTaxe();
    		}
    	}
    	return $montant;
    }

}
