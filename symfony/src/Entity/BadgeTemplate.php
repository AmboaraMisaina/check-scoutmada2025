<?php

namespace App\Entity;

use App\Repository\BadgeTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'S_badge_templates')]
class BadgeTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Correspond à organization_id (fk_badge_template_org)
    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Organization $organization = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $filePath = null; // chemin vers le PDF ou l'image du modèle

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    // Relation One-to-Many vers BadgeField
    #[ORM\OneToMany(mappedBy: 'badgeTemplate', targetEntity: BadgeField::class, orphanRemoval: true)]
    private Collection $badgeFields;

    public function __construct()
    {
        $this->badgeFields = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, BadgeField>
     */
    public function getBadgeFields(): Collection
    {
        return $this->badgeFields;
    }

    public function addBadgeField(BadgeField $badgeField): static
    {
        if (!$this->badgeFields->contains($badgeField)) {
            $this->badgeFields->add($badgeField);
            $badgeField->setBadgeTemplate($this);
        }

        return $this;
    }

    public function removeBadgeField(BadgeField $badgeField): static
    {
        if ($this->badgeFields->removeElement($badgeField)) {
            // set the owning side to null (unless already changed)
            if ($badgeField->getBadgeTemplate() === $this) {
                $badgeField->setBadgeTemplate(null);
            }
        }

        return $this;
    }
}