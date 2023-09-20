<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VetScheduleRepository")
 * @ORM\Table(name="vet_schedule")
 * @ORM\HasLifecycleCallbacks()
 */

class VetScheduleEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="schedule_type", type="string")
     */
    protected $scheduleType;

    /**
     * @ORM\Column(name="schedule_date_from", type="datetime", nullable=true)
     */
    protected $scheduleDateFrom;
    
    /**
     * @ORM\Column(name="schedule_date_to", type="datetime", nullable=true)
     */
    protected $scheduleDateTo;

    /**
     * @ORM\Column(name="schedule_time_from", type="string", nullable=true)
     */
    protected $scheduleTimeFrom;
    
    /**
     * @ORM\Column(name="schedule_time_to", type="string", nullable=true)
     */
    protected $scheduleTimeTo;
    
    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="vetVetSchedules")
     * @ORM\JoinColumn(name="vet_id", referencedColumnName="id", nullable=true)
     */
    protected $vet;

     /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="vetSchedules")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    public function __construct()
    {
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Admission Defined Setters and Getters												   */
    /*--------------------------------------------------------------------------------------------------------*/

   /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return VetSchedule
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

    public function getScheduleType(): ?string
    {
        return $this->scheduleType;
    }

    public function setScheduleType(string $scheduleType): self
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }

    public function getScheduleDateFrom(): ?\DateTimeInterface
    {
        return $this->scheduleDateFrom;
    }

    public function setScheduleDateFrom(?\DateTimeInterface $scheduleDateFrom): self
    {
        $this->scheduleDateFrom = $scheduleDateFrom;

        return $this;
    }

    public function getScheduleDateTo(): ?\DateTimeInterface
    {
        return $this->scheduleDateTo;
    }

    public function setScheduleDateTo(?\DateTimeInterface $scheduleDateTo): self
    {
        $this->scheduleDateTo = $scheduleDateTo;

        return $this;
    }

    public function getScheduleTimeFrom(): ?string
    {
        return $this->scheduleTimeFrom;
    }

    public function setScheduleTimeFrom(?string $scheduleTimeFrom): self
    {
        $this->scheduleTimeFrom = $scheduleTimeFrom;

        return $this;
    }

    public function getScheduleTimeTo(): ?string
    {
        return $this->scheduleTimeTo;
    }

    public function setScheduleTimeTo(?string $scheduleTimeTo): self
    {
        $this->scheduleTimeTo = $scheduleTimeTo;

        return $this;
    }

    public function getVet(): ?UserEntity
    {
        return $this->vet;
    }

    public function setVet(?UserEntity $vet): self
    {
        $this->vet = $vet;

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

   
    
}
