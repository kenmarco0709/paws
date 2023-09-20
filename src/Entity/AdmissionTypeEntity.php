<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdmissionTypeRepository")
 * @ORM\Table(name="admission_type")
 * @ORM\HasLifecycleCallbacks()
 */

class AdmissionTypeEntity extends BaseEntity
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
     * @ORM\OneToMany(targetEntity="AdmissionEntity", mappedBy="transactionType", cascade={"remove"})
     */
    protected $admissions;

    /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="transactionType", cascade={"remove"})
     */
    protected $schedules;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordEntity", mappedBy="admissionType", cascade={"remove"})
     */
    protected $medicalRecords;

    public function __construct($data = null)
    {
        $this->admissions = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->medicalRecords = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					AdmissionType Defined Setters and Getters											  */
    /*--------------------------------------------------------------------------------------------------------*/

/**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return AdmissionTypeEntity
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
            $admission->setTransactionType($this);
        }

        return $this;
    }

    public function removeAdmission(AdmissionEntity $admission): self
    {
        if ($this->admissions->removeElement($admission)) {
            // set the owning side to null (unless already changed)
            if ($admission->getTransactionType() === $this) {
                $admission->setTransactionType(null);
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
            $schedule->setTransactionType($this);
        }

        return $this;
    }

    public function removeSchedule(ScheduleEntity $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getTransactionType() === $this) {
                $schedule->setTransactionType(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordEntity>
     */
    public function getMedicalRecords(): Collection
    {
        return $this->medicalRecords;
    }

    public function addMedicalRecord(MedicalRecordEntity $medicalRecord): self
    {
        if (!$this->medicalRecords->contains($medicalRecord)) {
            $this->medicalRecords[] = $medicalRecord;
            $medicalRecord->setAdmissionType($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecordEntity $medicalRecord): self
    {
        if ($this->medicalRecords->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getAdmissionType() === $this) {
                $medicalRecord->setAdmissionType(null);
            }
        }

        return $this;
    }



    

   

}
