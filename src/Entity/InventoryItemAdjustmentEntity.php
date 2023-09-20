<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InventoryItemAdjustmentRepository")
 * @ORM\Table(name="inventory_item_adjustment")
 * @ORM\HasLifecycleCallbacks()
 */

class InventoryItemAdjustmentEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="adjustment_type", type="string")
     */
    protected $adjustmentType;

     /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\Column(name="quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $quantity;

    /**
     * @ORM\Column(name="selling_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $sellingPrice;

    /**
     * @ORM\Column(name="buying_price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $buyingPrice;

    /**
     * @ORM\Column(name="low_quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $lowQuantity;

    /**
     * @ORM\ManyToOne(targetEntity="InventoryItemEntity", inversedBy="inventoryItemAdjusments")
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id", nullable=true)
     */
    protected $inventoryItem;



  

   

    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					InventoryItemAdjustment Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return InventoryItemAdjustmentEntity
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

    public function getAdjustmentType(): ?string
    {
        return $this->adjustmentType;
    }

    public function setAdjustmentType(string $adjustmentType): self
    {
        $this->adjustmentType = $adjustmentType;

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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(?string $quantity): self
    {
        $this->quantity = str_replace(',', '', $quantity);

        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(?string $sellingPrice): self
    {
        $this->sellingPrice = str_replace(',', '', $sellingPrice);

        return $this;
    }

    public function getBuyingPrice(): ?string
    {
        return $this->buyingPrice;
    }

    public function setBuyingPrice(?string $buyingPrice): self
    {
        $this->buyingPrice = str_replace(',', '', $buyingPrice);

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

    public function getLowQuantity(): ?string
    {
        return $this->lowQuantity;
    }

    public function setLowQuantity(?string $lowQuantity): self
    {
        $this->lowQuantity = $lowQuantity;

        return $this;
    }



    

    



   
}
