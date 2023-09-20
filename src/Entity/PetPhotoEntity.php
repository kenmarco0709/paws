<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PetPhotoRepository")
 * @ORM\Table(name="pet_photo")
 * @ORM\HasLifecycleCallbacks()
 */

class PetPhotoEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(name="before_description", type="string", nullable=true)
     */
    protected $beforeDescription;

     /**
     * @ORM\Column(name="parsed_before_description", type="string", nullable=true)
     */
    protected $parsedBeforeDescription;

    /**
     * @ORM\Column(name="after_description", type="string", nullable=true)
     */
    protected $afterDescription;

     /**
     * @ORM\Column(name="parsed_after_description", type="string", nullable=true)
     */
    protected $parsedAfterDescription;

    /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

     /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="petPhotos")
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
     * @return PetPhotoEntity
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
     * Remove the photo from the disk
     *
     * @ORM\PreRemove
     */
    public function removeBeforePhoto() {

        $photo = $this->getUploadRootDir() . '/' . $this->parsedBeforeDescription;
        if(!empty($this->beforeDescription) && photo_exists($photo)) unlink($photo);
    }

    /**
     * Remove the photo from the disk
     *
     * @ORM\PreRemove
     */
    public function removeAfterPhoto() {

        $photo = $this->getUploadRootDir() . '/' . $this->parsedAfterDescription;
        if(!empty($this->beforeDescription) && photo_exists($photo)) unlink($photo);
    }

        /**
     * Get uploadDir
     *
     * @return string
     */
    public function getUploadDir() {

        return '/uploads/photo';
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
     * get beforePhotoWebPath
     *
     * @return string
     */
    public function getBeforePhotoWebPath() {

        $parsedDesc = $this->parsedBeforeDescription;
        $photo = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }       
    }

        /**
     * get afterPhotoWebPath
     *
     * @return string
     */
    public function getAfterPhotoWebPath() {

        $parsedDesc = $this->parsedAfterDescription;
        $photo = $this->getUploadRootDir() . '/' . $parsedDesc;
     
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

    public function getBeforeDescription(): ?string
    {
        return $this->beforeDescription;
    }

    public function setBeforeDescription(?string $beforeDescription): self
    {
        $this->beforeDescription = $beforeDescription;

        return $this;
    }

    public function getParsedBeforeDescription(): ?string
    {
        return $this->parsedBeforeDescription;
    }

    public function setParsedBeforeDescription(?string $parsedBeforeDescription): self
    {
        $this->parsedBeforeDescription = $parsedBeforeDescription;

        return $this;
    }

    public function getAfterDescription(): ?string
    {
        return $this->afterDescription;
    }

    public function setAfterDescription(?string $afterDescription): self
    {
        $this->afterDescription = $afterDescription;

        return $this;
    }

    public function getParsedAfterDescription(): ?string
    {
        return $this->parsedAfterDescription;
    }

    public function setParsedAfterDescription(?string $parsedAfterDescription): self
    {
        $this->parsedAfterDescription = $parsedAfterDescription;

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

}
