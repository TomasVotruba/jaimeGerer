<?php

namespace AppBundle\Entity\Emailing;

use AppBundle\Entity\Company;
use Doctrine\ORM\Mapping as ORM;

/**
 * MauticApiKey
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MauticApiKey
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
     * @ORM\Column(name="secretKey", type="string", length=255)
     */
    private $secretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="publicKey", type="string", length=255)
     */
    private $publicKey;

    /**
     * @var
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Company")
     */
    protected $company;


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
     * Set secretKey
     *
     * @param string $secretKey
     * @return MauticApiKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Get secretKey
     *
     * @return string 
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set publicKey
     *
     * @param string $publicKey
     * @return MauticApiKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string 
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     * @return MauticApiKey
     */
    public function setCompany(\AppBundle\Entity\Company $company = null)
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
