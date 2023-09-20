<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CabinetFormRepository")
 * @ORM\Table(name="cabinet_form")
 * @ORM\HasLifecycleCallbacks()
 */

class CabinetFormEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="form_type", type="string")
     */
    protected $formType;

    /**
     * @ORM\Column(name="file_desc", type="string", nullable=true)
     */
    protected $fileDesc;
    
     /**
     * @ORM\Column(name="parsed_file_desc", type="string", nullable=true)
     */
    protected $parsedFiledDesc;

     /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="cabinetForms")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;

    
     /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedFileDesc;
        if(!empty($this->fileDesc) && file_exists($file)) unlink($file);
    }

        /**
     * Get uploadDir
     *
     * @return string
     */
    public function getUploadDir() {

        return '/uploads/file/form_cabinet';
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

        $parsedDesc = $this->parsedFiledDesc;
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

    public function getFormType(): ?string
    {
        return $this->formType;
    }

    public function setFormType(string $formType): self
    {
        $this->formType = $formType;

        return $this;
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

    public function getParsedFiledDesc(): ?string
    {
        return $this->parsedFiledDesc;
    }

    public function setParsedFiledDesc(?string $parsedFiledDesc): self
    {
        $this->parsedFiledDesc = $parsedFiledDesc;

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


  
}
