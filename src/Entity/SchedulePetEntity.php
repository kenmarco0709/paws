<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SchedulePetRepository")
 * @ORM\Table(name="schedule_pet")
 * @ORM\HasLifecycleCallbacks()
 */

class SchedulePetEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ScheduleEntity", inversedBy="schedulePets", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id", nullable=true)
     */
    protected $schedule;
    
    /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="schedulePets")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;



  

   

    public function __construct()
    {
        $this->medicalRecords = new ArrayCollection();
    }
    
    /*--------------------------------------------------------------------------------------------------------*/
    /*					Schedule Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return SchedulePetEntity
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

    public function getSchedule(): ?ScheduleEntity
    {
        return $this->schedule;
    }

    public function setSchedule(?ScheduleEntity $schedule): self
    {
        $this->schedule = $schedule;

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
