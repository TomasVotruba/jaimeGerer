<?php

namespace AppBundle\Entity\Social;

use Doctrine\ORM\Mapping as ORM;

/**
 * Merci
 *
 * @ORM\Table(name="merci")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Social\MerciRepository")
 */
class Merci
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="fromText", type="string", length=255, nullable=true)
     */
    private $fromText;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $fromUser;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $to;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="string", length=255)
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Social\TableauMerci", inversedBy="mercis")
     * @ORM\JoinColumn(nullable=true)
     */
    private $tableauMerci;

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
     * Set type
     *
     * @param string $type
     * @return Merci
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
     * Set fromText
     *
     * @param string $fromText
     * @return Merci
     */
    public function setFromText($fromText)
    {
        $this->fromText = $fromText;

        return $this;
    }

    /**
     * Get fromText
     *
     * @return string 
     */
    public function getFromText()
    {
        return $this->fromText;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Merci
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Merci
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
     * Set fromUser
     *
     * @param \AppBundle\Entity\User $fromUser
     * @return Merci
     */
    public function setFromUser(\AppBundle\Entity\User $fromUser = null)
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * Get fromUser
     *
     * @return \AppBundle\Entity\User 
     */
    public function getFromUser()
    {
        return $this->fromUser;
    }

    /**
     * Set tableauMerci
     *
     * @param \AppBundle\Entity\Social\TableauMerci $tableauMerci
     * @return Merci
     */
    public function setTableauMerci(\AppBundle\Entity\Social\TableauMerci $tableauMerci = null)
    {
        $this->tableauMerci = $tableauMerci;

        return $this;
    }

    /**
     * Get tableauMerci
     *
     * @return \AppBundle\Entity\Social\TableauMerci 
     */
    public function getTableauMerci()
    {
        return $this->tableauMerci;
    }


    /**
     * Set to
     *
     * @param \AppBundle\Entity\User $to
     * @return Merci
     */
    public function setTo(\AppBundle\Entity\User $to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return \AppBundle\Entity\User 
     */
    public function getTo()
    {
        return $this->to;
    }
}
