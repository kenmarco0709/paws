<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MedicalRecordPrescriptionInventoryItemRepository")
 * @ORM\Table(name="medical_record_prescription_inventory_item")
 * @ORM\HasLifecycleCallbacks()
 */

class MedicalRecordPrescriptionInventoryItemEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\Column(name="quantity", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $quantity;


    /**
     * @ORM\ManyToOne(targetEntity="MedicalRecordEntity", inversedBy="medicalRecordItems")
     * @ORM\JoinColumn(name="medical_record_id", referencedColumnName="id")
     */
    protected $medicalRecord;

    /**
     * @ORM\ManyToOne(targetEntity="InventoryItemEntity", inversedBy="medicalRecordItems")
     * @ORM\JoinColumn(name="inventory_item_id", referencedColumnName="id")
     */
    protected $inventoryItem;

    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Medicalrecordtype Defined Setters and Getters										  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return MedicalRecordPrescriptionInventoryItemEntity
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
        $this->quantity = $quantity;

        return $this;
    }

    public function getMedicalRecord(): ?MedicalRecordEntity
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecordEntity $medicalRecord): self
    {
        $this->medicalRecord = $medicalRecord;

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

   
    


   
   
}
