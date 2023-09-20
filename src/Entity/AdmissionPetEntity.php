<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdmissionPetRepository")
 * @ORM\Table(name="admission_pet")
 * @ORM\HasLifecycleCallbacks()
 */

class AdmissionPetEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionEntity", inversedBy="admissionPets", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="admission_id", referencedColumnName="id", nullable=true)
     */
    protected $admission;
    
    /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="admissionPets")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;
      
    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordEntity", mappedBy="admissionPet", cascade={"remove", "persist"})
     * @ORM\OrderBy({"id" = "DESC"})
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
     * @return AdmissionPetEntity
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

    public function getAdmission(): ?AdmissionEntity
    {
        return $this->admission;
    }

    public function setAdmission(?AdmissionEntity $admission): self
    {
        $this->admission = $admission;

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
            $medicalRecord->setAdmissionPet($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecordEntity $medicalRecord): self
    {
        if ($this->medicalRecords->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getAdmissionPet() === $this) {
                $medicalRecord->setAdmissionPet(null);
            }
        }

        return $this;
    }



    



    
}
