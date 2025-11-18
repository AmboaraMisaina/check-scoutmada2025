<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "registration_followups")]
class RegistrationFollowup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $participant_id = null;

    #[ORM\Column]
    private ?int $step_id = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?int $created_by = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $created_at = null;


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

    public function getStepId(): ?int
    {
        return $this->step_id;
    }

    public function setStepId(int $step_id): static
    {
        $this->step_id = $step_id;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

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

}
