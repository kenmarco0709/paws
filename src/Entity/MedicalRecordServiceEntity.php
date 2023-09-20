<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MedicalRecordServiceRepository")
 * @ORM\Table(name="medical_record_service")
 * @ORM\HasLifecycleCallbacks()
 */

class MedicalRecordServiceEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    protected $remarks;

    /**
     * @ORM\ManyToOne(targetEntity="MedicalRecordEntity", inversedBy="medicalRecordServices")
     * @ORM\JoinColumn(name="medical_record_id", referencedColumnName="id")
     */
    protected $medicalRecord;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceEntity", inversedBy="medicalRecordServices")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    protected $service;



  

   

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

    public function getMedicalRecord(): ?MedicalRecordEntity
    {
        return $this->medicalRecord;
    }

    public function setMedicalRecord(?MedicalRecordEntity $medicalRecord): self
    {
        $this->medicalRecord = $medicalRecord;

        return $this;
    }

    public function getService(): ?ServiceEntity
    {
        return $this->service;
    }

    public function setService(?ServiceEntity $service): self
    {
        $this->service = $service;

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

    

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }



    

   
   
}
