<?php

namespace App\Entity;

use App\Repository\RegistrationFollowupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "S_registration_followups")]
class RegistrationFollowup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relation ManyToOne vers Participant
    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'registrationFollowups')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Participant $participant = null;

    // Relation ManyToOne vers RegistrationStep
    #[ORM\ManyToOne(targetEntity: RegistrationStep::class, inversedBy: 'registrationFollowups')]
    #[ORM\JoinColumn(name: 'step_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?RegistrationStep $step = null;


    #[ORM\ManyToOne(targetEntity: Program::class)]
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Program $program = null;

    // Statut (ex: pending, approved, rejected, etc.)
    #[ORM\Column]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $createdBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $createdAt = null;

    // ========================
    // GETTERS & SETTERS
    // ========================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;
        return $this;
    }

    public function getStep(): ?RegistrationStep
    {
        return $this->step;
    }

    public function setStep(?RegistrationStep $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function getStatus(): ?String
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getProgram(): ?Program
    {
        return $this->program;
    }
    public function setProgram(?Program $program): static
    {
        $this->program = $program;
        return $this;
    }   
}