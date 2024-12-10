<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantityInStock = null;

    #[ORM\Column]
    private ?int $quantitySold = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantityBackOrdered = null;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    private ?Product $product = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantityInStock(): ?int
    {
        return $this->quantityInStock;
    }

    public function setQuantityInStock(int $quantityInStock): static
    {
        $this->quantityInStock = $quantityInStock;

        return $this;
    }

    public function getQuantitySold(): ?int
    {
        return $this->quantitySold;
    }

    public function setQuantitySold(int $quantitySold): static
    {
        $this->quantitySold = $quantitySold;

        return $this;
    }

    public function getQuantityBackOrdered(): ?int
    {
        return $this->quantityBackOrdered;
    }

    public function setQuantityBackOrdered(?int $quantityBackOrdered): static
    {
        $this->quantityBackOrdered = $quantityBackOrdered;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
