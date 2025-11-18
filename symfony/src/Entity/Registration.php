<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

// #[ORM\Entity(repositoryClass: \App\Repository\RegistrationRepository::class)]
#[ORM\Table(name: "S_registrations")]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $participant_id = null;

    #[ORM\Column]
    private ?int $program_id = null;

    #[ORM\Column]
    private ?int $created_by = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updated_at = null;

    /* ========== GETTERS & SETTERS ========== */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipantId(): ?int
    {
        return $this->participant_id;
    }

    public function setParticipantId(int $participant_id): static
    {
        $this->participant_id = $participant_id;
        return $this;
    }

    public function getProgramId(): ?int
    {
        return $this->program_id;
    }

    public function setProgramId(int $program_id): static
    {
        $this->program_id = $program_id;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(int $created_by): static
    {
        $this->created_by = $created_by;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
