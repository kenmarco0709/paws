<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceAdmissionInventoryItemRepository")
 * @ORM\Table(name="invoice_admission_inventory_item")
 * @ORM\HasLifecycleCallbacks()
 */

class InvoiceAdmissionInventoryItemEntity extends BaseEntity
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
     * @ORM\Column(name="discount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $discount;

    /**
     * @ORM\Column(name="buying_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $buyingPrice;
    
    /**
     * @ORM\Column(name="selling_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $sellingPrice;
    
    /**
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(name="remarks", type="string", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\ManyToOne(targetEntity="InventoryItemEntity", inversedBy="invoiceAdmissionInventoryItems")
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id")
     */
    protected $inventoryItem;
    
    /**
     * @ORM\ManyToOne(targetEntity="InvoiceEntity", inversedBy="invoiceAdmissionInventoryItems")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     */
    protected $invoice;

       /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="invoiceAdmissionInventoryItems")
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id")
     */
    protected $admission;

     /**
     * @ORM\ManyToOne(targetEntity="SupplierEntity", inversedBy="invoiceAdmissionInventoryItems")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getInventoryItem(): ?InventoryItemEntity
    {
        return $this->inventoryItem;
    }

    public function setInventoryItem(?InventoryItemEntity $inventoryItem): self
    {
        $this->inventoryItem = $inventoryItem;

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

    public function getAdmission(): ?AdmissionEntity
    {
        return $this->admission;
    }

    public function setAdmission(?AdmissionEntity $admission): self
    {
        $this->admission = $admission;

        return $this;
    }

    public function getBuyingPrice(): ?string
    {
        return $this->buyingPrice;
    }

    public function setBuyingPrice(?string $buyingPrice): self
    {
        $this->buyingPrice = $buyingPrice;

        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(?string $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    public function getSupplier(): ?SupplierEntity
    {
        return $this->supplier;
    }

    public function setSupplier(?SupplierEntity $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }



   
   
}
