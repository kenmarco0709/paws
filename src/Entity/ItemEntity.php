<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @ORM\Table(name="item")
 * @ORM\HasLifecycleCallbacks()
 */

class ItemEntity extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="items")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="ItemCategoryEntity", inversedBy="items")
     * @ORM\JoinColumn(name="item_category_id", referencedColumnName="id", nullable=true)
     */
    protected $itemCategory;

    /**
     * @ORM\OneToMany(targetEntity="InventoryItemEntity", mappedBy="item", cascade={"remove"})
     */
    protected $inventoryItems;

    public function __construct($data = null)
    {
        $this->pets = new ArrayCollection();
        $this->inventoryItems = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Item Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ItemEntity
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
            $inventoryItem->setItem($this);
        }

        return $this;
    }

    public function removeInventoryItem(InventoryItemEntity $inventoryItem): self
    {
        if ($this->inventoryItems->removeElement($inventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItem->getItem() === $this) {
                $inventoryItem->setItem(null);
            }
        }

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

    public function getItemCategory(): ?ItemCategoryEntity
    {
        return $this->itemCategory;
    }

    public function setItemCategory(?ItemCategoryEntity $itemCategory): self
    {
        $this->itemCategory = $itemCategory;

        return $this;
    }



    

    



}
