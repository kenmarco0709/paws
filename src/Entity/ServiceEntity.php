<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 * @ORM\Table(name="service")
 * @ORM\HasLifecycleCallbacks()
 */

class ServiceEntity extends BaseEntity
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
     * @ORM\Column(name="price", type="decimal", precision=12, scale=2, nullable=true)
     */
    protected $price;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="services")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceTypeEntity", inversedBy="services")
     * @ORM\JoinColumn(name="service_type_id", referencedColumnName="id", nullable=true)
     */
    protected $serviceType;

      /**
     * @ORM\OneToMany(targetEntity="MedicalRecordServiceEntity", mappedBy="service", cascade={"remove"})
     */
    protected $medicalRecordServices;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceServiceEntity", mappedBy="service", cascade={"remove"})
     */
    protected $invoiceServices;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionServiceEntity", mappedBy="service", cascade={"remove"})
     */
    protected $invoiceAdmissionServices;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInventoryItemEntity", mappedBy="service", cascade={"remove"})
     */
    protected $serviceInventoryItems;


    public function __construct($data = null)
    {
        $this->pets = new ArrayCollection();
        $this->medicalRecordServices = new ArrayCollection();
        $this->invoiceServices = new ArrayCollection();
        $this->invoiceAdmissionServices = new ArrayCollection();
        $this->serviceInventoryItems = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Service Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ServiceEntity
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {

        $this->price =  str_replace(',','', $price);


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

    public function getServiceType(): ?ServiceTypeEntity
    {
        return $this->serviceType;
    }

    public function setServiceType(?ServiceTypeEntity $serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordServiceEntity>
     */
    public function getMedicalRecordServices(): Collection
    {
        return $this->medicalRecordServices;
    }

    public function addMedicalRecordService(MedicalRecordServiceEntity $medicalRecordService): self
    {
        if (!$this->medicalRecordServices->contains($medicalRecordService)) {
            $this->medicalRecordServices[] = $medicalRecordService;
            $medicalRecordService->setService($this);
        }

        return $this;
    }

    public function removeMedicalRecordService(MedicalRecordServiceEntity $medicalRecordService): self
    {
        if ($this->medicalRecordServices->removeElement($medicalRecordService)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordService->getService() === $this) {
                $medicalRecordService->setService(null);
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
            $invoiceService->setService($this);
        }

        return $this;
    }

    public function removeInvoiceService(InvoiceServiceEntity $invoiceService): self
    {
        if ($this->invoiceServices->removeElement($invoiceService)) {
            // set the owning side to null (unless already changed)
            if ($invoiceService->getService() === $this) {
                $invoiceService->setService(null);
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
            $invoiceAdmissionService->setService($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionService(InvoiceAdmissionServiceEntity $invoiceAdmissionService): self
    {
        if ($this->invoiceAdmissionServices->removeElement($invoiceAdmissionService)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionService->getService() === $this) {
                $invoiceAdmissionService->setService(null);
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
            $serviceInventoryItem->setService($this);
        }

        return $this;
    }

    public function removeServiceInventoryItem(ServiceInventoryItemEntity $serviceInventoryItem): self
    {
        if ($this->serviceInventoryItems->removeElement($serviceInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($serviceInventoryItem->getService() === $this) {
                $serviceInventoryItem->setService(null);
            }
        }

        return $this;
    }
}
