<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 */

class UserEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

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
     * @ORM\Column(name="prc_no", type="string", nullable=true)
     */
    protected $prcNo;

      /**
     * @ORM\Column(name="prc_expiration_date", type="datetime", nullable=true)
     */
    protected $prcExpirationDate;
    
    /**
     * @ORM\Column(name="ptr", type="string", nullable=true)
     */
    protected $ptr;

     /**
     * @ORM\Column(name="tin_no", type="string", nullable=true)
     */
    protected $tinNo;

     /**
     * @ORM\Column(name="s2", type="string", nullable=true)
     */
    protected $s2;

    /**
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    protected $gender;

    /**
     * @ORM\Column(name="employee_status", type="string", nullable=true)
     */
    protected $employeeStatus;

    /**
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="contact_no", type="string", nullable=true)
     */
    protected $contactNo;


    /**
     * @ORM\ManyToOne(targetEntity="CompanyEntity", inversedBy="users")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="users")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\OneToMany(targetEntity="UserAccessEntity", mappedBy="user", cascade={"remove"})
     */
    protected $userAccesses;

    /**
     * @ORM\OneToMany(targetEntity="AdmissionEntity", mappedBy="vet", cascade={"remove"})
     */
    protected $vetAdmisssions;

     /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="attendingVet", cascade={"remove"})
     */
    protected $vetSchedules;

     /**
     * @ORM\OneToMany(targetEntity="VetScheduleEntity", mappedBy="vet", cascade={"remove"})
     */
    protected $vetVetSchedules;

     /**
     * @ORM\OneToMany(targetEntity="MedicalRecordEntity", mappedBy="attendingVet", cascade={"remove"})
     */
    protected $shelterMedicalRecords;

    /**
     * @ORM\OneToMany(targetEntity="AuditTrailEntity", mappedBy="user", cascade={"remove"})
     */
    protected $auditTrail;


    /**
     * Set createdAt
     *
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdBy = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->createdAt = new \DateTime();
        $this->updatedBy = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->updatedAt = new \DateTime();
    }

    /**
     * Set updatedAt
     *
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedBy = isset($_COOKIE['username']) ? $_COOKIE['username'] : 'System';
        $this->updatedAt = new \DateTime();
    }

    public function __construct($data = null)
    {

        $this->userAccesses = new ArrayCollection();
        $this->vetAdmisssions = new ArrayCollection();
        $this->vetSchedules = new ArrayCollection();
        $this->auditTrail = new ArrayCollection();
        $this->vetVetSchedules = new ArrayCollection();
        $this->shelterMedicalRecords = new ArrayCollection();

    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					User Defined Setters and Getters													  */
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
     * @return UserEntity
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getPrcNo(): ?string
    {
        return $this->prcNo;
    }

    public function setPrcNo(?string $prcNo): self
    {
        $this->prcNo = $prcNo;

        return $this;
    }

    public function getPrcExpirationDate(): ?string
    {
        return $this->prcExpirationDate;
    }

    public function setPrcExpirationDate(?string $prcExpirationDate): self
    {
        $this->prcExpirationDate = $prcExpirationDate;

        return $this;
    }

    public function getPtr(): ?string
    {
        return $this->ptr;
    }

    public function setPtr(?string $ptr): self
    {
        $this->ptr = $ptr;

        return $this;
    }

    public function getTinNo(): ?string
    {
        return $this->tinNo;
    }

    public function setTinNo(?string $tinNo): self
    {
        $this->tinNo = $tinNo;

        return $this;
    }

    public function getS2(): ?string
    {
        return $this->s2;
    }

    public function setS2(?string $s2): self
    {
        $this->s2 = $s2;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getEmployeeStatus(): ?string
    {
        return $this->employeeStatus;
    }

    public function setEmployeeStatus(?string $employeeStatus): self
    {
        $this->employeeStatus = $employeeStatus;

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

    public function getCompany(): ?CompanyEntity
    {
        return $this->company;
    }

    public function setCompany(?CompanyEntity $company): self
    {
        $this->company = $company;

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
     * @return Collection<int, UserAccessEntity>
     */
    public function getUserAccesses(): Collection
    {
        return $this->userAccesses;
    }

    public function addUserAccess(UserAccessEntity $userAccess): self
    {
        if (!$this->userAccesses->contains($userAccess)) {
            $this->userAccesses[] = $userAccess;
            $userAccess->setUser($this);
        }

        return $this;
    }

    public function removeUserAccess(UserAccessEntity $userAccess): self
    {
        if ($this->userAccesses->removeElement($userAccess)) {
            // set the owning side to null (unless already changed)
            if ($userAccess->getUser() === $this) {
                $userAccess->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdmissionEntity>
     */
    public function getVetAdmisssions(): Collection
    {
        return $this->vetAdmisssions;
    }

    public function addVetAdmisssion(AdmissionEntity $vetAdmisssion): self
    {
        if (!$this->vetAdmisssions->contains($vetAdmisssion)) {
            $this->vetAdmisssions[] = $vetAdmisssion;
            $vetAdmisssion->setVet($this);
        }

        return $this;
    }

    public function removeVetAdmisssion(AdmissionEntity $vetAdmisssion): self
    {
        if ($this->vetAdmisssions->removeElement($vetAdmisssion)) {
            // set the owning side to null (unless already changed)
            if ($vetAdmisssion->getVet() === $this) {
                $vetAdmisssion->setVet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScheduleEntity>
     */
    public function getVetSchedules(): Collection
    {
        return $this->vetSchedules;
    }

    public function addVetSchedule(ScheduleEntity $vetSchedule): self
    {
        if (!$this->vetSchedules->contains($vetSchedule)) {
            $this->vetSchedules[] = $vetSchedule;
            $vetSchedule->setAttendingVet($this);
        }

        return $this;
    }

    public function removeVetSchedule(ScheduleEntity $vetSchedule): self
    {
        if ($this->vetSchedules->removeElement($vetSchedule)) {
            // set the owning side to null (unless already changed)
            if ($vetSchedule->getAttendingVet() === $this) {
                $vetSchedule->setAttendingVet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AuditTrailEntity>
     */
    public function getAuditTrail(): Collection
    {
        return $this->auditTrail;
    }

    public function addAuditTrail(AuditTrailEntity $auditTrail): self
    {
        if (!$this->auditTrail->contains($auditTrail)) {
            $this->auditTrail[] = $auditTrail;
            $auditTrail->setUser($this);
        }

        return $this;
    }

    public function removeAuditTrail(AuditTrailEntity $auditTrail): self
    {
        if ($this->auditTrail->removeElement($auditTrail)) {
            // set the owning side to null (unless already changed)
            if ($auditTrail->getUser() === $this) {
                $auditTrail->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VetScheduleEntity>
     */
    public function getVetVetSchedules(): Collection
    {
        return $this->vetVetSchedules;
    }

    public function addVetVetSchedule(VetScheduleEntity $vetVetSchedule): self
    {
        if (!$this->vetVetSchedules->contains($vetVetSchedule)) {
            $this->vetVetSchedules[] = $vetVetSchedule;
            $vetVetSchedule->setVet($this);
        }

        return $this;
    }

    public function removeVetVetSchedule(VetScheduleEntity $vetVetSchedule): self
    {
        if ($this->vetVetSchedules->removeElement($vetVetSchedule)) {
            // set the owning side to null (unless already changed)
            if ($vetVetSchedule->getVet() === $this) {
                $vetVetSchedule->setVet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordEntity>
     */
    public function getShelterMedicalRecords(): Collection
    {
        return $this->shelterMedicalRecords;
    }

    public function addShelterMedicalRecord(MedicalRecordEntity $shelterMedicalRecord): self
    {
        if (!$this->shelterMedicalRecords->contains($shelterMedicalRecord)) {
            $this->shelterMedicalRecords[] = $shelterMedicalRecord;
            $shelterMedicalRecord->setAttendingVet($this);
        }

        return $this;
    }

    public function removeShelterMedicalRecord(MedicalRecordEntity $shelterMedicalRecord): self
    {
        if ($this->shelterMedicalRecords->removeElement($shelterMedicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($shelterMedicalRecord->getAttendingVet() === $this) {
                $shelterMedicalRecord->setAttendingVet(null);
            }
        }

        return $this;
    }



    

    


    
}
