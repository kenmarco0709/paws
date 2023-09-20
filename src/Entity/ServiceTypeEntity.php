<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceTypeRepository")
 * @ORM\Table(name="service_type")
 * @ORM\HasLifecycleCallbacks()
 */

class ServiceTypeEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="code", type="string")
     */
    protected $code;
    
    /**
     * @ORM\Column(name="description", type="string")
     */
    protected $description;

      /**
     * @ORM\OneToMany(targetEntity="ServiceEntity", mappedBy="serviceType", cascade={"remove"})
     */
    protected $services;

    public function __construct($data = null)
    {
        $this->pets = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					ServiceType Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

 /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ServiceEntity
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
     * @return Collection<int, ServiceEntity>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(ServiceEntity $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setServiceType($this);
        }

        return $this;
    }

    public function removeService(ServiceEntity $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getServiceType() === $this) {
                $service->setServiceType(null);
            }
        }

        return $this;
    }



    

    

  

}
