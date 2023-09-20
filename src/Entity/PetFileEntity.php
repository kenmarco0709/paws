<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetFileRepository")
 * @ORM\Table(name="pet_file")
 * @ORM\HasLifecycleCallbacks()
 */

class PetFileEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    protected $description;

     /**
     * @ORM\Column(name="parsed_description", type="string", nullable=true)
     */
    protected $parsedDescription;

     /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="petFiles")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;
    
    public function __construct()
    {
        
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Pet Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/


      /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return PetFileEntity
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


    /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedDescription;
        if(!empty($this->description) && file_exists($file)) unlink($file);
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

        $parsedDesc = $this->parsedDescription;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }       
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
