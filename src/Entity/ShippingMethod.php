<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\ShippingMethodRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ShippingMethodRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_SHIPPING_METHOD_NAME', fields: ['name'])]
class ShippingMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $cost = null;

    #[ORM\Column(length: 100)]
    private ?string $estimatedDeliveryTime = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(?string $cost): static
    {
        $this->cost = $cost;

        return $this;
    }

    public function getEstimatedDeliveryTime(): ?string
    {
        return $this->estimatedDeliveryTime;
    }

    public function setEstimatedDeliveryTime(string $estimatedDeliveryTime): static
    {
        $this->estimatedDeliveryTime = $estimatedDeliveryTime;

        return $this;
    }
}
