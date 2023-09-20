<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpeciesRepository")
 * @ORM\Table(name="species")
 * @ORM\HasLifecycleCallbacks()
 */

class SpeciesEntity extends BaseEntity
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
     * @ORM\ManyToOne(targetEntity="BranchEntity", inversedBy="specieses")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id", nullable=true)
     */
    protected $branch;

    /**
     * @ORM\OneToMany(targetEntity="PetEntity", mappedBy="species", cascade={"remove"})
     */
    protected $pets;

    /**
     * @ORM\OneToMany(targetEntity="FacilityEntity", mappedBy="species", cascade={"remove"})
     */
    protected $facilities;

    public function __construct($data = null)
    {
        $this->pets = new ArrayCollection();
        $this->facilities = new ArrayCollection();
    }

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Species Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/


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
     * @return Collection<int, PetEntity>
     */
    public function getPets(): Collection
    {
        return $this->pets;
    }

    public function addPet(PetEntity $pet): self
    {
        if (!$this->pets->contains($pet)) {
            $this->pets[] = $pet;
            $pet->setSpecies($this);
        }

        return $this;
    }

    public function removePet(PetEntity $pet): self
    {
        if ($this->pets->removeElement($pet)) {
            // set the owning side to null (unless already changed)
            if ($pet->getSpecies() === $this) {
                $pet->setSpecies(null);
            }
        }

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

    /**
     * @return Collection<int, FacilityEntity>
     */
    public function getFacilities(): Collection
    {
        return $this->facilities;
    }

    public function addFacility(FacilityEntity $facility): self
    {
        if (!$this->facilities->contains($facility)) {
            $this->facilities[] = $facility;
            $facility->setSpecies($this);
        }

        return $this;
    }

    public function removeFacility(FacilityEntity $facility): self
    {
        if ($this->facilities->removeElement($facility)) {
            // set the owning side to null (unless already changed)
            if ($facility->getSpecies() === $this) {
                $facility->setSpecies(null);
            }
        }

        return $this;
    }


}
