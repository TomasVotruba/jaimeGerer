<?php

namespace AppBundle\Entity\TimeTracker;

use Doctrine\ORM\Mapping as ORM;

/**
 * Temps
 *
 * @ORM\Table(name="temps")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TimeTracker\TempsRepository")
 */
class Temps
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
     * @var float
     *
     * @ORM\Column(name="duree", type="float")
     */
    private $duree;

    /**
    * @var \DateTime
    *
    * @ORM\Column(name="date", type="date", nullable=true)
    */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="activite", type="string", length=255, nullable=true)
     */
    private $activite;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Opportunite", inversedBy="temps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actionCommerciale;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


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
     * Set activite
     *
     * @param string $activite
     * @return Temps
     */
    public function setActivite($activite)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite
     *
     * @return string 
     */
    public function getActivite()
    {
        return $this->activite;
    }

    /**
     * Set actionCommerciale
     *
     * @param \AppBundle\Entity\CRM\Opportunite $actionCommerciale
     * @return Temps
     */
    public function setActionCommerciale(\AppBundle\Entity\CRM\Opportunite $actionCommerciale)
    {
        $this->actionCommerciale = $actionCommerciale;

        return $this;
    }

    /**
     * Get actionCommerciale
     *
     * @return \AppBundle\Entity\CRM\Opportunite 
     */
    public function getActionCommerciale()
    {
        return $this->actionCommerciale;
    }

    /**
     * Set duree
     *
     * @param float $duree
     * @return Temps
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;

        return $this;
    }

    /**
     * Get duree
     *
     * @return float 
     */
    public function getDuree()
    {
        return $this->duree;
    }

     public function getDureeAsString(){
        $duree = $this->duree;
        $hours = floor($duree);
        $minutesDec = $duree-$hours;
        $minutes = $minutesDec*60;
        
        return str_pad($hours, 2, 0, STR_PAD_LEFT).'h'.str_pad($minutes, 2, 0, STR_PAD_LEFT);
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Temps
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Temps
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
