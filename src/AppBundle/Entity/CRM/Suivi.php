<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * Suivi
 *
 * @ORM\Table(name="suivi")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CRM\SuiviRepository")
 */
class Suivi
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCreation", type="datetime")
     */
    private $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="methodeContact", type="string", length=255)
     */
    private $methodeContact;

    /**
     * @var string
     *
     * @ORM\Column(name="infos", type="text")
     */
    private $infos;

    /**
     * @var boolean
     *
     * @ORM\Column(name="done", type="boolean")
     */
    private $done;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contact;
    
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
     * Set date
     *
     * @param \DateTime $date
     * @return Suivi
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     * @return Suivi
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
     * Set methodeContact
     *
     * @param string $methodeContact
     * @return Suivi
     */
    public function setMethodeContact($methodeContact)
    {
        $this->methodeContact = $methodeContact;

        return $this;
    }

    /**
     * Get methodeContact
     *
     * @return string 
     */
    public function getMethodeContact()
    {
        return $this->methodeContact;
    }

    /**
     * Set infos
     *
     * @param string $infos
     * @return Suivi
     */
    public function setInfos($infos)
    {
        $this->infos = $infos;

        return $this;
    }

    /**
     * Get infos
     *
     * @return string 
     */
    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * Set done
     *
     * @param boolean $done
     * @return Suivi
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get done
     *
     * @return boolean 
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set contact
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     * @return Suivi
     */
    public function setContact(\AppBundle\Entity\CRM\Contact $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \AppBundle\Entity\CRM\Contact 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Suivi
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
