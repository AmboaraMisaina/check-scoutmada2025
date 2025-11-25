<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Organization;

#[ORM\Entity]
#[ORM\Table(name: "S_registration_steps")]
class RegistrationStep
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(nullable: false)] // Si l'organisation est obligatoire
    private ?Organization $organization = null; // Remarquez le nom de la propriété, sans "Id"

    #[ORM\Column(length: 255)]
    private ?string $step = null;

    #[ORM\Column(nullable: true)]
    private ?int $step_order = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column]
    private ?int $created_by = null;

    #[ORM\Column]
    private ?int $updated_by = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;
        return $this;
    }
    
    public function getStep(): ?string
    {
        return $this->step;
    }

    public function setStep(string $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function getStepOrder(): ?int
    {
        return $this->step_order;
    }

    public function setStepOrder(?int $step_order): static
    {
        $this->step_order = $step_order;
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

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(int $created_by): static
    {
        $this->created_by = $created_by;
        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(int $updated_by): static
    {
        $this->updated_by = $updated_by;
        return $this;
    }
}
