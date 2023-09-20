<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRepository")
 * @ORM\Table(name="facility")
 * @ORM\HasLifecycleCallbacks()
 */

class FacilityEntity extends BaseEntity
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
     * @ORM\Column(name="capacity", type="integer", nullable=true)
     */
    protected $capacity;

    /**
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="facilities")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\ManyToOne(targetEntity="SpeciesEntity", inversedBy="facilities")
     * @ORM\JoinColumn(name="species_id", referencedColumnName="id", nullable=true)
     */
    protected $species;

    /**
     * @ORM\OneToMany(targetEntity="ShelterAdmissionEntity", mappedBy="facility", cascade={"remove"})
     * @ORM\OrderBy({"id" = "DESC"})
     */
    protected $shelterAdmissions;


    public function __construct($data = null)
    {
        $this->shelterAdmissions = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Facility Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

      /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FacilityEntity
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

    public function getBranch(): ?BranchEntity
    {
        return $this->branch;
    }

    public function setBranch(?BranchEntity $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    public function getSpecies(): ?SpeciesEntity
    {
        return $this->species;
    }

    public function setSpecies(?SpeciesEntity $species): self
    {
        $this->species = $species;

        return $this;
    }

    /**
     * @return Collection<int, ShelterAdmissionEntity>
     */
    public function getShelterAdmissions(): Collection
    {
        return $this->shelterAdmissions;
    }

    public function addShelterAdmission(ShelterAdmissionEntity $shelterAdmission): self
    {
        if (!$this->shelterAdmissions->contains($shelterAdmission)) {
            $this->shelterAdmissions[] = $shelterAdmission;
            $shelterAdmission->setFacility($this);
        }

        return $this;
    }

    public function removeShelterAdmission(ShelterAdmissionEntity $shelterAdmission): self
    {
        if ($this->shelterAdmissions->removeElement($shelterAdmission)) {
            // set the owning side to null (unless already changed)
            if ($shelterAdmission->getFacility() === $this) {
                $shelterAdmission->setFacility(null);
            }
        }

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }


}
