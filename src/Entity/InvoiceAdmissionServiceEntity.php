<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceAdmissionServiceRepository")
 * @ORM\Table(name="invoice_admission_service")
 * @ORM\HasLifecycleCallbacks()
 */

class InvoiceAdmissionServiceEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $quantity;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(name="discount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $discount;

    /**
     * @ORM\Column(name="remarks", type="string", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceEntity", inversedBy="invoiceAdmissionServices")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    protected $service;
    
    /**
     * @ORM\ManyToOne(targetEntity="InvoiceEntity", inversedBy="invoiceAdmissionServices")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     */
    protected $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="invoiceAdmissionServices")
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id")
     */
    protected $admission;

    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Medicalrecordtype Defined Setters and Getters										  */
    /*--------------------------------------------------------------------------------------------------------*/


    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getService(): ?ServiceEntity
    {
        return $this->service;
    }

    public function setService(?ServiceEntity $service): self
    {
        $this->service = $service;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

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

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

 



    

   
   
}
