<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    private ?string $discountType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $discountValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2, nullable: true)]
    private ?string $maxDiscountAmountForPercentage = null;

    #[ORM\Column]
    private array $appliesTo = [];

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $minimumCartValue = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private ?int $usageLimit = null;

    #[ORM\Column]
    private ?int $singleUserLimit = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDiscountType(): ?string
    {
        return $this->discountType;
    }

    public function setDiscountType(string $discountType): static
    {
        $this->discountType = $discountType;

        return $this;
    }

    public function getDiscountValue(): ?string
    {
        return $this->discountValue;
    }

    public function setDiscountValue(string $discountValue): static
    {
        $this->discountValue = $discountValue;

        return $this;
    }

    public function getMaxDiscountAmountForPercentage(): ?string
    {
        return $this->maxDiscountAmountForPercentage;
    }

    public function setMaxDiscountAmountForPercentage(string $maxDiscountAmountForPercentage): static
    {
        $this->maxDiscountAmountForPercentage = $maxDiscountAmountForPercentage;

        return $this;
    }

    public function getAppliesTo(): array
    {
        return $this->appliesTo;
    }

    public function setAppliesTo(array $appliesTo): static
    {
        $this->appliesTo = $appliesTo;

        return $this;
    }

    public function getMinimumCartValue(): ?string
    {
        return $this->minimumCartValue;
    }

    public function setMinimumCartValue(string $minimumCartValue): static
    {
        $this->minimumCartValue = $minimumCartValue;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(int $usageLimit): static
    {
        $this->usageLimit = $usageLimit;

        return $this;
    }

    public function getSingleUserLimit(): ?int
    {
        return $this->singleUserLimit;
    }

    public function setSingleUserLimit(int $singleUserLimit): static
    {
        $this->singleUserLimit = $singleUserLimit;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
