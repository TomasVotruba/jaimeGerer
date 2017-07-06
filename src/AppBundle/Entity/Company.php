<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * Company
 *
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CompanyRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Company
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
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     */
    private $logo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="tampon", type="string", length=255, nullable=true)
     */
    private $tampon;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="codePostal", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $codePostal;

    /**
     * @var string
     *
     * @ORM\Column(name="Pays", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $pays;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="telephone", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $telephone;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     */
    private $fax; 

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=7, nullable=true)
     */
    private $color;
  
    //for logo upload
    /**
     * @Assert\Image(
     * 	maxSize="2M",
     * 	minHeight="50",
     * 	maxHeight="300",
     * 	minWidth="50",
     * 	maxWidth="300" )
     */
    private $file;
    private $tempFilename;

    /**
     * @var float
     *
     * @ORM\Column(name="credits", type="float", nullable=false)
     */
    private $credits = 0;

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
     * @return Company
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
     * Set logo
     *
     * @param string $logo
     * @return Company
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return Company
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Company
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set codePostal
     *
     * @param string $codePostal
     * @return Company
     */
    public function setCodePostal($codePostal)
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * Get codePostal
     *
     * @return string 
     */
    public function getCodePostal()
    {
        return $this->codePostal;
    }

    /**
     * Set pays
     *
     * @param string $pays
     * @return Company
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return string 
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Company
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set telephone
     *
     * @param phone_number $telephone
     * @return Company
     */
    public function setTelephone($telephone)
    {
        $this->telephone = preg_replace('/[^0-9\/+\ ]/', '', $telephone);

        return $this;
    }

    /**
     * Get telephone
     *
     * @return phone_number 
     */
    public function getTelephone()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->telephone);
    }


    /**
     * Set fax
     *
     * @param phone_number $fax
     * @return Company
     */
    public function setFax($fax)
    {
        $this->fax = preg_replace('/[^0-9\/+\ ]/', '', $fax);

        return $this;
    }

    /**
     * Get fax
     *
     * @return phone_number 
     */
    public function getFax()
    {
        return preg_replace('/[^0-9\/+\ ]/', '', $this->fax);
    }
    
    public function getFile() {
    	return $this->file;
    }
    
    public function setFile(UploadedFile $file)
    {

    	$this->file = $file;
    	$this->tempFilename = null;
    	 
    	if (null !== $this->logo) {
    		$this->tempFilename = $this->logo;
    		$this->logo = null;
    	}
    
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
    	// Si jamais il n'y a pas de fichier (champ facultatif)
    	if (null === $this->file) {
    		return;
    	}
    	 
    	if ($this->file) {
    		$this->logo = $this->_simplifyString($this->nom).'.'.$this->file->guessExtension();
    	}
    
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
    	
    	if (null === $this->file) {
    		return;
    	}
    
    	if ($this->file) {
    		if (null !== $this->tempFilename) {
    			$oldFile = $this->getLogoUploadRootDir().'/'.$this->tempFilename;
    			if (file_exists($oldFile)) {
    				unlink($oldFile);
    			}
    		}
    
    		$this->file->move(
    				$this->getLogoUploadRootDir(),
    				$this->logo
    		);
    	}
    	 
    }
    
    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
    	if($this->logo!= null){
    		$this->tempFilename = $this->getLogoUploadRootDir().'/'.$this->id.'.'.$this->logo;
    	}
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
    	if (file_exists($this->tempFilename)) {
    		unlink($this->tempFilename);
    	}
    }
    
    public function getLogoUploadDir()
    {
    	return 'upload';
    }
    
    protected function getLogoUploadRootDir()
    {
    	return __DIR__.'/../../../web/'.$this->getLogoUploadDir();
    }
    
    /**
     * Simplify a string (lowercase, no space, no special character)
     * @param unknown $s
     * @return unknown
     */
    private function _simplifyString($s){
    		
    	$s = strtolower($s);
    		
    	$a_specialchars = array(
    			'à', 'â', 'é', 'è', 'ê', 'ë', 'ï', 'î', 'ô', 'ö', 'ù', 'û',
    			'-', "'", ' ',
    	);
    		
    	$a_normalchars = array(
    			'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'o', 'u', 'u',
    			'', '','',
    	);
    		
    	$s = str_replace($a_specialchars, $a_normalchars, $s);
    		
    	return $s;
    }
    
    public function getTempFilename() {
    	return $this->tempFilename;
    }
    public function setTempFilename($tempFilename) {
    	$this->tempFilename = $tempFilename;
    	return $this;
    }
    
    public function __toString(){
    	return $this->nom;
    }
     

    /**
     * Set color
     *
     * @param string $color
     * @return Company
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set tampon
     *
     * @param string $tampon
     * @return Company
     */
    public function setTampon($tampon)
    {
        $this->tampon = $tampon;

        return $this;
    }

    /**
     * Get tampon
     *
     * @return string 
     */
    public function getTampon()
    {
        return $this->tampon;
    }


    /**
     * Set credits
     *
     * @param float $credits
     * @return Company
     */
    public function setCredits($credits)
    {
        $this->credits = (float)$credits;

        return $this;
    }

    /**
     * Get credits
     *
     * @return float
     */
    public function getCredits()
    {
        return $this->credits;
    }

    /**
     * Ajoute des crédits au nombre de crédits de la compagnie
     * @param   float   $credits
     * @return  $this
     */
    public function addCredits($credits)
    {
        $this->credits += (float)$credits;
        return $this;
    }
    /**
     * Enlève des crédits au nombre de crédits de la compagnie
     * @param   float   $credits
     * @return  $this
     */
    public function removeCredits($credits)
    {
        $this->credits -= (float)$credits;
        return $this;
    }
}
