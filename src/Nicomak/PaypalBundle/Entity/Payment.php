<?php
/**
 * Entité représentant un paiement
 * @package         Nicomak
 * @subpackage      PaypalBundle
 * @category        Entity
 * @author          Gilles Ortheau
 */
namespace Nicomak\PaypalBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    AppBundle\Entity\Company,
    Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Table(name="payment")
 * @ORM\Entity
 */
class Payment
{
    // Statuts des paiement
    const STATUS_NEW        = 'N';  // Nouveau paiement
    const STATUS_CONFIRM    = 'C';  // Paiement confirmé (mais pas encore validé par Paypal
    const STATUS_CANCELLED  = 'X';  // Paiement annulé
    const STATUS_VALIDATED  = 'V';  // Paiement validé par Paypal

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     */
    private $company;

    /** @ORM\Column(type="decimal", precision = 2) */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $ref;

    /**
     * @ORM\Column(type="string", length=1)
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    private $itemNumber;

    /**
     * @ORM\Column(type="integer", options={"default"=1})
     */
    private $quantity;

    /**
     * Constructeur
     * @param   float               $amount
     * @param   Company             $company
     */
    public function __construct($amount, Company $company)
    {
        $this->amount       = (float)$amount;
        $this->company      = $company;
        $this->status       = self::STATUS_NEW;
        $this->quantity     = 1;

        $this->setRef();
    }

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
     * Get company
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set ref
     *
     * @param   string|null  $ref
     * @return  Payment
     */
    public function setRef($ref = null)
    {
        if (!$ref) {
            $ref = sha1(($this->getCompany() ? $this->getCompany()->getNom() . '_' . $this->getCompany()->getId() :
                    uniqid('Nicomak')) . date('YmdHis'));
        }
        $this->ref = $ref;
        return $this;
    }

    /**
     * Get ref
     *
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set status
     *
     * @param   string  $status
     * @return  Payment
     */
    public function setStatus($status = self::STATUS_NEW)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set itemName
     *
     * @param   string  $itemName
     * @return  Payment
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
        return $this;
    }

    /**
     * Get itemName
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    /**
     * Set itemNumber
     *
     * @param   string  $itemNumber
     * @return  Payment
     */
    public function setItemNumber($itemNumber)
    {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    /**
     * Get itemNumber
     *
     * @return string
     */
    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    /**
     * Set quantity
     *
     * @param   string  $quantity
     * @return  Payment
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Valide le paiement
     * @return  Payment
     */
    public function validate()
    {
        $this->status = self::STATUS_VALIDATED;
        return $this;
    }

    /**
     * Annule le paiement
     * @return  Payment
     */
    public function cancel()
    {
        $this->status = self::STATUS_CANCELLED;
        return $this;
    }

    /**
     * Confirme le paiement
     * @return  Payment
     */
    public function confirm()
    {
        $this->status = self::STATUS_CONFIRM;
        return $this;
    }

    /**
     * Indique si le paiement a déjà été valide
     * @return  bool
     */
    public function isValid()
    {
        return !!($this->status == self::STATUS_VALIDATED);
    }
}

