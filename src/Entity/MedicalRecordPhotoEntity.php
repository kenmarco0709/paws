<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MedicalRecordPhotoRepository")
 * @ORM\Table(name="medical_record_photo")
 * @ORM\HasLifecycleCallbacks()
 */

class MedicalRecordPhotoEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="file_desc", type="string", nullable=true)
     */
    protected $fileDesc;
    
     /**
     * @ORM\Column(name="parsed_file_desc", type="string", nullable=true)
     */
    protected $parsedFileDesc;

    /**
     * @ORM\ManyToOne(targetEntity="MedicalRecordEntity", inversedBy="medicalRecordLaboratories", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="medical_record_id", referencedColumnName="id")
     */
    protected $medicalRecord;

    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedFileDesc;
        if(!empty($this->vaxCardDesc) && file_exists($file)) unlink($file);
    }

        /**
     * Get uploadDir
     *
     * @return string
     */
    public function getUploadDir() {

        return '/uploads/file/medical/photo';
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

        $parsedDesc = $this->parsedFileDesc;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }

    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Medicalrecordtype Defined Setters and Getters										  */
    /*--------------------------------------------------------------------------------------------------------*/


    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFileDesc(): ?string
    {
        return $this->fileDesc;
    }

    public function setFileDesc(?string $fileDesc): self
    {
        $this->fileDesc = $fileDesc;

        return $this;
    }

    public function getParsedFileDesc(): ?string
    {
        return $this->parsedFileDesc;
    }

    public function setParsedFileDesc(?string $parsedFileDesc): self
    {
        $this->parsedFileDesc = $parsedFileDesc;

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
