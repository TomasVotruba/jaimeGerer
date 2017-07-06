<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * ChequePiece
 *
 * @ORM\Table(name="cheque_piece")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\ChequePieceRepository")
 */
class ChequePiece
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Cheque", inversedBy="pieces")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $cheque;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\Avoir")
     * @ORM\JoinColumn(nullable=true)
     */
    private $avoir;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix")
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

    /**
     * @var float
     *
     * @ORM\Column(name="autre_montant", type="float", nullable=true)
     */
    private $autreMontant;

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
     * Set cheque
     *
     * @param \AppBundle\Entity\Compta\Cheque $cheque
     * @return ChequePiece
     */
    public function setCheque(\AppBundle\Entity\Compta\Cheque $cheque)
    {
        $this->cheque = $cheque;

        return $this;
    }

    /**
     * Get cheque
     *
     * @return \AppBundle\Entity\Compta\Cheque 
     */
    public function getCheque()
    {
        return $this->cheque;
    }

    /**
     * Set avoir
     *
     * @param \AppBundle\Entity\Compta\Avoir $avoir
     * @return ChequePiece
     */
    public function setAvoir(\AppBundle\Entity\Compta\Avoir $avoir)
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
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return ChequePiece
     */
    public function setFacture(\AppBundle\Entity\CRM\DocumentPrix $facture)
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
     * Set autreMontant
     *
     * @param float $autreMontant
     * @return ChequePiece
     */
    public function setAutreMontant($autreMontant)
    {
        $this->autreMontant = $autreMontant;

        return $this;
    }

    /**
     * Get autreMontant
     *
     * @return float 
     */
    public function getAutreMontant()
    {
        return $this->autreMontant;
    }
}
