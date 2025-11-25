<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'S_program_registrations')]
#[ORM\UniqueConstraint(name: 'unique_participant_program', columns: ['participant_id', 'program_id'])] // Évite les doublons
class ProgramRegistration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'programRegistrations')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Participant $participant = null;

    #[ORM\ManyToOne(targetEntity: Program::class, inversedBy: 'programRegistrations')]
    #[ORM\JoinColumn(name: 'program_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Program $program = null;

    #[ORM\Column]
    private int $createdBy;

    // ====================================================================
    // Getters & Setters
    // ====================================================================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;

        return $this;
    }

    public function getProgram(): ?Program
    {
        return $this->program;
    }

    public function setProgram(?Program $program): self
    {
        $this->program = $program;

        return $this;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }
    public function setCreatedBy(int $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}