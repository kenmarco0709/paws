<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdmissionRepository")
 * @ORM\Table(name="admission")
 * @ORM\HasLifecycleCallbacks()
 */

class AdmissionEntity extends BaseEntity
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
     * @ORM\Column(name="discharged_date", type="datetime", nullable=true)
     */
    protected $dischargedDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="admissions")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="vetAdmisssions")
     * @ORM\JoinColumn(name="attending_vet_id", referencedColumnName="id", nullable=true)
     */
    protected $attendingVet;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="admissions")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

       /**
     * @ORM\ManyToOne(targetEntity="AdmissionTypeEntity", inversedBy="admissions")
     * @ORM\JoinColumn(name="admission_type_id", referencedColumnName="id", nullable=true)
     */
    protected $admissionType;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="admissions")
     * @ORM\JoinColumn(name="admission_history_id", referencedColumnName="id", nullable=true)
     */
    protected $admissionHistory;

    /**
     * @ORM\OneToMany(targetEntity="AdmissionPetEntity", mappedBy="admission", cascade={"remove", "persist"})
     */
    protected $admissionPets;

    /**
     * @ORM\OneToMany(targetEntity="BillingEntity", mappedBy="admission", cascade={"remove"})
     */
    protected $billings;

        /**
     * @ORM\OneToMany(targetEntity="AdmissionEntity", mappedBy="admissionHistory", cascade={"remove"})
     */
    protected $admissions;

    /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="admission", cascade={"remove"})
     */
    protected $schedules;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceEntity", mappedBy="admission", cascade={"remove"})
     */
    protected $invoices;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionInventoryItemEntity", mappedBy="admission", cascade={"remove"})
     */
    protected $invoiceAdmissionInventoryItems;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceAdmissionServiceEntity", mappedBy="admission", cascade={"remove"})
     */
    protected $invoiceAdmissionServices;


    public function __construct()
    {
        $this->admissionPets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->admissions = new ArrayCollection();
        $this->billings = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->invoiceAdmissionInventoryItems = new ArrayCollection();
        $this->invoiceAdmissionServices = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Admission Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return AdmissionEntity
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

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

    public function getAdmissionType(): ?AdmissionTypeEntity
    {
        return $this->admissionType;
    }

    public function setAdmissionType(?AdmissionTypeEntity $admissionType): self
    {
        $this->admissionType = $admissionType;

        return $this;
    }

    /**
     * @return Collection<int, AdmissionPetEntity>
     */
    public function getAdmissionPets(): Collection
    {
        return $this->admissionPets;
    }

    public function addAdmissionPet(AdmissionPetEntity $admissionPet): self
    {
        if (!$this->admissionPets->contains($admissionPet)) {
            $this->admissionPets[] = $admissionPet;
            $admissionPet->setAdmission($this);
        }

        return $this;
    }

    public function removeAdmissionPet(AdmissionPetEntity $admissionPet): self
    {
        if ($this->admissionPets->removeElement($admissionPet)) {
            // set the owning side to null (unless already changed)
            if ($admissionPet->getAdmission() === $this) {
                $admissionPet->setAdmission(null);
            }
        }

        return $this;
    }

    public function getAttendingVet(): ?UserEntity
    {
        return $this->attendingVet;
    }

    public function setAttendingVet(?UserEntity $attendingVet): self
    {
        $this->attendingVet = $attendingVet;

        return $this;
    }

    public function getAdmissionHistory(): ?self
    {
        return $this->admissionHistory;
    }

    public function setAdmissionHistory(?self $admissionHistory): self
    {
        $this->admissionHistory = $admissionHistory;

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
            $admission->setAdmissionHistory($this);
        }

        return $this;
    }

    public function removeAdmission(AdmissionEntity $admission): self
    {
        if ($this->admissions->removeElement($admission)) {
            // set the owning side to null (unless already changed)
            if ($admission->getAdmissionHistory() === $this) {
                $admission->setAdmissionHistory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BillingEntity>
     */
    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(BillingEntity $billing): self
    {
        if (!$this->billings->contains($billing)) {
            $this->billings[] = $billing;
            $billing->setAdmission($this);
        }

        return $this;
    }

    public function removeBilling(BillingEntity $billing): self
    {
        if ($this->billings->removeElement($billing)) {
            // set the owning side to null (unless already changed)
            if ($billing->getAdmission() === $this) {
                $billing->setAdmission(null);
            }
        }

        return $this;
    }

    public function getDischargedDate(): ?\DateTimeInterface
    {
        return $this->dischargedDate;
    }

    public function setDischargedDate(?\DateTimeInterface $dischargedDate): self
    {
        $this->dischargedDate =  $dischargedDate;

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
            $schedule->setAdmission($this);
        }

        return $this;
    }

    public function removeSchedule(ScheduleEntity $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getAdmission() === $this) {
                $schedule->setAdmission(null);
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
            $invoiceAdmissionInventoryItem->setAdmission($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionInventoryItem(InvoiceAdmissionInventoryItemEntity $invoiceAdmissionInventoryItem): self
    {
        if ($this->invoiceAdmissionInventoryItems->removeElement($invoiceAdmissionInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionInventoryItem->getAdmission() === $this) {
                $invoiceAdmissionInventoryItem->setAdmission(null);
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
            $invoiceAdmissionService->setAdmission($this);
        }

        return $this;
    }

    public function removeInvoiceAdmissionService(InvoiceAdmissionServiceEntity $invoiceAdmissionService): self
    {
        if ($this->invoiceAdmissionServices->removeElement($invoiceAdmissionService)) {
            // set the owning side to null (unless already changed)
            if ($invoiceAdmissionService->getAdmission() === $this) {
                $invoiceAdmissionService->setAdmission(null);
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
            $invoice->setAdmission($this);
        }

        return $this;
    }

    public function removeInvoice(InvoiceEntity $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getAdmission() === $this) {
                $invoice->setAdmission(null);
            }
        }

        return $this;
    }





    
}
