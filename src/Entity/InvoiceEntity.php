<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @ORM\Table(name="invoice")
 * @ORM\HasLifecycleCallbacks()
 */

class InvoiceEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(name="discount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $discount;

    /**
     * @ORM\Column(name="amount_due", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amountDue;

    /**
     * @ORM\Column(name="invoice_date", type="datetime", nullable=true)
     */
    protected $invoiceDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="invoices")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="invoices")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="invoices")
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id", nullable=true)
     */
    protected $admission;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceInventoryItemEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $invoiceInventoryItems;

       /**
     * @ORM\OneToMany(targetEntity="InvoiceVoidInventoryItemEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $invoiceVoidInventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceServiceEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $invoiceServices;

        /**
     * @ORM\OneToMany(targetEntity="PaymentEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $payments;

      /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionInventoryItemEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $invoiceAdmissionInventoryItems;

          /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionServiceEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $invoiceAdmissionServices;
    
    /**
     * @ORM\OneToMany(targetEntity="ReimbursedPaymentEntity", mappedBy="invoice", cascade={"remove"})
     */
    protected $reimbursedPayments;




    public function __construct()
    {
        $this->invoiceInventoryItems = new ArrayCollection();
        $this->invoiceServices = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->invoiceAdmissionInventoryItems = new ArrayCollection();
        $this->invoiceAdmissionServices = new ArrayCollection();
        $this->invoiceVoidInventoryItems = new ArrayCollection();
        $this->reimbursedPayments = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Invoice Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return InvoiceEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get inventoryItemIds
     *
     *
     * @return array
     */
    public function inventoryItemIds()
    {

        $ids = [];

        foreach($this->invoiceInventoryItems as $k =>  $invoiceInventoryItem){

            $ids[$k] = $invoiceInventoryItem->getInventoryItem()->getId();
        }


        return $ids;
    }

       /**
     * Get serviceIds
     *
     *
     * @return array
     */
    public function serviceIds()
    {

        $ids = [];

        foreach($this->invoiceServices as $k =>  $invoiceService){

            $ids[$k] = $invoiceService->getService()->getId();
        }


        return $ids;
    }

     /**
     * Get totalPayment
     *
     *
     * @return int
     */
    public function totalPayment()
    {

        $amt = 0;

        foreach($this->payments as $payment){

            $amt += $payment->getAmount();
        }



        return $amt;
    }

    /**
     * Get grandTotal
     *
     *
     * @return int
     */
    public function grandTotal()
    {

        $amt = 0;

        foreach($this->payments as $payment){

            $amt += $payment->getAmount();
        }

        foreach($this->reimbursedPayments as $payment){

            $amt -= $payment->getAmount();
        }


        return $amt;
    }

    
      /**
     * Get totalReimbursePayment
     *
     *
     * @return int
     */
    public function totalReimbursePayment()
    {

        $amt = 0;


        foreach($this->reimbursedPayments as $payment){

            $amt += $payment->getAmount();
        }


        return $amt;
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
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

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(?\DateTimeInterface $invoiceDate): self
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    public function setClient(?ClientEntity $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getBranch(): ?BranchEntity
    {
        return $this->branch;
    }

    public function setBranch(?BranchEntity $branch): self
    {
        $this->branch = $branch;

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

    public function getAmountDue(): ?string
    {
        return $this->amountDue;
    }

    public function setAmountDue(?string $amountDue): self
    {
        $this->amountDue = $amountDue;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceInventoryItemEntity>
     */
    public function getInvoiceInventoryItems(): Collection
    {
        return $this->invoiceInventoryItems;
    }

    public function addInvoiceInventoryItem(InvoiceInventoryItemEntity $invoiceInventoryItem): self
    {
        if (!$this->invoiceInventoryItems->contains($invoiceInventoryItem)) {
            $this->invoiceInventoryItems[] = $invoiceInventoryItem;
            $invoiceInventoryItem->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceInventoryItem(InvoiceInventoryItemEntity $invoiceInventoryItem): self
    {
        if ($this->invoiceInventoryItems->removeElement($invoiceInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceInventoryItem->getInvoice() === $this) {
                $invoiceInventoryItem->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceServiceEntity>
     */
    public function getInvoiceServices(): Collection
    {
        return $this->invoiceServices;
    }

    public function addInvoiceService(InvoiceServiceEntity $invoiceService): self
    {
        if (!$this->invoiceServices->contains($invoiceService)) {
            $this->invoiceServices[] = $invoiceService;
            $invoiceService->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceService(InvoiceServiceEntity $invoiceService): self
    {
        if ($this->invoiceServices->removeElement($invoiceService)) {
            // set the owning side to null (unless already changed)
            if ($invoiceService->getInvoice() === $this) {
                $invoiceService->setInvoice(null);
            }
        }

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
            $payment->setInvoice($this);
        }

        return $this;
    }

    public function removePayment(PaymentEntity $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getInvoice() === $this) {
                $payment->setInvoice(null);
            }
        }

        return $this;
    }

    

    /**
     * @return Collection<int, InvoiceAdmissionInventoryItemEntity>
     */
    public function getInvoiceAdmissionInventoryItems(): Collection
    {
        return $this->invoiceAdmissionInventoryItems;
    }

    public function addInvoiceAdmissionInventoryItem(InvoiceAdmissionInventoryItemEntity $invoiceAdmissionInventoryItem): self
    {
        if (!$this->invoiceAdmissionInventoryItems->contains($invoiceAdmissionInventoryItem)) {
            $this->invoiceAdmissionInventoryItems[] = $invoiceAdmissionInventoryItem;
            $invoiceAdmissionInventoryItem->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionInventoryItem(InvoiceAdmissionInventoryItemEntity $invoiceAdmissionInventoryItem): self
    {
        if ($this->invoiceAdmissionInventoryItems->removeElement($invoiceAdmissionInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionInventoryItem->getInvoice() === $this) {
                $invoiceAdmissionInventoryItem->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceAdmissionServiceEntity>
     */
    public function getInvoiceAdmissionServices(): Collection
    {
        return $this->invoiceAdmissionServices;
    }

    public function addInvoiceAdmissionService(InvoiceAdmissionServiceEntity $invoiceAdmissionService): self
    {
        if (!$this->invoiceAdmissionServices->contains($invoiceAdmissionService)) {
            $this->invoiceAdmissionServices[] = $invoiceAdmissionService;
            $invoiceAdmissionService->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionService(InvoiceAdmissionServiceEntity $invoiceAdmissionService): self
    {
        if ($this->invoiceAdmissionServices->removeElement($invoiceAdmissionService)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionService->getInvoice() === $this) {
                $invoiceAdmissionService->setInvoice(null);
            }
        }

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
     * @return Collection<int, InvoiceVoidInventoryItemEntity>
     */
    public function getInvoiceVoidInventoryItems(): Collection
    {
        return $this->invoiceVoidInventoryItems;
    }

    public function addInvoiceVoidInventoryItem(InvoiceVoidInventoryItemEntity $invoiceVoidInventoryItem): self
    {
        if (!$this->invoiceVoidInventoryItems->contains($invoiceVoidInventoryItem)) {
            $this->invoiceVoidInventoryItems[] = $invoiceVoidInventoryItem;
            $invoiceVoidInventoryItem->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceVoidInventoryItem(InvoiceVoidInventoryItemEntity $invoiceVoidInventoryItem): self
    {
        if ($this->invoiceVoidInventoryItems->removeElement($invoiceVoidInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceVoidInventoryItem->getInvoice() === $this) {
                $invoiceVoidInventoryItem->setInvoice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReimbursedPaymentEntity>
     */
    public function getReimbursedPayments(): Collection
    {
        return $this->reimbursedPayments;
    }

    public function addReimbursedPayment(ReimbursedPaymentEntity $reimbursedPayment): self
    {
        if (!$this->reimbursedPayments->contains($reimbursedPayment)) {
            $this->reimbursedPayments[] = $reimbursedPayment;
            $reimbursedPayment->setInvoice($this);
        }

        return $this;
    }

    public function removeReimbursedPayment(ReimbursedPaymentEntity $reimbursedPayment): self
    {
        if ($this->reimbursedPayments->removeElement($reimbursedPayment)) {
            // set the owning side to null (unless already changed)
            if ($reimbursedPayment->getInvoice() === $this) {
                $reimbursedPayment->setInvoice(null);
            }
        }

        return $this;
    }



    

    
}
