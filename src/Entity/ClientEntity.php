<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 * @ORM\Table(name="client")
 * @ORM\HasLifecycleCallbacks()
 */

class ClientEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="middle_name", type="string", nullable=true)
     */
    protected $middleName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="contact_no", type="string", nullable=true)
     */
    protected $contactNo;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="clients")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\OneToMany(targetEntity="PetEntity", mappedBy="client", cascade={"remove"})
     */
    protected $pets;

     /**
     * @ORM\OneToMany(targetEntity="ClientPetEntity", mappedBy="client", cascade={"remove"})
     */
    protected $clientPets;

    /**
     * @ORM\OneToMany(targetEntity="AdmissionEntity", mappedBy="client", cascade={"remove"})
     */
    protected $admissions;

    /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="client", cascade={"remove"})
     */
    protected $schedules;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceEntity", mappedBy="client", cascade={"remove"})
     */
    protected $invoices;



  

    public function __construct()
    {
        $this->pets = new ArrayCollection();
        $this->admissions = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->clientPets = new ArrayCollection();
    }

    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Client Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName() {

        return $this->firstName . ' ' . $this->lastName;
    }


/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientEntity
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getContactNo(): ?string
    {
        return $this->contactNo;
    }

    public function setContactNo(?string $contactNo): self
    {
        $this->contactNo = $contactNo;

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
            $pet->setClient($this);
        }

        return $this;
    }

    public function removePet(PetEntity $pet): self
    {
        if ($this->pets->removeElement($pet)) {
            // set the owning side to null (unless already changed)
            if ($pet->getClient() === $this) {
                $pet->setClient(null);
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
            $admission->setClient($this);
        }

        return $this;
    }

    public function removeAdmission(AdmissionEntity $admission): self
    {
        if ($this->admissions->removeElement($admission)) {
            // set the owning side to null (unless already changed)
            if ($admission->getClient() === $this) {
                $admission->setClient(null);
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
            $schedule->setClient($this);
        }

        return $this;
    }

    public function removeSchedule(ScheduleEntity $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getClient() === $this) {
                $schedule->setClient(null);
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
            $invoice->setClient($this);
        }

        return $this;
    }

    public function removeInvoice(InvoiceEntity $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientPetEntity>
     */
    public function getClientPets(): Collection
    {
        return $this->clientPets;
    }

    public function addClientPet(ClientPetEntity $clientPet): self
    {
        if (!$this->clientPets->contains($clientPet)) {
            $this->clientPets[] = $clientPet;
            $clientPet->setClient($this);
        }

        return $this;
    }

    public function removeClientPet(ClientPetEntity $clientPet): self
    {
        if ($this->clientPets->removeElement($clientPet)) {
            // set the owning side to null (unless already changed)
            if ($clientPet->getClient() === $this) {
                $clientPet->setClient(null);
            }
        }

        return $this;
    }



    

   
    
}
