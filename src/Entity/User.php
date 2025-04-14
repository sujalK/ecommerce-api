<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    public ?array $accessTokenScopes = null;

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $carts;

    /**
     * @var Collection<int, ShippingAddress>
     */
    #[ORM\OneToMany(targetEntity: ShippingAddress::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $shippingAddresses;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'ownedBy', orphanRemoval: true)]
    private Collection $orders;

    /**
     * @var Collection<int, ApiToken>
     */
    #[ORM\OneToMany(targetEntity: ApiToken::class, mappedBy: 'owner')]
    private Collection $apiTokens;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $verifiedAt = null;

    public function __construct()
    {
        $this->carts = new ArrayCollection();
        $this->shippingAddresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {

        if ( null === $this->accessTokenScopes ) {
            $roles   = $this->roles;
            $roles[] = 'ROLE_FULL_USER';
        } else {
            $roles = $this->accessTokenScopes;
        }

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

//    public function getAccountActiveStatus(): ?bool
//    {
//        return $this->accountActiveStatus;
//    }
//
//    public function setAccountActiveStatus(bool $accountActiveStatus): static
//    {
//        $this->accountActiveStatus = $accountActiveStatus;
//
//        return $this;
//    }

//    public function getVerificationStatus(): ?bool
//    {
//        return $this->verificationStatus;
//    }
//
//    public function setVerificationStatus(bool $verificationStatus): static
//    {
//        $this->verificationStatus = $verificationStatus;
//
//        return $this;
//    }

    public function getAccessTokenScopes(): ?array
    {
        return $this->accessTokenScopes;
    }

    public function setAccessTokenScopes(?array $accessTokenScopes): static
    {
        $this->accessTokenScopes = $accessTokenScopes;

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setOwner($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getOwner() === $this) {
                $cart->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ShippingAddress>
     */
    public function getShippingAddresses(): Collection
    {
        return $this->shippingAddresses;
    }

    public function addShippingAddress(ShippingAddress $shippingAddress): static
    {
        if (!$this->shippingAddresses->contains($shippingAddress)) {
            $this->shippingAddresses->add($shippingAddress);
            $shippingAddress->setOwner($this);
        }

        return $this;
    }

    public function removeShippingAddress(ShippingAddress $shippingAddress): static
    {
        if ($this->shippingAddresses->removeElement($shippingAddress)) {
            // set the owning side to null (unless already changed)
            if ($shippingAddress->getOwner() === $this) {
                $shippingAddress->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setOwnedBy($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getOwnedBy() === $this) {
                $order->setOwnedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwner($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwner() === $this) {
                $apiToken->setOwner(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $isVerified): void
    {
        $this->isVerified = $isVerified;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }
}
