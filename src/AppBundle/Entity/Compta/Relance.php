<?php

namespace AppBundle\Entity\Compta;

use Doctrine\ORM\Mapping as ORM;

/**
 * Relance
 *
 * @ORM\Table(name="relance")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Compta\RelanceRepository")
 */
class Relance
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
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;


    /**
     * @var integer
     *
     * @ORM\Column(name="num", type="integer")
     */
    private $num;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="relances")
     * @var string
     *
     * @ORM\Column(name="objet", type="string", length=10)
     */
    private $objet;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\DocumentPrix", inversedBy="relances" )
     * @ORM\JoinColumn(nullable=true)
     */
    private $facture;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Contact", inversedBy="priseContacts")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $contact;

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
     * @return Relance
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
     * Set message
     *
     * @param string $message
     * @return Relance
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Relance
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

    /**
     * Set facture
     *
     * @param \AppBundle\Entity\CRM\DocumentPrix $facture
     * @return Relance
     */
    public function setFacture(\AppBundle\Entity\CRM\DocumentPrix $facture = null)
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Relance
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

    /**
     * Set contact
     *
     * @param \AppBundle\Entity\CRM\Contact $contact
     * @return Relance
     */
    public function setContact(\AppBundle\Entity\CRM\Contact $contact = null)
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
     * Set num
     *
     * @param integer $num
     * @return Relance
     */
    public function setNum($num)
    {
        $this->num = $num;

        return $this;
    }

    /**
     * Get num
     *
     * @return integer
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set objet
     *
     * @param string $objet
     * @return Relance
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }


    public function __toString(){
    	return 'Le '.$this->date->format('d/m/Y').' par '.strtolower($this->type);
    }

}
