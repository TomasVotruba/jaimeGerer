<?php

namespace AppBundle\Entity\CRM;

use Doctrine\ORM\Mapping as ORM;

/**
 * RapportFilter
 *
 * @ORM\Table(name="rapport_filter")
 * @ORM\Entity
 */
class RapportFilter
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
     * @ORM\Column(name="champ", type="string", length=255)
     */
    private $champ;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255,  nullable=true)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="andor", type="string", length=255, nullable=true)
     */
    private $andor;

     /**
     * @var boolean
     *
     * @ORM\Column(name="end_group", type="boolean")
     */
    private $endGroup = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Rapport", inversedBy="filtres")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $rapport;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CRM\Prospection")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $prospection;

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
     * Set champ
     *
     * @param string $champ
     * @return RapportFilter
     */
    public function setChamp($champ)
    {
        $this->champ = $champ;

        return $this;
    }

    /**
     * Get champ
     *
     * @return string 
     */
    public function getChamp()
    {
        return $this->champ;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return RapportFilter
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set valeur
     *
     * @param string $valeur
     * @return RapportFilter
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
     * Set andor
     *
     * @param string $andor
     * @return RapportFilter
     */
    public function setAndor($andor)
    {
        $this->andor = $andor;

        return $this;
    }

    /**
     * Get andor
     *
     * @return string 
     */
    public function getAndor()
    {
        return $this->andor;
    }

    /**
     * Set rapport
     *
     * @param \AppBundle\Entity\CRM\Rapport $rapport
     * @return RapportFilter
     */
    public function setRapport(\AppBundle\Entity\CRM\Rapport $rapport)
    {
        $this->rapport = $rapport;

        return $this;
    }

    /**
     * Get rapport
     *
     * @return \AppBundle\Entity\CRM\Rapport 
     */
    public function getRapport()
    {
        return $this->rapport;
    }

    /**
     * Set endGroup
     *
     * @param boolean $endGroup
     * @return RapportFilter
     */
    public function setEndGroup($endGroup)
    {
        $this->endGroup = $endGroup;

        return $this;
    }

    /**
     * Get endGroup
     *
     * @return boolean
     */
    public function getEndGroup()
    {
        return $this->endGroup;
    }

    /**
     * Set prospection
     *3
     * @param \AppBundle\Entity\CRM\Prospection $prospection
     * @return RapportFilter
     */
    public function setProspection(\AppBundle\Entity\CRM\Prospection $prospection)
    {
        $this->prospection = $prospection;

        return $this;
    }

    /**
     * Get prospection
     *
     * @return \AppBundle\Entity\CRM\Prospection
     */
    public function getProspection()
    {
        return $this->prospection;
    }
}
