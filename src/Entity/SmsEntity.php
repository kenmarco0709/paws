<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsRepository")
 * @ORM\Table(name="sms")
 * @ORM\HasLifecycleCallbacks()
 */

class SmsEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="sms_type", type="string")
     */
    protected $smsType;
    
    /**
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="CompanyEntity", inversedBy="smss")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;


    public function __construct($data = null)
    {
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Sms Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

            /**
     * Remove the file from the disk
     *
     * @ORM\PreRemove
     */
    public function removeFile() {

        $file = $this->getUploadRootDir() . '/' . $this->parsedLogoDesc;
        if(!empty($this->logoDesc) && file_exists($file)) unlink($file);
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

        $parsedDesc = $this->parsedLogoDesc;
        $file = $this->getUploadRootDir() . '/' . $parsedDesc;
     
        if(!empty($parsedDesc) ) {
            return   $this->getUploadDir() . '/' . $parsedDesc;
        } else {
            return '';
        }

       
    }


    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return SmsEntity
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
     * @return Collection<int, UserEntity>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserEntity $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setSms($this);
        }

        return $this;
    }

    public function removeUser(UserEntity $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getSms() === $this) {
                $user->setSms(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BranchEntity>
     */
    public function getBranches(): Collection
    {
        return $this->branches;
    }

    public function addBranch(BranchEntity $branch): self
    {
        if (!$this->branches->contains($branch)) {
            $this->branches[] = $branch;
            $branch->setSms($this);
        }

        return $this;
    }

    public function removeBranch(BranchEntity $branch): self
    {
        if ($this->branches->removeElement($branch)) {
            // set the owning side to null (unless already changed)
            if ($branch->getSms() === $this) {
                $branch->setSms(null);
            }
        }

        return $this;
    }



    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getContactNo(): ?string
    {
        return $this->contactNo;
    }

    public function setContactNo(?string $contactNo): self
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLogoDesc(): ?string
    {
        return $this->logoDesc;
    }

    public function setLogoDesc(?string $logoDesc): self
    {
        $this->logoDesc = $logoDesc;

        return $this;
    }

    public function getParsedLogoDesc(): ?string
    {
        return $this->parsedLogoDesc;
    }

    public function setParsedLogoDesc(?string $parsedLogoDesc): self
    {
        $this->parsedLogoDesc = $parsedLogoDesc;

        return $this;
    }

    public function getSmsType(): ?string
    {
        return $this->smsType;
    }

    public function setSmsType(string $smsType): self
    {
        $this->smsType = $smsType;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCompany(): ?CompanyEntity
    {
        return $this->company;
    }

    public function setCompany(?CompanyEntity $company): self
    {
        $this->company = $company;

        return $this;
    }



    

    



}
