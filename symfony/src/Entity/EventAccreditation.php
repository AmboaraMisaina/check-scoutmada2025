<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "event_accreditations")]
class EventAccreditation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $participant_type_id = null;

    #[ORM\Column]
    private ?int $event_id = null;

    #[ORM\Column]
    private ?int $created_by = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $updated_at = null;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipantTypeId(): ?int
    {
        return $this->participant_type_id;
    }

    public function setParticipantTypeId(int $participant_type_id): static
    {
        $this->participant_type_id = $participant_type_id;

        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->event_id;
    }

    public function setEventId(int $event_id): static
    {
        $this->event_id = $event_id;

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
    public function setCreatedAt(?\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }
    public function setUpdatedAt(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}