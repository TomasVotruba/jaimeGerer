<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Settings
 *
 * @ORM\Table(name="settings")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SettingsRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Settings
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
     * @ORM\Column(name="parametre", type="string", length=255, nullable=false)
     */
    private $parametre;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="text", nullable=false)
     */
    private $valeur;
    
    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=255, nullable=false)
     */
    private $module;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compta\CompteComptable")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compteComptable;
    
  	 /**
     * @var string
     *
     * @ORM\Column(name="help_text", type="string", length=255, nullable=true)
     */
    private $helpText;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, nullable=true)
     */
    private $titre;
    
    /**
     * @var string
     *
     * @ORM\Column(name="noTVA", type="boolean")
     */
    private $noTVA;
    
    /**
     * @var string
     *
     * @ORM\Column(name="categorie", type="string", length=255, nullable=false)
     */
    private $categorie;
    
    //for image upload
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parametre
     *
     * @param string $parametre
     * @return Settings
     */
    public function setParametre($parametre)
    {
        $this->parametre = $parametre;

        return $this;
    }

    /**
     * Get parametre
     *
     * @return string 
     */
    public function getParametre()
    {
        return $this->parametre;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     * @return Settings
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }
    
    /**
     * Set module
     *
     * @param string $module
     * @return Settings
     */
    public function setModule($module)
    {
    	$this->module = $module;
    
    	return $this;
    }
    
    /**
     * Get module
     *
     * @return string
     */
    public function getModule()
    {
    	return $this->module;
    }
   
    public function __toString()
    {
    	return $this->getValeur();
    }
    

    /**
     * Set type
     *
     * @param string $type
     * @return Settings
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
    
    public function getFile() {
    	return $this->file;
    }
    
    public function setFile(UploadedFile $file)
    {
    	$this->file = $file;
    	$this->tempFilename = null;
    
    	if (null !== $this->valeur) {
    		$this->tempFilename = $this->valeur;
    		$this->valeur = null;
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
    		$this->valeur = $this->_simplifyString($this->parametre).'.'.$this->file->guessExtension();
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
    			$oldFile = $this->getUploadRootDir().'/'.$this->tempFilename;
    			if (file_exists($oldFile)) {
    				unlink($oldFile);
    			}
    		}
    
    		$this->file->move(
    				$this->getUploadRootDir(),
    				$this->valeur
    		);
    	}
    
    }
    
    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
    	if($this->valeur != null){
    		$this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->_simplifyString($this->parametre);
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
    
    public function getUploadDir()
    {
    	return 'upload';
    }
    
    protected function getUploadRootDir()
    {
    	return __DIR__.'/../../../web/'.$this->getUploadDir();
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

	public function __clone() {
   	 	if ($this->id) {
        	$this->setId(null);
    	}
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	

    /**
     * Set helpText
     *
     * @param string $helpText
     * @return Settings
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }

    /**
     * Get helpText
     *
     * @return string 
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return Settings
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set categorie
     *
     * @param string $categorie
     * @return Settings
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return string 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set noTVA
     *
     * @param boolean $noTVA
     * @return Settings
     */
    public function setNoTVA($noTVA)
    {
        $this->noTVA = $noTVA;

        return $this;
    }

    /**
     * Get noTVA
     *
     * @return boolean 
     */
    public function getNoTVA()
    {
        return $this->noTVA;
    }
}
