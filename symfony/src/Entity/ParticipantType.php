<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "S_participant_types")]
class ParticipantType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'participant_type', targetEntity: EventAccreditation::class, orphanRemoval: true)]
    private Collection $eventAccreditations;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function __construct()
    {
            ;
        // Assurez-vous d'initialiser d'autres collections si elles existent
    }

    // 🚩 AJOUTER LES MÉTHODES D'ACCÈS 🚩

    /**
     * @return Collection<int, EventAccreditation>
     */
    public function getEventAccreditations(): Collection
    {
        return $this->eventAccreditations;
    }

    public function addEventAccreditation(EventAccreditation $eventAccreditation): static
    {
        if (!$this->eventAccreditations->contains($eventAccreditation)) {
            $this->eventAccreditations->add($eventAccreditation);
            $eventAccreditation->setParticipantType($this);
        }

        return $this;
    }

    public function removeEventAccreditation(EventAccreditation $eventAccreditation): static
    {
        if ($this->eventAccreditations->removeElement($eventAccreditation)) {
            // set the owning side to null (unless already changed)
            if ($eventAccreditation->getParticipantType() === $this) {
                $eventAccreditation->setParticipantType(null);
            }
        }

        return $this;
    }
}