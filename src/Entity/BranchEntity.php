<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BranchRepository")
 * @ORM\Table(name="branch")
 * @ORM\HasLifecycleCallbacks()
 */

class BranchEntity extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="CompanyEntity", inversedBy="branches")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;

    /**
     * @ORM\OneToMany(targetEntity="UserEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $users;

     /**
     * @ORM\OneToMany(targetEntity="ClientEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $clients;

    /**
     * @ORM\OneToMany(targetEntity="AdmissionEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $admissions;

      /**
     * @ORM\OneToMany(targetEntity="ServiceEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="SpeciesEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $specieses;

    /**
     * @ORM\OneToMany(targetEntity="InventoryItemEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $inventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="PaymentTypeEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $paymentTypes;

    /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $schedules;

       /**
     * @ORM\OneToMany(targetEntity="InvoiceEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $invoices;

    /**
     * @ORM\OneToMany(targetEntity="FacilityEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $facilities;

     /**
     * @ORM\OneToMany(targetEntity="VetScheduleEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $vetSchedules;

     /**
     * @ORM\OneToMany(targetEntity="SupplierEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $suppliers;

     /**
     * @ORM\OneToMany(targetEntity="ItemCategoryEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $itemCategories;

     /**
     * @ORM\OneToMany(targetEntity="ItemEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $items;

     /**
     * @ORM\OneToMany(targetEntity="PetEntity", mappedBy="branch", cascade={"remove"})
     */
    protected $pets;
    
    /**
     * @ORM\OneToMany(targetEntity="ShelterAdmissionEntity", mappedBy="branch", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $shelterAdmissions;


    public function __construct($data = null)
    {
        $this->users = new ArrayCollection();
        $this->clients = new ArrayCollection();
        $this->admissions = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->inventoryItems = new ArrayCollection();
        $this->paymentTypes = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->vetSchedules = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->itemCategories = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->specieses = new ArrayCollection();
        $this->facilities = new ArrayCollection();
        $this->shelterAdmissions = new ArrayCollection();
        $this->pets = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Branch Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return BranchEntity
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

    public function getCompany(): ?CompanyEntity
    {
        return $this->company;
    }

    public function setCompany(?CompanyEntity $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, UserEntity>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserEntity $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setBranch($this);
        }

        return $this;
    }

    public function removeUser(UserEntity $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getBranch() === $this) {
                $user->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientEntity>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(ClientEntity $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setBranch($this);
        }

        return $this;
    }

    public function removeClient(ClientEntity $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getBranch() === $this) {
                $client->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdmissionEntity>
     */
    public function getAdmissions(): Collection
    {
        return $this->admissions;
    }

    public function addAdmission(AdmissionEntity $admission): self
    {
        if (!$this->admissions->contains($admission)) {
            $this->admissions[] = $admission;
            $admission->setBranch($this);
        }

        return $this;
    }

    public function removeAdmission(AdmissionEntity $admission): self
    {
        if ($this->admissions->removeElement($admission)) {
            // set the owning side to null (unless already changed)
            if ($admission->getBranch() === $this) {
                $admission->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ServiceEntity>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(ServiceEntity $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setBranch($this);
        }

        return $this;
    }

    public function removeService(ServiceEntity $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getBranch() === $this) {
                $service->setBranch(null);
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
            $inventoryItem->setBranch($this);
        }

        return $this;
    }

    public function removeInventoryItem(InventoryItemEntity $inventoryItem): self
    {
        if ($this->inventoryItems->removeElement($inventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($inventoryItem->getBranch() === $this) {
                $inventoryItem->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PaymentTypeEntity>
     */
    public function getPaymentTypes(): Collection
    {
        return $this->paymentTypes;
    }

    public function addPaymentType(PaymentTypeEntity $paymentType): self
    {
        if (!$this->paymentTypes->contains($paymentType)) {
            $this->paymentTypes[] = $paymentType;
            $paymentType->setBranch($this);
        }

        return $this;
    }

    public function removePaymentType(PaymentTypeEntity $paymentType): self
    {
        if ($this->paymentTypes->removeElement($paymentType)) {
            // set the owning side to null (unless already changed)
            if ($paymentType->getBranch() === $this) {
                $paymentType->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScheduleEntity>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(ScheduleEntity $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setBranch($this);
        }

        return $this;
    }

    public function removeSchedule(ScheduleEntity $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getBranch() === $this) {
                $schedule->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, InvoiceEntity>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(InvoiceEntity $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setBranch($this);
        }

        return $this;
    }

    public function removeInvoice(InvoiceEntity $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getBranch() === $this) {
                $invoice->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VetScheduleEntity>
     */
    public function getVetSchedules(): Collection
    {
        return $this->vetSchedules;
    }

    public function addVetSchedule(VetScheduleEntity $vetSchedule): self
    {
        if (!$this->vetSchedules->contains($vetSchedule)) {
            $this->vetSchedules[] = $vetSchedule;
            $vetSchedule->setBranch($this);
        }

        return $this;
    }

    public function removeVetSchedule(VetScheduleEntity $vetSchedule): self
    {
        if ($this->vetSchedules->removeElement($vetSchedule)) {
            // set the owning side to null (unless already changed)
            if ($vetSchedule->getBranch() === $this) {
                $vetSchedule->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SupplierEntity>
     */
    public function getSuppliers(): Collection
    {
        return $this->suppliers;
    }

    public function addSupplier(SupplierEntity $supplier): self
    {
        if (!$this->suppliers->contains($supplier)) {
            $this->suppliers[] = $supplier;
            $supplier->setBranch($this);
        }

        return $this;
    }

    public function removeSupplier(SupplierEntity $supplier): self
    {
        if ($this->suppliers->removeElement($supplier)) {
            // set the owning side to null (unless already changed)
            if ($supplier->getBranch() === $this) {
                $supplier->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ItemCategoryEntity>
     */
    public function getItemCategories(): Collection
    {
        return $this->itemCategories;
    }

    public function addItemCategory(ItemCategoryEntity $itemCategory): self
    {
        if (!$this->itemCategories->contains($itemCategory)) {
            $this->itemCategories[] = $itemCategory;
            $itemCategory->setBranch($this);
        }

        return $this;
    }

    public function removeItemCategory(ItemCategoryEntity $itemCategory): self
    {
        if ($this->itemCategories->removeElement($itemCategory)) {
            // set the owning side to null (unless already changed)
            if ($itemCategory->getBranch() === $this) {
                $itemCategory->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ItemEntity>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(ItemEntity $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setBranch($this);
        }

        return $this;
    }

    public function removeItem(ItemEntity $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getBranch() === $this) {
                $item->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SpeciesEntity>
     */
    public function getSpecieses(): Collection
    {
        return $this->specieses;
    }

    public function addSpeciese(SpeciesEntity $speciese): self
    {
        if (!$this->specieses->contains($speciese)) {
            $this->specieses[] = $speciese;
            $speciese->setBranch($this);
        }

        return $this;
    }

    public function removeSpeciese(SpeciesEntity $speciese): self
    {
        if ($this->specieses->removeElement($speciese)) {
            // set the owning side to null (unless already changed)
            if ($speciese->getBranch() === $this) {
                $speciese->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FacilityEntity>
     */
    public function getFacilities(): Collection
    {
        return $this->facilities;
    }

    public function addFacility(FacilityEntity $facility): self
    {
        if (!$this->facilities->contains($facility)) {
            $this->facilities[] = $facility;
            $facility->setBranch($this);
        }

        return $this;
    }

    public function removeFacility(FacilityEntity $facility): self
    {
        if ($this->facilities->removeElement($facility)) {
            // set the owning side to null (unless already changed)
            if ($facility->getBranch() === $this) {
                $facility->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ShelterAdmissionEntity>
     */
    public function getShelterAdmissions(): Collection
    {
        return $this->shelterAdmissions;
    }

    public function addShelterAdmission(ShelterAdmissionEntity $shelterAdmission): self
    {
        if (!$this->shelterAdmissions->contains($shelterAdmission)) {
            $this->shelterAdmissions[] = $shelterAdmission;
            $shelterAdmission->setBranch($this);
        }

        return $this;
    }

    public function removeShelterAdmission(ShelterAdmissionEntity $shelterAdmission): self
    {
        if ($this->shelterAdmissions->removeElement($shelterAdmission)) {
            // set the owning side to null (unless already changed)
            if ($shelterAdmission->getBranch() === $this) {
                $shelterAdmission->setBranch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PetEntity>
     */
    public function getPets(): Collection
    {
        return $this->pets;
    }

    public function addPet(PetEntity $pet): self
    {
        if (!$this->pets->contains($pet)) {
            $this->pets[] = $pet;
            $pet->setBranch($this);
        }

        return $this;
    }

    public function removePet(PetEntity $pet): self
    {
        if ($this->pets->removeElement($pet)) {
            // set the owning side to null (unless already changed)
            if ($pet->getBranch() === $this) {
                $pet->setBranch(null);
            }
        }

        return $this;
    }



    

 

}
