<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShelterAdmissionRepository")
 * @ORM\Table(name="shelter_admission")
 * @ORM\HasLifecycleCallbacks()
 */

class ShelterAdmissionEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="status", type="string")
     */
    protected $status;
        
    /**
     * @ORM\Column(name="adopter_name", type="string", nullable=true))
     */
    protected $adopterName;

    /**
     * @ORM\Column(name="adopter_contact", type="string", nullable=true))
     */
    protected $adopterContact;

    /**
     * @ORM\Column(name="adopter_address", type="string", nullable=true))
     */
    protected $adopterAddress;

    /**
     * @ORM\Column(name="adopter_email_address", type="string", nullable=true))
     */
    protected $adopterEmailAddress;
    /**
     * @ORM\Column(name="foster_name", type="string", nullable=true))
     */
    protected $fosterName;

    /**
     * @ORM\Column(name="foster_contact", type="string", nullable=true))
     */
    protected $fosterContact;

    /**
     * @ORM\Column(name="foster_address", type="string", nullable=true))
     */
    protected $fosterAddress;

    /**
     * @ORM\Column(name="foster_email_address", type="string", nullable=true))
     */
    protected $fosterEmailAddress;

    /**
     * @ORM\Column(name="rescuer_name", type="string", nullable=true))
     */
    protected $rescuerName;
    
    /**
     * @ORM\Column(name="rescue_place", type="string", nullable=true))
     */
    protected $rescuePlace;

    /**
     * @ORM\Column(name="rescue_story", type="text", nullable=true))
     */
    protected $rescueStory;
    

    /**
     * @ORM\Column(name="admission_type", type="string")
     */
    protected $admissionType;

    /**
     * @ORM\Column(name="rescuer_contact", type="string", nullable=true))
     */
    protected $rescuerContact;
    
    /**
     * @ORM\Column(name="admission_date", type="datetime", nullable=true)
     */
    protected $admissionDate;

    /**
     * @ORM\Column(name="rescue_date", type="datetime", nullable=true)
     */
    protected $rescueDate;

    /**
     * @ORM\Column(name="adoption_date", type="datetime", nullable=true)
     */
    protected $adoptionDate;
    
    /**
     * @ORM\Column(name="returned_date", type="datetime", nullable=true)
     */
    protected $returnedDate;

    /**
     * @ORM\Column(name="returned_reason", type="string", nullable=true))
     */
    protected $returnedReason;

    /**
     * @ORM\Column(name="remarks", type="string", nullable=true))
     */
    protected $remarks;

    /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="shelterAdmissions")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="shelterAdmissions")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="FacilityEntity", inversedBy="shelterAdmissions")
     * @ORM\JoinColumn(name="facility_id", referencedColumnName="id", nullable=true)
     */
    protected $facility;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordEntity", mappedBy="shelterAdmission", cascade={"remove"})
     */
    protected $medicalRecords;

    public function __construct()
    {
        $this->medicalRecords = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Admission Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ShelterAdmissionEntity
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

    public function getAdopterName(): ?string
    {
        return $this->adopterName;
    }

    public function setAdopterName(string $adopterName): self
    {
        $this->adopterName = $adopterName;

        return $this;
    }

    public function getAdopterContact(): ?string
    {
        return $this->adopterContact;
    }

    public function setAdopterContact(string $adopterContact): self
    {
        $this->adopterContact = $adopterContact;

        return $this;
    }

    public function getRescuerName(): ?string
    {
        return $this->rescuerName;
    }

    public function setRescuerName(string $rescuerName): self
    {
        $this->rescuerName = $rescuerName;

        return $this;
    }

    public function getRescuerContact(): ?string
    {
        return $this->rescuerContact;
    }

    public function setRescuerContact(string $rescuerContact): self
    {
        $this->rescuerContact = $rescuerContact;

        return $this;
    }

    public function getPet(): ?PetEntity
    {
        return $this->pet;
    }

    public function setPet(?PetEntity $pet): self
    {
        $this->pet = $pet;

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

    public function getFacility(): ?FacilityEntity
    {
        return $this->facility;
    }

    public function setFacility(?FacilityEntity $facility): self
    {
        $this->facility = $facility;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(?\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;

        return $this;
    }

    public function getAdoptionDate(): ?\DateTimeInterface
    {
        return $this->adoptionDate;
    }

    public function setAdoptionDate(?\DateTimeInterface $adoptionDate): self
    {
        $this->adoptionDate = $adoptionDate;

        return $this;
    }

    public function getReturnedDate(): ?\DateTimeInterface
    {
        return $this->returnedDate;
    }

    public function setReturnedDate(?\DateTimeInterface $returnedDate): self
    {
        $this->returnedDate = $returnedDate;

        return $this;
    }

    public function getReturnedReason(): ?string
    {
        return $this->returnedReason;
    }

    public function setReturnedReason(string $returnedReason): self
    {
        $this->returnedReason = $returnedReason;

        return $this;
    }

    public function getAdmissionType(): ?string
    {
        return $this->admissionType;
    }

    public function setAdmissionType(string $admissionType): self
    {
        $this->admissionType = $admissionType;

        return $this;
    }

    public function getAdopterAddress(): ?string
    {
        return $this->adopterAddress;
    }

    public function setAdopterAddress(?string $adopterAddress): self
    {
        $this->adopterAddress = $adopterAddress;

        return $this;
    }

    public function getAdopterEmailAddress(): ?string
    {
        return $this->adopterEmailAddress;
    }

    public function setAdopterEmailAddress(?string $adopterEmailAddress): self
    {
        $this->adopterEmailAddress = $adopterEmailAddress;

        return $this;
    }

    public function getRescuePlace(): ?string
    {
        return $this->rescuePlace;
    }

    public function setRescuePlace(?string $rescuePlace): self
    {
        $this->rescuePlace = $rescuePlace;

        return $this;
    }

    public function getRescueStory(): ?string
    {
        return $this->rescueStory;
    }

    public function setRescueStory(?string $rescueStory): self
    {
        $this->rescueStory = $rescueStory;

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
            $medicalRecord->setShelterAdmission($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecordEntity $medicalRecord): self
    {
        if ($this->medicalRecords->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getShelterAdmission() === $this) {
                $medicalRecord->setShelterAdmission(null);
            }
        }

        return $this;
    }

    public function getRescueDate(): ?\DateTimeInterface
    {
        return $this->rescueDate;
    }

    public function setRescueDate(?\DateTimeInterface $rescueDate): self
    {
        $this->rescueDate = $rescueDate;

        return $this;
    }

    public function getFosterName(): ?string
    {
        return $this->fosterName;
    }

    public function setFosterName(?string $fosterName): self
    {
        $this->fosterName = $fosterName;

        return $this;
    }

    public function getFosterContact(): ?string
    {
        return $this->fosterContact;
    }

    public function setFosterContact(?string $fosterContact): self
    {
        $this->fosterContact = $fosterContact;

        return $this;
    }

    public function getFosterAddress(): ?string
    {
        return $this->fosterAddress;
    }

    public function setFosterAddress(?string $fosterAddress): self
    {
        $this->fosterAddress = $fosterAddress;

        return $this;
    }

    public function getFosterEmailAddress(): ?string
    {
        return $this->fosterEmailAddress;
    }

    public function setFosterEmailAddress(?string $fosterEmailAddress): self
    {
        $this->fosterEmailAddress = $fosterEmailAddress;

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

    public function getVaccineLotNo(): ?string
    {
        return $this->vaccineLotNo;
    }

    public function setVaccineLotNo(?string $vaccineLotNo): self
    {
        $this->vaccineLotNo = $vaccineLotNo;

        return $this;
    }

    public function getVaccineBatchNo(): ?string
    {
        return $this->vaccineBatchNo;
    }

    public function setVaccineBatchNo(?string $vaccineBatchNo): self
    {
        $this->vaccineBatchNo = $vaccineBatchNo;

        return $this;
    }

        
}
