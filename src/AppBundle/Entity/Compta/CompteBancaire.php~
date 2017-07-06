<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompteBancaire
 *
 * @ORM\Table(name="compte_bancaire")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\CompteBancaireRepository")
 */
class CompteBancaire
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="num", type="string", length=255)
     */
    private $num;
    
    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=255)
     */
    private $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", length=255)
     */
    private $iban;

    /**
     * @var string
     *
     * @ORM\Column(name="domiciliation", type="string", length=255)
     */
    private $domiciliation;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteComptable;

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
     * Set nom
     *
     * @param string $nom
     * @return CompteBancaire
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set bic
     *
     * @param string $bic
     * @return CompteBancaire
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string 
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set iban
     *
     * @param string $iban
     * @return CompteBancaire
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string 
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set domiciliation
     *
     * @param string $domiciliation
     * @return CompteBancaire
     */
    public function setDomiciliation($domiciliation)
    {
        $this->domiciliation = $domiciliation;

        return $this;
    }

    /**
     * Get domiciliation
     *
     * @return string 
     */
    public function getDomiciliation()
    {
        return $this->domiciliation;
    }

  
	public function getCompany() {
		return $this->company;
	}
	public function setCompany($company) {
		$this->company = $company;
		return $this;
	}
	public function getCompteComptable() {
		return $this->compteComptable;
	}
	public function setCompteComptable($compteComptable) {
		$this->compteComptable = $compteComptable;
		return $this;
	}
	
	public function __toString(){
		return $this->getNom();
	}
	

    /**
     * Set num
     *
     * @param string $num
     * @return CompteBancaire
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
}
