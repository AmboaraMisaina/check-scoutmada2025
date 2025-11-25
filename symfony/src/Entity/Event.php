<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity] // Ajoutez le Repository
#[ORM\Table(name: "S_events")] // 🚩 Retrait du préfixe 'S_' pour la cohérence
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // organization_id (FK)
    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // 🚩 Correction : Utilisation du camelCase
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    // 🚩 Correction : Utilisation du camelCase
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    // 🚩 Correction : Utilisation du camelCase
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    // 🚩 Correction : Utilisation du camelCase
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    // 🚩 CORRECTION MAJEURE : Relation ManyToOne vers l'objet Admin
    #[ORM\Column(type: 'integer')]
    private ?int $createdBy = null;

    // 🚩 CORRECTION MAJEURE : Relation ManyToOne vers l'objet Admin
    #[ORM\Column(type: 'integer')]
    private ?int $updatedBy = null;

    // 🚩 Correction : Utilisation de DateTimeImmutable et camelCase
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $createdAt = null;

    // 🚩 Correction : Utilisation de DateTimeImmutable et camelCase
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $updatedAt = null;

    // 🚩 CORRECTION MAJEURE : Remplacement de la M:M par la O:M vers l'entité intermédiaire
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventAccreditation::class, orphanRemoval: true)]
    private Collection $eventAccreditations;

    public function __construct()
    {
        $this->eventAccreditations = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /* ======================
        GETTERS & SETTERS
        ====================== */
        
    // --- Gestion de la nouvelle Collection d'Accréditations (O:M) ---

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
            $eventAccreditation->setEvent($this);
        }

        return $this;
    }

    public function removeEventAccreditation(EventAccreditation $eventAccreditation): static
    {
        if ($this->eventAccreditations->removeElement($eventAccreditation)) {
            // set the owning side to null (unless already changed)
            if ($eventAccreditation->getEvent() === $this) {
                $eventAccreditation->setEvent(null);
            }
        }
        return $this;
    }

    // --- Remplacement des anciennes fonctions M:M (pour le formulaire) ---

    /**
     * Cette méthode est gardée UNIQUEMENT pour la compatibilité avec le formulaire EventType,
     * qui lira les accréditations existantes. La logique de SETTING est dans le Controller.
     */
    public function getAuthorizedParticipantTypes(): Collection
    {
        // Retourne les types de participants accrédités à travers l'entité intermédiaire
        return $this->eventAccreditations->map(fn (EventAccreditation $ea) => $ea->getParticipantType());
    }


    // --- Getters/Setters pour les autres propriétés (corrigés en camelCase) ---

    public function getId(): ?int
    {
        return $this->id;
    }
    
    // ... (Organization est correct) ...
    
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime; // Corrigé
    }

    public function setStartTime(\DateTimeInterface $startTime): static // Corrigé
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime; // Corrigé
    }

    public function setEndTime(\DateTimeInterface $endTime): static // Corrigé
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate; // Corrigé
    }

    public function setStartDate(\DateTimeInterface $startDate): static // Corrigé
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate; // Corrigé
    }

    public function setEndDate(\DateTimeInterface $endDate): static // Corrigé
    {
        $this->endDate = $endDate;
        return $this;
    }

    // --- Getters/Setters pour Admin ---

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    // --- Getters/Setters pour Dates ---

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt; // Corrigé
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static // Corrigé
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt; // Corrigé
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static // Corrigé
    {
        $this->updatedAt = $updatedAt;
        return $this;
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
}