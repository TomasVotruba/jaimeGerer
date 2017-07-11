<?php

namespace AppBundle\Entity\Emailing;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneContent
 *
 * @ORM\Table(name="zone_content")
 * @ORM\Entity
 */
class ZoneContent
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
     * @ORM\Column(name="zone_content", type="text")
     */
    private $zoneContent;

    /**
     * @var string
     *
     * @ORM\Column(name="zone", type="string", length=255)
     */
    private $zone;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Emailing\TemplateEmailing", inversedBy="zoneContents")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $template;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Emailing\Campagne", inversedBy="zoneContents")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $campagne;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_creation", type="date")
     */
    private $dateCreation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_edition", type="date", nullable=true)
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
     * Set zoneContent
     *
     * @param string $zoneContent
     * @return ZoneContent
     */
    public function setZoneContent($zoneContent)
    {
        $this->zoneContent = $zoneContent;

        return $this;
    }

    /**
     * Get zoneContent
     *
     * @return string 
     */
    public function getZoneContent()
    {
        return $this->zoneContent;
    }

    /**
     * Set zone
     *
     * @param string $zone
     * @return ZoneContent
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone
     *
     * @return string 
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set template
     *
     * @param \AppBundle\Entity\Emailing\TemplateEmailing $template
     * @return ZoneContent
     */
    public function setTemplate(\AppBundle\Entity\Emailing\TemplateEmailing $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \AppBundle\Entity\Emailing\TemplateEmailing
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set campagne
     *
     * @param \AppBundle\Entity\Emailing\Campagne $campagne
     * @return ZoneContent
     */
    public function setCampagne(\AppBundle\Entity\Emailing\Campagne $campagne = null)
    {
        $this->compagne = $compagne;

        return $this;
    }

    /**
     * Get campagne
     *
     * @return \AppBundle\Entity\Emailing\Campagne
     */
    public function getCampagne()
    {
        return $this->campagne;
    }

    /**
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return ZoneContent
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
     * @return ZoneContent
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
     * @return ZoneContent
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
     * @return ZoneContent
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
