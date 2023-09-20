<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BehaviorRecordRepository")
 * @ORM\Table(name="behavior_record")
 * @ORM\HasLifecycleCallbacks()
 */

class BehaviorRecordEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="handler", type="string", nullable=true))
     */
    protected $handler;

    /**
     * @ORM\Column(name="area", type="string", nullable=true))
     */
    protected $area;
    
    /**
     * @ORM\Column(name="remarks", type="string", nullable=true))
     */
    protected $remarks;
    
    /**
     * @ORM\Column(name="behavior_record_date", type="datetime")
     */
    protected $behaviorRecordDate;

    /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="behaviorRecords")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;


   

    public function __construct()
    {
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

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setHandler(?string $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

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

    public function getBehaviorRecordDate(): ?\DateTimeInterface
    {
        return $this->behaviorRecordDate;
    }

    public function setBehaviorRecordDate(\DateTimeInterface $behaviorRecordDate): self
    {
        $this->behaviorRecordDate = $behaviorRecordDate;

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
