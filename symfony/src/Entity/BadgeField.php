<?php

namespace App\Entity;

use App\Repository\BadgeFieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'badge_fields')]
class BadgeField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Correspond à badge_template_id (fk_badge_field_template)
    #[ORM\ManyToOne(inversedBy: 'badgeFields')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BadgeTemplate $badgeTemplate = null;

    #[ORM\Column(length: 100)]
    private ?string $fieldName = null; // ex: "first_name", "last_name", "qr_code"

    #[ORM\Column]
    private ?int $posX = null; // Position X

    #[ORM\Column]
    private ?int $posY = null; // Position Y

    #[ORM\Column(nullable: true)]
    private ?int $fontSize = 12;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fontColor = '#000000';

    #[ORM\Column(nullable: true)]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    private ?int $height = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBadgeTemplate(): ?BadgeTemplate
    {
        return $this->badgeTemplate;
    }

    public function setBadgeTemplate(?BadgeTemplate $badgeTemplate): static
    {
        $this->badgeTemplate = $badgeTemplate;
        return $this;
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): static
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function getPosX(): ?int
    {
        return $this->posX;
    }

    public function setPosX(int $posX): static
    {
        $this->posX = $posX;
        return $this;
    }

    public function getPosY(): ?int
    {
        return $this->posY;
    }

    public function setPosY(int $posY): static
    {
        $this->posY = $posY;
        return $this;
    }

    public function getFontSize(): ?int
    {
        return $this->fontSize;
    }

    public function setFontSize(?int $fontSize): static
    {
        $this->fontSize = $fontSize;
        return $this;
    }

    public function getFontColor(): ?string
    {
        return $this->fontColor;
    }

    public function setFontColor(?string $fontColor): static
    {
        $this->fontColor = $fontColor;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;
        return $this;
    }
}