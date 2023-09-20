<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MedicalRecordRepository")
 * @ORM\Table(name="medical_record")
 * @ORM\HasLifecycleCallbacks()
 */

class MedicalRecordEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="weight", type="string", nullable=true)
     */
    protected $weight;

    /**
     * @ORM\Column(name="height", type="string", nullable=true)
     */
    protected $height;
     /**
     * @ORM\Column(name="temperature", type="string", nullable=true)
     */
    protected $temperature;

    /**
     * @ORM\Column(name="primary_complain", type="string", nullable=true)
     */
    protected $primaryComplain;

    /**
     * @ORM\Column(name="medical_interpretation", type="string", nullable=true)
     */
    protected $medicalInterPretation;

    /**
     * @ORM\Column(name="diagnosis", type="text", nullable=true)
     */
    protected $diagnosis;

     /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

     /**
     * @ORM\Column(name="prescription", type="text", nullable=true)
     */
    protected $prescription;

    /**
     * @ORM\Column(name="vaccine_lot_no", type="string", nullable=true))
     */
    protected $vaccineLotNo;

    /**
     * @ORM\Column(name="vaccine_batch_no", type="string", nullable=true))
     */
    protected $vaccineBatchNo;

    /**
     * @ORM\Column(name="vaccine_dute_date", type="datetime", nullable=true)
     */
    protected $vaccineDueDate;

        /**
     * @ORM\Column(name="vaccine_expiration_date", type="datetime", nullable=true)
     */
    protected $vaccineExpirationDate;

    /**
     * @ORM\Column(name="returned_date", type="datetime", nullable=true)
     */
    protected $returnedDate;

     /**
     * @ORM\Column(name="vax_card_desc", type="string", nullable=true)
     */
    protected $vaxCardDesc;
    
     /**
     * @ORM\Column(name="parsed_vax_card", type="string", nullable=true)
     */
    protected $parsedVaxCardDesc;

      /**
     * @ORM\ManyToOne(targetEntity="ShelterAdmissionEntity", inversedBy="medicalRecords")
     * @ORM\JoinColumn(name="shelter_admission_id", referencedColumnName="id", nullable=true)
     */
    protected $shelterAdmission;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionTypeEntity", inversedBy="medicalRecords")
     * @ORM\JoinColumn(name="admission_type_id", referencedColumnName="id", nullable=true)
     */
    protected $admissionType;

    /**
     * @ORM\ManyToOne(targetEntity="AdmissionPetEntity", inversedBy="medicalRecords")
     * @ORM\JoinColumn(name="admission_pet_id", referencedColumnName="id", nullable=true)
     */
    protected $admissionPet;

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="shelterMedicalRecords")
     * @ORM\JoinColumn(name="attending_vet_id", referencedColumnName="id", nullable=true)
     */
    protected $attendingVet;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordServiceEntity", mappedBy="medicalRecord", cascade={"remove"})
     */
    protected $medicalRecordServices;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordItemEntity", mappedBy="medicalRecord", cascade={"remove"})
     */
    protected $medicalRecordItems;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordLaboratoryEntity", mappedBy="medicalRecord", cascade={"remove", "persist"})
     */
    protected $medicalRecordLaboratories;

    
    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordPhotoEntity", mappedBy="medicalRecord", cascade={"remove", "persist"})
     */
    protected $medicalRecordPhotos;

      /**
     * @ORM\ManyToOne(targetEntity="MedicalRecordEntity", inversedBy="medicalRecords")
     * @ORM\JoinColumn(name="medical_record_id", referencedColumnName="id", nullable=true)
     */
    protected $medicalRecordHistory;

    /**
     * @ORM\OneToMany(targetEntity="MedicalRecordEntity", mappedBy="medicalRecordHistory", cascade={"remove"})
     */
    protected $medicalRecords;

        /**
     * @ORM\OneToMany(targetEntity="MedicalRecordPrescriptionInventoryItemEntity", mappedBy="medicalRecord", cascade={"remove"})
     */
    protected $medicalRecordPrescriptionInventoryItems;

     /**
     * @ORM\OneToMany(targetEntity="ScheduleEntity", mappedBy="medicalRecord", cascade={"remove"})
     */
    protected $schedules;


  
    public function __construct($data = null)
    {
        $this->medicalRecordServices = new ArrayCollection();
        $this->medicalRecordItems = new ArrayCollection();
        $this->medicalRecordLaboratories = new ArrayCollection();
        $this->medicalRecords = new ArrayCollection();
        $this->medicalRecordPrescriptionInventoryItems = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->medicalRecordPhotos = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Medicalrecordtype Defined Setters and Getters										  */
    /*--------------------------------------------------------------------------------------------------------*/


            /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedVaxCardDesc;
        if(!empty($this->vaxCardDesc) && file_exists($file)) unlink($file);
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

        $parsedDesc = $this->parsedVaxCardDesc;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }

    /**
     * Get medicalRecordScheduleIdsInArray
     *
     *
     * @return array
     */
    public function getMedicalRecordScheduleIdsInArray()
    {

        $ids= [];

        foreach($this->getSchedules() as $k => $schedule){

            $ids[$k] = $schedule->getId();
        }

        return $ids;
    }


     /**
     * Set medicalRecordServiceInArray
     *
     *
     * @return array
     */
    public function medicalRecordServiceInArray()
    {

        $services = [];

        foreach($this->getMedicalRecordItems() as $k => $item){

            $services[$k] = $item->getService()->getDescription();
            
        }

        return $services;
    }

     /**
     * Set medicalRecordItemIdsArray
     *
     *
     * @return array
     */
    public function medicalRecordItemIdsArray()
    {

        $ids = [];

        foreach($this->getMedicalRecordItems() as $k => $ii){

            $ids[$k] = $ii->getInventoryItem()->getId();
            
        }

        return $ids;
    }

     /**
     * Set medicalRecordServiceIdsArray
     *
     *
     * @return array
     */
    public function medicalRecordServiceIdsArray()
    {

        $services = [];

        foreach($this->getMedicalRecordServices() as $k => $servicess){
            if(!$servicess->getIsDeleted()){

                $services[$k] = $servicess->getService()->getId();
            }
            
        }

        return $services;
    }

    /**
     * Set medicalRecordPrescriptionItemIdsArray
     *
     *
     * @return array
     */
    public function medicalRecordPrescriptionItemIdsArray()
    {

        $ids = [];

        foreach($this->getMedicalRecordPrescriptionInventoryItems() as $k => $ii){
            if(!$ii->getIsDeleted()){
                $ids[$k] = $ii->getInventoryItem()->getId();

            }
        }

        return $ids;
    }

    /**
     * Get medicalRecordLaboratoryIds
     *
     *
     * @return array
     */
    public function medicalRecordLaboratoryIds()
    {

        $ids = [];

        foreach($this->getMedicalRecordLaboratories() as $k => $ii){
            if(!$ii->getIsDeleted()){
                $ids[$k] = $ii->getIdEncoded();
            }
        }
        return $ids;
    }

    /**
     * Get totalServiceExpense
     *
     *
     * @return int
     */
    public function getTotalServiceExpense()
    {

        $totalServiceExpense = 0;

        foreach($this->getMedicalRecordServices() as $k => $ii){
            if(!$ii->getIsDeleted()){
                $totalServiceExpense += $ii->getService()->getPrice();
            }
        }
        return $totalServiceExpense;
    }

    /**
     * Get totalItemExpense
     *
     *
     * @return int
     */
    public function getTotalItemExpense()
    {

        $totalItemExpense = 0;

        foreach($this->getMedicalRecordItems() as $k => $ii){
            if(!$ii->getIsDeleted()){
                $totalItemExpense += $ii->getInventoryItem()->getBuyingPrice() * $ii->getQuantity();
            }
        }
        return $totalItemExpense;
    }


    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getTemperature(): ?string
    {
        return $this->temperature;
    }

    public function setTemperature(?string $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getPrimaryComplain(): ?string
    {
        return $this->primaryComplain;
    }

    public function setPrimaryComplain(?string $primaryComplain): self
    {
        $this->primaryComplain = $primaryComplain;

        return $this;
    }

    public function getMedicalInterPretation(): ?string
    {
        return $this->medicalInterPretation;
    }

    public function setMedicalInterPretation(?string $medicalInterPretation): self
    {
        $this->medicalInterPretation = $medicalInterPretation;

        return $this;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(?string $diagnosis): self
    {
        $this->diagnosis = $diagnosis;

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

    public function getPrescription(): ?string
    {
        return $this->prescription;
    }

    public function setPrescription(?string $prescription): self
    {
        $this->prescription = $prescription;

        return $this;
    }

    public function getVaccineDueDate(): ?\DateTimeInterface
    {
        return $this->vaccineDueDate;
    }

    public function setVaccineDueDate(?\DateTimeInterface $vaccineDueDate): self
    {
        $this->vaccineDueDate = $vaccineDueDate;

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

    public function getVaxCardDesc(): ?string
    {
        return $this->vaxCardDesc;
    }

    public function setVaxCardDesc(?string $vaxCardDesc): self
    {
        $this->vaxCardDesc = $vaxCardDesc;

        return $this;
    }

    public function getParsedVaxCardDesc(): ?string
    {
        return $this->parsedVaxCardDesc;
    }

    public function setParsedVaxCardDesc(?string $parsedVaxCardDesc): self
    {
        $this->parsedVaxCardDesc = $parsedVaxCardDesc;

        return $this;
    }

    public function getAdmissionPet(): ?AdmissionPetEntity
    {
        return $this->admissionPet;
    }

    public function setAdmissionPet(?AdmissionPetEntity $admissionPet): self
    {
        $this->admissionPet = $admissionPet;

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
            $medicalRecordService->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeMedicalRecordService(MedicalRecordServiceEntity $medicalRecordService): self
    {
        if ($this->medicalRecordServices->removeElement($medicalRecordService)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordService->getMedicalRecord() === $this) {
                $medicalRecordService->setMedicalRecord(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordItemEntity>
     */
    public function getMedicalRecordItems(): Collection
    {
        return $this->medicalRecordItems;
    }

    public function addMedicalRecordItem(MedicalRecordItemEntity $medicalRecordItem): self
    {
        if (!$this->medicalRecordItems->contains($medicalRecordItem)) {
            $this->medicalRecordItems[] = $medicalRecordItem;
            $medicalRecordItem->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeMedicalRecordItem(MedicalRecordItemEntity $medicalRecordItem): self
    {
        if ($this->medicalRecordItems->removeElement($medicalRecordItem)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordItem->getMedicalRecord() === $this) {
                $medicalRecordItem->setMedicalRecord(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordLaboratoryEntity>
     */
    public function getMedicalRecordLaboratories(): Collection
    {
        return $this->medicalRecordLaboratories;
    }

    public function addMedicalRecordLaboratory(MedicalRecordLaboratoryEntity $medicalRecordLaboratory): self
    {
        if (!$this->medicalRecordLaboratories->contains($medicalRecordLaboratory)) {
            $this->medicalRecordLaboratories[] = $medicalRecordLaboratory;
            $medicalRecordLaboratory->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeMedicalRecordLaboratory(MedicalRecordLaboratoryEntity $medicalRecordLaboratory): self
    {
        if ($this->medicalRecordLaboratories->removeElement($medicalRecordLaboratory)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordLaboratory->getMedicalRecord() === $this) {
                $medicalRecordLaboratory->setMedicalRecord(null);
            }
        }

        return $this;
    }

    public function getMedicalRecordHistory(): ?self
    {
        return $this->medicalRecordHistory;
    }

    public function setMedicalRecordHistory(?self $medicalRecordHistory): self
    {
        $this->medicalRecordHistory = $medicalRecordHistory;

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
            $medicalRecord->setMedicalRecordHistory($this);
        }

        return $this;
    }

    public function removeMedicalRecord(MedicalRecordEntity $medicalRecord): self
    {
        if ($this->medicalRecords->removeElement($medicalRecord)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecord->getMedicalRecordHistory() === $this) {
                $medicalRecord->setMedicalRecordHistory(null);
            }
        }

        return $this;
    }

    

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordPrescriptionInventoryItemEntity>
     */
    public function getMedicalRecordPrescriptionInventoryItems(): Collection
    {
        return $this->medicalRecordPrescriptionInventoryItems;
    }

    public function addMedicalRecordPrescriptionInventoryItem(MedicalRecordPrescriptionInventoryItemEntity $medicalRecordPrescriptionInventoryItem): self
    {
        if (!$this->medicalRecordPrescriptionInventoryItems->contains($medicalRecordPrescriptionInventoryItem)) {
            $this->medicalRecordPrescriptionInventoryItems[] = $medicalRecordPrescriptionInventoryItem;
            $medicalRecordPrescriptionInventoryItem->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeMedicalRecordPrescriptionInventoryItem(MedicalRecordPrescriptionInventoryItemEntity $medicalRecordPrescriptionInventoryItem): self
    {
        if ($this->medicalRecordPrescriptionInventoryItems->removeElement($medicalRecordPrescriptionInventoryItem)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordPrescriptionInventoryItem->getMedicalRecord() === $this) {
                $medicalRecordPrescriptionInventoryItem->setMedicalRecord(null);
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
            $schedule->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeSchedule(ScheduleEntity $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getMedicalRecord() === $this) {
                $schedule->setMedicalRecord(null);
            }
        }

        return $this;
    }

    public function getShelterAdmission(): ?ShelterAdmissionEntity
    {
        return $this->shelterAdmission;
    }

    public function setShelterAdmission(?ShelterAdmissionEntity $shelterAdmission): self
    {
        $this->shelterAdmission = $shelterAdmission;

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

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

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

    public function getVaccineExpirationDate(): ?\DateTimeInterface
    {
        return $this->vaccineExpirationDate;
    }

    public function setVaccineExpirationDate(?\DateTimeInterface $vaccineExpirationDate): self
    {
        $this->vaccineExpirationDate = $vaccineExpirationDate;

        return $this;
    }

    /**
     * @return Collection<int, MedicalRecordPhotoEntity>
     */
    public function getMedicalRecordPhotos(): Collection
    {
        return $this->medicalRecordPhotos;
    }

    public function addMedicalRecordPhoto(MedicalRecordPhotoEntity $medicalRecordPhoto): self
    {
        if (!$this->medicalRecordPhotos->contains($medicalRecordPhoto)) {
            $this->medicalRecordPhotos[] = $medicalRecordPhoto;
            $medicalRecordPhoto->setMedicalRecord($this);
        }

        return $this;
    }

    public function removeMedicalRecordPhoto(MedicalRecordPhotoEntity $medicalRecordPhoto): self
    {
        if ($this->medicalRecordPhotos->removeElement($medicalRecordPhoto)) {
            // set the owning side to null (unless already changed)
            if ($medicalRecordPhoto->getMedicalRecord() === $this) {
                $medicalRecordPhoto->setMedicalRecord(null);
            }
        }

        return $this;
    }



    


}
