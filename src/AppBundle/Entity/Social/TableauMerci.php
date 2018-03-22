<?php

namespace AppBundle\Entity\Social;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableauMerci
 *
 * @ORM\Table(name="tableau_merci")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Social\TableauMerciRepository")
 */
class TableauMerci
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
     * @ORM\Column(name="dateDebut", type="date")
     */
    private $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFin", type="date")
     */
    private $dateFin;

    /**
     * @var integer
     *
     * @ORM\Column(name="objectifInterne", type="integer")
     */
    private $objectifInterne;

    /**
     * @var integer
     *
     * @ORM\Column(name="objectifExterne", type="integer")
     */
    private $objectifExterne;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Social\Merci", mappedBy="tableauMerci")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $mercis;


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
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return TableauMerci
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return TableauMerci
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set objectifInterne
     *
     * @param integer $objectifInterne
     * @return TableauMerci
     */
    public function setObjectifInterne($objectifInterne)
    {
        $this->objectifInterne = $objectifInterne;

        return $this;
    }

    /**
     * Get objectifInterne
     *
     * @return integer 
     */
    public function getObjectifInterne()
    {
        return $this->objectifInterne;
    }

    /**
     * Set objectifExterne
     *
     * @param integer $objectifExterne
     * @return TableauMerci
     */
    public function setObjectifExterne($objectifExterne)
    {
        $this->objectifExterne = $objectifExterne;

        return $this;
    }

    /**
     * Get objectifExterne
     *
     * @return integer 
     */
    public function getObjectifExterne()
    {
        return $this->objectifExterne;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mercis = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add mercis
     *
     * @param \AppBundle\Entity\Social\Merci $mercis
     * @return TableauMerci
     */
    public function addMerci(\AppBundle\Entity\Social\Merci $mercis)
    {
        $this->mercis[] = $mercis;

        return $this;
    }

    /**
     * Remove mercis
     *
     * @param \AppBundle\Entity\Social\Merci $mercis
     */
    public function removeMerci(\AppBundle\Entity\Social\Merci $mercis)
    {
        $this->mercis->removeElement($mercis);
    }

    /**
     * Get mercis
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMercis()
    {
        return $this->mercis;
    }

    public function getNumMerciExterne(){
        $num = 0;
        foreach($this->mercis as $merci){
            if(strtolower($merci->getType()) == "externe"){
                $num++;
            }
        }
        return $num;
    }

    public function getNumMerciInterne(){
        $num = 0;
        foreach($this->mercis as $merci){
            if(strtolower($merci->getType()) == "interne"){
                $num++;
            }
        }
        return $num;
    }

    public function getMerciInternes(){
       $arr_mercis = array();
        foreach($this->mercis as $merci){
            if(strtolower($merci->getType()) == "interne"){
                $arr_mercis[] = $merci;
            }
        }
        return $arr_mercis;
    }

    public function getMerciExternes(){
       $arr_mercis = array();
        foreach($this->mercis as $merci){
            if(strtolower($merci->getType()) == "externe"){
                $arr_mercis[] = $merci;
            }
        }
        return $arr_mercis;
    }
}
