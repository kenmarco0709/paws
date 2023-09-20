<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 * @ORM\Table(name="payment")
 * @ORM\HasLifecycleCallbacks()
 */

class PaymentEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amount;
    
    /**
     * @ORM\Column(name="amount_tendered", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountTendered;

    /**
     * @ORM\Column(name="amount_change", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountChange;
        
    /**
     * @ORM\Column(name="discount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $discount;

    /**
     * @ORM\Column(name="reference_no", type="string", nullable=true)
     */
    protected $referenceNo;

    /**
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    protected $payment_date;

    /**
     * @ORM\Column(name="is_deposit", type="boolean", nullable=true)
     */
    protected $isDeposit;

    /**
     * @ORM\ManyToOne(targetEntity="BillingEntity", inversedBy="payments")
     * @ORM\JoinColumn(name="billing_id", referencedColumnName="id", nullable=true)
     */
    protected $billing;

    /**
     * @ORM\ManyToOne(targetEntity="InvoiceEntity", inversedBy="payments")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", nullable=true)
     */
    protected $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentTypeEntity", inversedBy="payments")
     * @ORM\JoinColumn(name="payment_type_id", referencedColumnName="id", nullable=true)
     */
    protected $paymentType;

    
    public function __construct()
    {
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Payment Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PaymentEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = str_replace(",", "",  $amount);

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getReferenceNo(): ?string
    {
        return $this->referenceNo;
    }

    public function setReferenceNo(?string $referenceNo): self
    {
        $this->referenceNo = $referenceNo;

        return $this;
    }

    public function getBilling(): ?BillingEntity
    {
        return $this->billing;
    }

    public function setBilling(?BillingEntity $billing): self
    {
        $this->billing = $billing;

        return $this;
    }

    public function getPaymentType(): ?PaymentTypeEntity
    {
        return $this->paymentType;
    }

    public function setPaymentType(?PaymentTypeEntity $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getInvoice(): ?InvoiceEntity
    {
        return $this->invoice;
    }

    public function setInvoice(?InvoiceEntity $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->payment_date;
    }

    public function setPaymentDate(?\DateTimeInterface $payment_date): self
    {
        $this->payment_date = $payment_date;

        return $this;
    }

    public function isIsDeposit(): ?bool
    {
        return $this->isDeposit;
    }

    public function setIsDeposit(?bool $isDeposit): self
    {
        $this->isDeposit = $isDeposit;

        return $this;
    }

    public function getAmountTendered(): ?string
    {
        return $this->amountTendered;
    }

    public function setAmountTendered(?string $amountTendered): self
    {
        $this->amountTendered = $amountTendered;

        return $this;
    }

    public function getAmountChange(): ?string
    {
        return $this->amountChange;
    }

    public function setAmountChange(?string $amountChange): self
    {
        $this->amountChange = $amountChange;

        return $this;
    }



    

    
    
}
