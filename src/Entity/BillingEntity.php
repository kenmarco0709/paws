<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BillingRepository")
 * @ORM\Table(name="billing")
 * @ORM\HasLifecycleCallbacks()
 */

class BillingEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="amount_due", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountDue;
    
    /**
     * @ORM\Column(name="payment_amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $paymentAmount;

    /**
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="billings")
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id", nullable=true)
     */
    protected $admission;

    /**
     * @ORM\OneToMany(targetEntity="PaymentEntity", mappedBy="billing", cascade={"remove"})
     */
    protected $payments;



  

    
    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Billing Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return BillingEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Set totalPayment
     *
     *
     * @return int
     */
    public function totalPayment()
    {

        $totalPayment = 0;

        foreach($this->payments as  $payment){
            $totalPayment+= $payment->getAmount();
        }

        return $totalPayment;
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAmountDue(): ?string
    {
        return $this->amountDue;
    }

    public function setAmountDue(?string $amountDue): self
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    public function getPaymentAmount(): ?string
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount(?string $paymentAmount): self
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAdmission(): ?AdmissionEntity
    {
        return $this->admission;
    }

    public function setAdmission(?AdmissionEntity $admission): self
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * @return Collection<int, PaymentEntity>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(PaymentEntity $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setBilling($this);
        }

        return $this;
    }

    public function removePayment(PaymentEntity $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getBilling() === $this) {
                $payment->setBilling(null);
            }
        }

        return $this;
    }



    

   
    
}
