<?php

namespace App\Entity;

use App\Entity\Base\BaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientPetRepository")
 * @ORM\Table(name="client_pet")
 * @ORM\HasLifecycleCallbacks()
 */
class ClientPetEntity extends BaseEntity
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ClientEntity", inversedBy="clientPets")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="PetEntity", inversedBy="clientPets")
     * @ORM\JoinColumn(name="pet_id", referencedColumnName="id", nullable=true)
     */
    protected $pet;
    

    public function __construct()
    {
    }

    
     /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return ClientPetEntity
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

    /*--------------------------------------------------------------------------------------------------------*/
    /*					Client Defined Setters and Getters													  */
    /*--------------------------------------------------------------------------------------------------------*/

    

    /*--------------------------------------------------------------------------------------------------------*/
    /*					    Defined Setters and Getters													      */
    /*--------------------------------------------------------------------------------------------------------*/

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getClient(): ?ClientEntity
    {
        return $this->client;
    }

    public function setClient(?ClientEntity $client): self
    {
        $this->client = $client;

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
