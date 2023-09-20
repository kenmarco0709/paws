<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventoryItemRepository")
 * @ORM\Table(name="inventory_item")
 * @ORM\HasLifecycleCallbacks()
 */

class InventoryItemEntity extends BaseEntity
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
     * @ORM\Column(name="beginning_quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $beginningQuantity;

    /**
     * @ORM\Column(name="low_quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $lowQuantity;

    /**
     * @ORM\Column(name="buying_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $buyingPrice;

    /**
     * @ORM\Column(name="selling_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $sellingPrice;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="inventoryItems")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="ItemEntity", inversedBy="inventoryItems")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", nullable=true)
     */
    protected $item;

    /**
     * @ORM\ManyToOne(targetEntity="SupplierEntity", inversedBy="inventoryItems")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id", nullable=true)
     */
    protected $supplier;

    /**
     * @ORM\OneToMany(targetEntity="InventoryItemAdjustmentEntity", mappedBy="itemInventory", cascade={"remove"})
     */
    protected $inventoryItemAdjustments;

     /**
     * @ORM\OneToMany(targetEntity="MedicalRecordItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $medicalRecordItems;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceInventoryItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $invoiceInventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionInventoryItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $invoiceAdmissionInventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceVoidInventoryItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $invoiceVoidInventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordPrescriptionInventoryItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $medicalRecordPrescriptionInventoryItems;

      /**
     * @ORM\OneToMany(targetEntity="InventoryItemCompletedOrderEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $inventoryItemCompletedOrders;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInventoryItemEntity", mappedBy="inventoryItem", cascade={"remove"})
     */
    protected $serviceInventoryItems;
    
    public function __construct($data = null)
    {
        $this->inventoryItemAdjustments = new ArrayCollection();
        $this->medicalRecordItems = new ArrayCollection();
        $this->invoiceInventoryItems = new ArrayCollection();
        $this->invoiceAdmissionInventoryItems = new ArrayCollection();
        $this->invoiceVoidInventoryItems = new ArrayCollection();
        $this->medicalRecordPrescriptionInventoryItems = new ArrayCollection();
        $this->inventoryItemCompletedOrders = new ArrayCollection();
        $this->serviceInventoryItems = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					InventoryItem Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return InventoryItemEntity
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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = str_replace(',','', $quantity);

        return $this;
    }

    public function getBuyingPrice(): ?string
    {
        return $this->buyingPrice;
    }

    public function setBuyingPrice(?string $buyingPrice): self
    {
        $this->buyingPrice =  str_replace(',','', $buyingPrice);

        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(?string $sellingPrice): self
    {
        $this->sellingPrice =  str_replace(',','', $sellingPrice);

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

    public function getItem(): ?ItemEntity
    {
        return $this->item;
    }

    public function setItem(?ItemEntity $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return Collection<int, InventoryItemAdjustmentEntity>
     */
    public function getInventoryItemAdjustments(): Collection
    {
        return $this->inventoryItemAdjustments;
    }

    public function addInventoryItemAdjustment(InventoryItemAdjustmentEntity $inventoryItemAdjustment): self
    {
        if (!$this->inventoryItemAdjustments->contains($inventoryItemAdjustment)) {
            $this->inventoryItemAdjustments[] = $inventoryItemAdjustment;
            $inventoryItemAdjustment->setItemInventory($this);
        }

        return $this;
    }

    public function removeInventoryItemAdjustment(InventoryItemAdjustmentEntity $inventoryItemAdjustment): self
    {
        if ($this->inventoryItemAdjustments->removeElement($inventoryItemAdjustment)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItemAdjustment->getItemInventory() === $this) {
                $inventoryItemAdjustment->setItemInventory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordItemEntity>
     */
    public function getMedicalRecordItems(): Collection
    {
        return $this->medicalRecordItems;
    }

    public function addMedicalRecordItem(MedicalRecordItemEntity $medicalRecordItem): self
    {
        if (!$this->medicalRecordItems->contains($medicalRecordItem)) {
            $this->medicalRecordItems[] = $medicalRecordItem;
            $medicalRecordItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeMedicalRecordItem(MedicalRecordItemEntity $medicalRecordItem): self
    {
        if ($this->medicalRecordItems->removeElement($medicalRecordItem)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordItem->getInventoryItem() === $this) {
                $medicalRecordItem->setInventoryItem(null);
            }
        }

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
            $invoiceInventoryItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeInvoiceInventoryItem(InvoiceInventoryItemEntity $invoiceInventoryItem): self
    {
        if ($this->invoiceInventoryItems->removeElement($invoiceInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceInventoryItem->getInventoryItem() === $this) {
                $invoiceInventoryItem->setInventoryItem(null);
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
            $invoiceAdmissionInventoryItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionInventoryItem(InvoiceAdmissionInventoryItemEntity $invoiceAdmissionInventoryItem): self
    {
        if ($this->invoiceAdmissionInventoryItems->removeElement($invoiceAdmissionInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionInventoryItem->getInventoryItem() === $this) {
                $invoiceAdmissionInventoryItem->setInventoryItem(null);
            }
        }

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
            $invoiceVoidInventoryItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeInvoiceVoidInventoryItem(InvoiceVoidInventoryItemEntity $invoiceVoidInventoryItem): self
    {
        if ($this->invoiceVoidInventoryItems->removeElement($invoiceVoidInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceVoidInventoryItem->getInventoryItem() === $this) {
                $invoiceVoidInventoryItem->setInventoryItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordPrescriptionInventoryItemEntity>
     */
    public function getMedicalRecordPrescriptionInventoryItems(): Collection
    {
        return $this->medicalRecordPrescriptionInventoryItems;
    }

    public function addMedicalRecordPrescriptionInventoryItem(MedicalRecordPrescriptionInventoryItemEntity $medicalRecordPrescriptionInventoryItem): self
    {
        if (!$this->medicalRecordPrescriptionInventoryItems->contains($medicalRecordPrescriptionInventoryItem)) {
            $this->medicalRecordPrescriptionInventoryItems[] = $medicalRecordPrescriptionInventoryItem;
            $medicalRecordPrescriptionInventoryItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeMedicalRecordPrescriptionInventoryItem(MedicalRecordPrescriptionInventoryItemEntity $medicalRecordPrescriptionInventoryItem): self
    {
        if ($this->medicalRecordPrescriptionInventoryItems->removeElement($medicalRecordPrescriptionInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordPrescriptionInventoryItem->getInventoryItem() === $this) {
                $medicalRecordPrescriptionInventoryItem->setInventoryItem(null);
            }
        }

        return $this;
    }

    public function getBeginningQuantity(): ?string
    {
        return $this->beginningQuantity;
    }

    public function setBeginningQuantity(?string $beginningQuantity): self
    {
        $this->beginningQuantity = $beginningQuantity;

        return $this;
    }

    public function getLowQuantity(): ?string
    {
        return $this->lowQuantity;
    }

    public function setLowQuantity(?string $lowQuantity): self
    {
        $this->lowQuantity = $lowQuantity;

        return $this;
    }

    /**
     * @return Collection<int, InventoryItemCompletedOrderEntity>
     */
    public function getInventoryItemCompletedOrders(): Collection
    {
        return $this->inventoryItemCompletedOrders;
    }

    public function addInventoryItemCompletedOrder(InventoryItemCompletedOrderEntity $inventoryItemCompletedOrder): self
    {
        if (!$this->inventoryItemCompletedOrders->contains($inventoryItemCompletedOrder)) {
            $this->inventoryItemCompletedOrders[] = $inventoryItemCompletedOrder;
            $inventoryItemCompletedOrder->setInventoryItem($this);
        }

        return $this;
    }

    public function removeInventoryItemCompletedOrder(InventoryItemCompletedOrderEntity $inventoryItemCompletedOrder): self
    {
        if ($this->inventoryItemCompletedOrders->removeElement($inventoryItemCompletedOrder)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItemCompletedOrder->getInventoryItem() === $this) {
                $inventoryItemCompletedOrder->setInventoryItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ServiceInventoryItemEntity>
     */
    public function getServiceInventoryItems(): Collection
    {
        return $this->serviceInventoryItems;
    }

    public function addServiceInventoryItem(ServiceInventoryItemEntity $serviceInventoryItem): self
    {
        if (!$this->serviceInventoryItems->contains($serviceInventoryItem)) {
            $this->serviceInventoryItems[] = $serviceInventoryItem;
            $serviceInventoryItem->setInventoryItem($this);
        }

        return $this;
    }

    public function removeServiceInventoryItem(ServiceInventoryItemEntity $serviceInventoryItem): self
    {
        if ($this->serviceInventoryItems->removeElement($serviceInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($serviceInventoryItem->getInventoryItem() === $this) {
                $serviceInventoryItem->setInventoryItem(null);
            }
        }

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



    


   
}
