<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetRepository")
 * @ORM\Table(name="pet")
 * @ORM\HasLifecycleCallbacks()
 */

class PetEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    protected $gender;

    /**
     * @ORM\Column(name="color_markings", type="string", nullable=true)
     */
    protected $colorMarkings;

    /**
     * @ORM\Column(name="height", type="string", nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(name="approximate_age", type="string", nullable=true)
     */
    protected $approximateAge;
    
    /**
     * @ORM\Column(name="cause_of_death", type="string", nullable=true)
     */
    protected $causeOfDeath;

    /**
     * @ORM\Column(name="birth_date", type="datetime", nullable=true)
     */
    protected $birthDate;

    /**
     * @ORM\Column(name="profile_pic_desc", type="string", nullable=true)
     */
    protected $profilePicDesc;

    /**
     * @ORM\Column(name="is_shelter_pet", type="boolean", nullable=true)
     */
    protected $isShelterPet;

    /**
     * @ORM\Column(name="is_fixed", type="boolean", nullable=true)
     */
    protected $isFixed;

    /**
     * @ORM\Column(name="fixed_date", type="datetime", nullable=true)
     */
    protected $fixedDate;

    /**
     * @ORM\Column(name="death_date", type="datetime", nullable=true)
     */
    protected $deathDate;
    
    /**
     * @ORM\Column(name="has_cruel_file", type="boolean", nullable=true)
     */
    protected $hasCruelFile;
    
    /**
     * @ORM\Column(name="is_deceased", type="boolean", nullable=true)
     */
    protected $isDeceased;
    
     /**
     * @ORM\OneToMany(targetEntity="ClientPetEntity", mappedBy="pet", cascade={"remove"})
     */
    protected $clientPets;
    
     /**
     * @ORM\Column(name="parsed_profile_pic_desc", type="string", nullable=true)
     */
    protected $parsedProfilePicDesc;

    /**
     * @ORM\Column(name="admission_date", type="datetime", nullable=true)
     */
    protected $admissionDate;

        /**
     * @ORM\Column(name="before_file_photo_description", type="string", nullable=true)
     */
    protected $beforeFilePhotoDescription;

    
    /**
     * @ORM\Column(name="parsed_before_file_photo_description", type="string", nullable=true)
     */
    protected $parsedBeforeFilePhotoDescription;

    
        /**
     * @ORM\Column(name="after_file_photo_description", type="string", nullable=true)
     */
    protected $afterFilePhotoDescription;

    
    /**
     * @ORM\Column(name="parsed_after_file_photo_description", type="string", nullable=true)
     */
    protected $parsedAfterFilePhotoDescription;

     /**
     * @ORM\Column(name="parsed_description", type="string", nullable=true)
     */
    protected $parsedDescription;

     /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="pets")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;
    
    /**
     * @ORM\ManyToOne(targetEntity="BreedEntity", inversedBy="pets")
     * @ORM\JoinColumn(name="breed_id", referencedColumnName="id", nullable=true)
     */
    protected $breed;

    /**
     * @ORM\ManyToOne(targetEntity="StageEntity", inversedBy="pets")
     * @ORM\JoinColumn(name="stage_id", referencedColumnName="id", nullable=true)
     */
    protected $stage;
    
    /**
     * @ORM\ManyToOne(targetEntity="SpeciesEntity", inversedBy="pets")
     * @ORM\JoinColumn(name="species_id", referencedColumnName="id", nullable=true)
     */
    protected $species;
    
    /**
     * @ORM\OneToMany(targetEntity="AdmissionPetEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $admissionPets;

    
    /**
     * @ORM\OneToMany(targetEntity="ShelterAdmissionEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $shelterAdmissions;

    /**
     * @ORM\OneToMany(targetEntity="CabinetFormEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $cabinetForms;

    /**
     * @ORM\OneToMany(targetEntity="BehaviorRecordEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $behaviorRecords;

    /**
     * @ORM\OneToMany(targetEntity="PetFileEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $petFiles;

        /**
     * @ORM\OneToMany(targetEntity="PetPhotoEntity", mappedBy="pet", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $petPhotos;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="pets")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    public function __construct()
    {
        $this->admissionPets = new ArrayCollection();
        $this->clientPets = new ArrayCollection();
        $this->cabinetForms = new ArrayCollection();
        $this->shelterAdmissions = new ArrayCollection();
        $this->behaviorRecords = new ArrayCollection();
        $this->petFiles = new ArrayCollection();
        $this->petPhotos = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Pet Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/


    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedProfilePicDesc;
        if(!empty($this->profilePic) && file_exists($file)) unlink($file);
    }

    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeBeforeFilePhoto() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedBeforeFilePhotoDescription;
        if(!empty($this->profilePic) && file_exists($file)) unlink($file);
    }

    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeAfterFilePhoto() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedAfterFilePhotoDescription;
        if(!empty($this->profilePic) && file_exists($file)) unlink($file);
    }

    /**
     * Get uploadDir
     *
     * @return string
     */
    public function getUploadDir() {

        return '/uploads/file';
    }

    /**
     * Get uploadRootDir
     *
     * @return string
     */
    public function getUploadRootDir() {

        return __DIR__ . './../../public' . $this->getUploadDir();
    }


    /**
     * get fileWebPath
     *
     * @return string
     */
    public function getFileWebPath() {

        $parsedDesc = $this->parsedProfilePicDesc;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }

        /**
     * get beforeFileWebPath
     *
     * @return string
     */
    public function getBeforeFileWebPath() {

        $parsedDesc = $this->parsedBeforeFilePhotoDescription;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }

    /**
     * get afterFileWebPath
     *
     * @return string
     */
    public function getAfterFileWebPath() {

        $parsedDesc = $this->parsedAfterFilePhotoDescription;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }

    /**
     * Get age
     *
     *
     * @return string
     */
    public function age()
    {
        
        $birthday = $this->getBirthDate();
        $interval = $birthday->diff(new \DateTime);
        return $interval->y;
    }

  /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PetEntity
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getIsDeleted(): ?bool
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getColorMarkings(): ?string
    {
        return $this->colorMarkings;
    }

    public function setColorMarkings(?string $colorMarkings): self
    {
        $this->colorMarkings = $colorMarkings;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

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

    public function getBreed(): ?BreedEntity
    {
        return $this->breed;
    }

    public function setBreed(?BreedEntity $breed): self
    {
        $this->breed = $breed;

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
            $admissionPet->setPet($this);
        }

        return $this;
    }

    public function removeAdmissionPet(AdmissionPetEntity $admissionPet): self
    {
        if ($this->admissionPets->removeElement($admissionPet)) {
            // set the owning side to null (unless already changed)
            if ($admissionPet->getPet() === $this) {
                $admissionPet->setPet(null);
            }
        }

        return $this;
    }

    public function getProfilePicDesc(): ?string
    {
        return $this->profilePicDesc;
    }

    public function setProfilePicDesc(?string $profilePicDesc): self
    {
        $this->profilePicDesc = $profilePicDesc;

        return $this;
    }

    public function getParsedProfilePicDesc(): ?string
    {
        return $this->parsedProfilePicDesc;
    }

    public function setParsedProfilePicDesc(?string $parsedProfilePicDesc): self
    {
        $this->parsedProfilePicDesc = $parsedProfilePicDesc;

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
            $clientPet->setPet($this);
        }

        return $this;
    }

    public function removeClientPet(ClientPetEntity $clientPet): self
    {
        if ($this->clientPets->removeElement($clientPet)) {
            // set the owning side to null (unless already changed)
            if ($clientPet->getPet() === $this) {
                $clientPet->setPet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CabinetFormEntity>
     */
    public function getCabinetForms(): Collection
    {
        return $this->cabinetForms;
    }

    public function addCabinetForm(CabinetFormEntity $cabinetForm): self
    {
        if (!$this->cabinetForms->contains($cabinetForm)) {
            $this->cabinetForms[] = $cabinetForm;
            $cabinetForm->setPet($this);
        }

        return $this;
    }

    public function removeCabinetForm(CabinetFormEntity $cabinetForm): self
    {
        if ($this->cabinetForms->removeElement($cabinetForm)) {
            // set the owning side to null (unless already changed)
            if ($cabinetForm->getPet() === $this) {
                $cabinetForm->setPet(null);
            }
        }

        return $this;
    }

    public function getSpecies(): ?SpeciesEntity
    {
        return $this->species;
    }

    public function setSpecies(?SpeciesEntity $species): self
    {
        $this->species = $species;

        return $this;
    }

    public function getApproximateAge(): ?string
    {
        return $this->approximateAge;
    }

    public function setApproximateAge(?string $approximateAge): self
    {
        $this->approximateAge = $approximateAge;

        return $this;
    }

    public function isIsShelterPet(): ?bool
    {
        return $this->isShelterPet;
    }

    public function setIsShelterPet(?bool $isShelterPet): self
    {
        $this->isShelterPet = $isShelterPet;

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
            $shelterAdmission->setPet($this);
        }

        return $this;
    }

    public function removeShelterAdmission(ShelterAdmissionEntity $shelterAdmission): self
    {
        if ($this->shelterAdmissions->removeElement($shelterAdmission)) {
            // set the owning side to null (unless already changed)
            if ($shelterAdmission->getPet() === $this) {
                $shelterAdmission->setPet(null);
            }
        }

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

    public function isIsFixed(): ?bool
    {
        return $this->isFixed;
    }

    public function setIsFixed(?bool $isFixed): self
    {
        $this->isFixed = $isFixed;

        return $this;
    }

    public function isHasCruelFile(): ?bool
    {
        return $this->hasCruelFile;
    }

    public function setHasCruelFile(?bool $hasCruelFile): self
    {
        $this->hasCruelFile = $hasCruelFile;

        return $this;
    }

    /**
     * @return Collection<int, BehaviorRecordEntity>
     */
    public function getBehaviorRecords(): Collection
    {
        return $this->behaviorRecords;
    }

    public function addBehaviorRecord(BehaviorRecordEntity $behaviorRecord): self
    {
        if (!$this->behaviorRecords->contains($behaviorRecord)) {
            $this->behaviorRecords[] = $behaviorRecord;
            $behaviorRecord->setPet($this);
        }

        return $this;
    }

    public function removeBehaviorRecord(BehaviorRecordEntity $behaviorRecord): self
    {
        if ($this->behaviorRecords->removeElement($behaviorRecord)) {
            // set the owning side to null (unless already changed)
            if ($behaviorRecord->getPet() === $this) {
                $behaviorRecord->setPet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PetFileEntity>
     */
    public function getPetFiles(): Collection
    {
        return $this->petFiles;
    }

    public function addPetFile(PetFileEntity $petFile): self
    {
        if (!$this->petFiles->contains($petFile)) {
            $this->petFiles[] = $petFile;
            $petFile->setPet($this);
        }

        return $this;
    }

    public function removePetFile(PetFileEntity $petFile): self
    {
        if ($this->petFiles->removeElement($petFile)) {
            // set the owning side to null (unless already changed)
            if ($petFile->getPet() === $this) {
                $petFile->setPet(null);
            }
        }

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getStage(): ?StageEntity
    {
        return $this->stage;
    }

    public function setStage(?StageEntity $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    public function getCauseOfDeath(): ?string
    {
        return $this->causeOfDeath;
    }

    public function setCauseOfDeath(?string $causeOfDeath): self
    {
        $this->causeOfDeath = $causeOfDeath;

        return $this;
    }

    public function isIsDeceased(): ?bool
    {
        return $this->isDeceased;
    }

    public function setIsDeceased(?bool $isDeceased): self
    {
        $this->isDeceased = $isDeceased;

        return $this;
    }

    public function getBeforeFilePhotoDescription(): ?string
    {
        return $this->beforeFilePhotoDescription;
    }

    public function setBeforeFilePhotoDescription(?string $beforeFilePhotoDescription): self
    {
        $this->beforeFilePhotoDescription = $beforeFilePhotoDescription;

        return $this;
    }

    public function getParsedBeforeFilePhotoDescription(): ?string
    {
        return $this->parsedBeforeFilePhotoDescription;
    }

    public function setParsedBeforeFilePhotoDescription(?string $parsedBeforeFilePhotoDescription): self
    {
        $this->parsedBeforeFilePhotoDescription = $parsedBeforeFilePhotoDescription;

        return $this;
    }

    public function getAfterFilePhotoDescription(): ?string
    {
        return $this->afterFilePhotoDescription;
    }

    public function setAfterFilePhotoDescription(?string $afterFilePhotoDescription): self
    {
        $this->afterFilePhotoDescription = $afterFilePhotoDescription;

        return $this;
    }

    public function getParsedAfterFilePhotoDescription(): ?string
    {
        return $this->parsedAfterFilePhotoDescription;
    }

    public function setParsedAfterFilePhotoDescription(?string $parsedAfterFilePhotoDescription): self
    {
        $this->parsedAfterFilePhotoDescription = $parsedAfterFilePhotoDescription;

        return $this;
    }

    public function getParsedDescription(): ?string
    {
        return $this->parsedDescription;
    }

    public function setParsedDescription(?string $parsedDescription): self
    {
        $this->parsedDescription = $parsedDescription;

        return $this;
    }

    public function getFixedDate(): ?\DateTimeInterface
    {
        return $this->fixedDate;
    }

    public function setFixedDate(?\DateTimeInterface $fixedDate): self
    {
        $this->fixedDate = $fixedDate;

        return $this;
    }

    public function getDeathDate(): ?\DateTimeInterface
    {
        return $this->deathDate;
    }

    public function setDeathDate(?\DateTimeInterface $deathDate): self
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    /**
     * @return Collection<int, PetPhotoEntity>
     */
    public function getPetPhotos(): Collection
    {
        return $this->petPhotos;
    }

    public function addPetPhoto(PetPhotoEntity $petPhoto): self
    {
        if (!$this->petPhotos->contains($petPhoto)) {
            $this->petPhotos[] = $petPhoto;
            $petPhoto->setPet($this);
        }

        return $this;
    }

    public function removePetPhoto(PetPhotoEntity $petPhoto): self
    {
        if ($this->petPhotos->removeElement($petPhoto)) {
            // set the owning side to null (unless already changed)
            if ($petPhoto->getPet() === $this) {
                $petPhoto->setPet(null);
            }
        }

        return $this;
    }



    
  
}
