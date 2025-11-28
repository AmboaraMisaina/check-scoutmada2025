<?php

namespace App\Entity;

use App\Repository\EventAccreditationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "S_event_accreditations")] // Retrait du préfixe 'S_' pour éviter les conflits
class EventAccreditation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // 1. RELATION VERS PARTICIPANT TYPE (ManyToOne)
    // Utilisation de la convention camelCase pour la propriété ($participantType)
    #[ORM\ManyToOne(inversedBy: 'eventAccreditations')]
    #[ORM\JoinColumn(name: 'participant_type_id', referencedColumnName: 'id', nullable: false)]
    private ?ParticipantType $participantType = null;

    // 2. RELATION VERS EVENT (ManyToOne)
    #[ORM\ManyToOne(inversedBy: 'eventAccreditations')] // inversedBy doit exister dans Event.php
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', nullable: false)]
    private ?Event $event = null;

    // 3. RELATION VERS ADMIN (CREATED BY)
    // Le créateur est un identifiant entier (int)
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $createdBy = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false)]
    private string $status;

    // 4. Champs de date/heure (Utilisation de DateTimeImmutable recommandé pour les dates de création/modification)
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    // --- Getters et Setters (Mis à jour en camelCase) ---
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipantType(): ?ParticipantType
    {
        return $this->participantType; // Nom de la propriété corrigé
    }

    public function setParticipantType(?ParticipantType $participantType): static // Nom de la propriété corrigé
    {
        $this->participantType = $participantType;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getCreatedBy(): ?int // Type de retour corrigé
    {
        return $this->createdBy; // Nom de la propriété corrigé
    }

    public function setCreatedBy(?int $createdBy): static // Type d'argument corrigé
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }  
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}