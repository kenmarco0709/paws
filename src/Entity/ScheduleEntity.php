<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScheduleRepository")
 * @ORM\Table(name="schedule")
 * @ORM\HasLifecycleCallbacks()
 */

class ScheduleEntity extends BaseEntity
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
     * @ORM\Column(name="sms_status", type="string", nullable=true)
     */
    protected $smsStatus;

    /**
     * @ORM\Column(name="schedule_date", type="datetime", nullable=true)
     */
    protected $scheduleDate;

    /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;
    
    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="schedules")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="vetSchedules")
     * @ORM\JoinColumn(name="attending_vet_id", referencedColumnName="id", nullable=true)
     */
    protected $attendingVet;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="schedules")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

       /**
     * @ORM\ManyToOne(targetEntity="AdmissionTypeEntity", inversedBy="schedules")
     * @ORM\JoinColumn(name="admission_type_id", referencedColumnName="id", nullable=true)
     */
    protected $admissionType;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="schedules")
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id", nullable=true)
     */
    protected $admission;

    /**
     * @ORM\ManyToOne(targetEntity="MedicalRecordEntity", inversedBy="schedules")
     * @ORM\JoinColumn(name="medical_record_id", referencedColumnName="id", nullable=true)
     */
    protected $medicalRecord;

    /**
     * @ORM\OneToMany(targetEntity="SchedulePetEntity", mappedBy="schedule", cascade={"remove", "persist"})
     */
    protected $schedulePets;

    public function __construct()
    {
        $this->schedulePets = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Admission Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return Schedule
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    
/**
     * Set schedulePetIds
     *
     *
     * @return array
     */
    public function schedulePetIds()
    {
        $ids = [];

        foreach($this->getSchedulePets() as $k => $schedulePet){

            $ids[$k] = $schedulePet->getPet()->getId();
        }

        return $ids;
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

    public function getScheduleDate(): ?\DateTimeInterface
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(?\DateTimeInterface $scheduleDate): self
    {
        $this->scheduleDate = $scheduleDate;

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

    public function getAdmission(): ?AdmissionEntity
    {
        return $this->admission;
    }

    public function setAdmission(?AdmissionEntity $admission): self
    {
        $this->admission = $admission;

        return $this;
    }

    /**
     * @return Collection<int, SchedulePetEntity>
     */
    public function getSchedulePets(): Collection
    {
        return $this->schedulePets;
    }

    public function addSchedulePet(SchedulePetEntity $schedulePet): self
    {
        if (!$this->schedulePets->contains($schedulePet)) {
            $this->schedulePets[] = $schedulePet;
            $schedulePet->setSchedule($this);
        }

        return $this;
    }

    public function removeSchedulePet(SchedulePetEntity $schedulePet): self
    {
        if ($this->schedulePets->removeElement($schedulePet)) {
            // set the owning side to null (unless already changed)
            if ($schedulePet->getSchedule() === $this) {
                $schedulePet->setSchedule(null);
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

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getSmsStatus(): ?string
    {
        return $this->smsStatus;
    }

    public function setSmsStatus(?string $smsStatus): self
    {
        $this->smsStatus = $smsStatus;

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



    

    




    
}
