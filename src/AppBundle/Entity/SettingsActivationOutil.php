<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SettingsActivationOutil
 *
 * @ORM\Table(name="settings_activation_outil")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SettingsActivationOutilRepository")
 */
class SettingsActivationOutil
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
     * @ORM\Column(name="outil", type="string", length=12)
     */
    private $outil;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

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
     * Set outil
     *
     * @param string $outil
     * @return SettingsActivationOutil
     */
    public function setOutil($outil)
    {
        $this->outil = $outil;

        return $this;
    }

    /**
     * Get outil
     *
     * @return string 
     */
    public function getOutil()
    {
        return $this->outil;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return SettingsActivationOutil
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
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     * @return SettingsActivationOutil
     */
    public function setCompany(\AppBundle\Entity\Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Company 
     */
    public function getCompany()
    {
        return $this->company;
    }
}
