<?php

namespace AppBundle\Entity\Emailing;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * CampagneContact
 *
 * @ORM\Table(name="campagne_contact", uniqueConstraints={@UniqueConstraint(name="campagne_contact", columns={"contact_id", "campagne_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Emailing\CampagneContactRepository")
 */
class CampagneContact
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Emailing\Campagne", inversedBy="campagneContacts", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $campagne;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact", inversedBy="campagneContacts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $contact;


    /**
     * @var boolean
     *
     * @ORM\Column(name="open", type="boolean")
     */
    private $open = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="openDate", type="datetime", nullable=true)
     */
    private $openDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="click", type="boolean")
     */
    private $click = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="clickDate", type="datetime", nullable=true)
     */
    private $clickDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="bounce", type="boolean")
     */
    private $bounce = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bounceDate", type="datetime", nullable=true)
     */
    private $bounceDate;

     /**
     * @var boolean
     *
     * @ORM\Column(name="delivered", type="boolean")
     */
    private $delivered = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deliveredDate", type="datetime", nullable=true)
     */
    private $deliveredDate;

     /**
     * @var boolean
     *
     * @ORM\Column(name="unsubscribed", type="boolean")
     */
    private $unsubscribed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="unsubscribedDate", type="datetime", nullable=true)
     */
    private $unsubscribedDate;


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
     * Set open
     *
     * @param boolean $open
     * @return CampagneContact
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return boolean 
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set openDate
     *
     * @param \DateTime $openDate
     * @return CampagneContact
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;

        return $this;
    }

    /**
     * Get openDate
     *
     * @return \DateTime 
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * Set click
     *
     * @param boolean $click
     * @return CampagneContact
     */
    public function setClick($click)
    {
        $this->click = $click;

        return $this;
    }

    /**
     * Get click
     *
     * @return boolean 
     */
    public function getClick()
    {
        return $this->click;
    }

    /**
     * Set clickDate
     *
     * @param \DateTime $clickDate
     * @return CampagneContact
     */
    public function setClickDate($clickDate)
    {
        $this->clickDate = $clickDate;

        return $this;
    }

    /**
     * Get clickDate
     *
     * @return \DateTime 
     */
    public function getClickDate()
    {
        return $this->clickDate;
    }

    /**
     * Set bounce
     *
     * @param boolean $bounce
     * @return CampagneContact
     */
    public function setBounce($bounce)
    {
        $this->bounce = $bounce;

        return $this;
    }

    /**
     * Get bounce
     *
     * @return boolean 
     */
    public function getBounce()
    {
        return $this->bounce;
    }

    /**
     * Set bounceDate
     *
     * @param \DateTime $bounceDate
     * @return CampagneContact
     */
    public function setBounceDate($bounceDate)
    {
        $this->bounceDate = $bounceDate;

        return $this;
    }

    /**
     * Get bounceDate
     *
     * @return \DateTime 
     */
    public function getBounceDate()
    {
        return $this->bounceDate;
    }

    /**
     * Set campagne
     *
     * @param \AppBundle\Entity\Emailing\Campagne $campagne
     * @return CampagneContact
     */
    public function setCampagne(\AppBundle\Entity\Emailing\Campagne $campagne)
    {
        $this->campagne = $campagne;

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
     * Set contact
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     * @return CampagneContact
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
     * Set delivered
     *
     * @param boolean $delivered
     * @return CampagneContact
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;

        return $this;
    }

    /**
     * Get delivered
     *
     * @return boolean 
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * Set deliveredDate
     *
     * @param \DateTime $deliveredDate
     * @return CampagneContact
     */
    public function setDeliveredDate($deliveredDate)
    {
        $this->deliveredDate = $deliveredDate;

        return $this;
    }

    /**
     * Get deliveredDate
     *
     * @return \DateTime 
     */
    public function getDeliveredDate()
    {
        return $this->deliveredDate;
    }

    /**
     * Set unsubscribed
     *
     * @param boolean $unsubscribed
     * @return CampagneContact
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;

        return $this;
    }

    /**
     * Get unsubscribed
     *
     * @return boolean 
     */
    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * Set unsubscribedDate
     *
     * @param \DateTime $unsubscribedDate
     * @return CampagneContact
     */
    public function setUnsubscribedDate($unsubscribedDate)
    {
        $this->unsubscribedDate = $unsubscribedDate;

        return $this;
    }

    /**
     * Get unsubscribedDate
     *
     * @return \DateTime 
     */
    public function getUnsubscribedDate()
    {
        return $this->unsubscribedDate;
    }
}
