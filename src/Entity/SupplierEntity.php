<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupplierRepository")
 * @ORM\Table(name="supplier")
 * @ORM\HasLifecycleCallbacks()
 */

class SupplierEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="code", type="string")
     */
    protected $code;
    
    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="suppliers")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

      /**
     * @ORM\OneToMany(targetEntity="InventoryItemCompletedOrderEntity", mappedBy="supplier", cascade={"remove"})
     */
    protected $inventoryItemCompletedOrders;

    /**
     * @ORM\OneToMany(targetEntity="InventoryItemEntity", mappedBy="supplier", cascade={"remove"})
     */
    protected $inventoryItems;
    

    public function __construct($data = null)
    {
        $this->inventoryItemCompletedOrders = new ArrayCollection();
        $this->inventoryItems = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Supplier Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return SupplierEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDeleted(): ?string
    {
        return $this->isDeleted;
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
            $inventoryItemCompletedOrder->setSupplier($this);
        }

        return $this;
    }

    public function removeInventoryItemCompletedOrder(InventoryItemCompletedOrderEntity $inventoryItemCompletedOrder): self
    {
        if ($this->inventoryItemCompletedOrders->removeElement($inventoryItemCompletedOrder)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItemCompletedOrder->getSupplier() === $this) {
                $inventoryItemCompletedOrder->setSupplier(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InventoryItemEntity>
     */
    public function getInventoryItems(): Collection
    {
        return $this->inventoryItems;
    }

    public function addInventoryItem(InventoryItemEntity $inventoryItem): self
    {
        if (!$this->inventoryItems->contains($inventoryItem)) {
            $this->inventoryItems[] = $inventoryItem;
            $inventoryItem->setSupplier($this);
        }

        return $this;
    }

    public function removeInventoryItem(InventoryItemEntity $inventoryItem): self
    {
        if ($this->inventoryItems->removeElement($inventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItem->getSupplier() === $this) {
                $inventoryItem->setSupplier(null);
            }
        }

        return $this;
    }
}
