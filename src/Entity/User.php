<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Der Vorname wird benötigt')]
    #[Assert\Length(
        min: 2,
        minMessage: 'Der Vorname sollte mindestens {{ limit }} Zeichen lang sein'
    )]
    private string $firstName;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Der Nachname wird benötigt')]
    #[Assert\Length(
        min: 2,
        minMessage: 'Der Nachname sollte mindestens {{ limit }} Zeichen lang sein'
    )]
    private string $lastName;

    /* GENERIEREN WIR IM LAUFE DES REGISTRIERUNGSPROZESSES AUTOMATISCH DAHER ERSTMAL KEINE VALIDIERUNG, DA IN FORM NICHT VORHANDEN */
    #[ORM\Column(length: 255)]
    private string $userName;
    
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Email wird benötigt')]
    #[Assert\Email(message: 'Bitte geben Sie eine gültige Emailadresse an')]
    private string $email;

    /* HÄNGT VON EINEM UPLOAD AB */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picturePath = null;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Passwort wird benötigt', groups: ['registration', 'login'])]
    #[Assert\Length(
        min: 6,
        minMessage: 'Das Passwort sollte mindestens {{ limit }} Zeichen lang sein',
        groups: ['registration', 'login'] // soll nur für diese Gruppen validiert werden!
    )]
    private ?string $password = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(enumType: UserRole::class)]
    private UserRole $role;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(mappedBy: 'createdFrom', targetEntity: Product::class, orphanRemoval: true)]
    private Collection $products;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $registerToken = null;

    #[ORM\Column]
    private ?bool $active = false;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $passwordResetToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column]
    private bool $isOnline = false;    

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    public function setPicturePath(?string $picturePath): static
    {
        $this->picturePath = $picturePath;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCreatedFrom($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCreatedFrom() === $this) {
                $product->setCreatedFrom(null);
            }
        }

        return $this;
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

    public function getRegisterToken(): ?string
    {
        return $this->registerToken;
    }

    public function setRegisterToken(?string $registerToken): static
    {
        $this->registerToken = $registerToken;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    // Erzeugen des Usernames
    public function generateUserName(): void
    {
        $firstLetter = strtolower(substr($this->firstName, 0, 1));
        $lastName = strtolower($this->lastName);
        $this->userName = $firstLetter . $lastName;
    }

    // Erzuegen des Tokens
    public function generateToken(): string
    {
        // Generiert ein zufälliges 6-stelliges Token
        return str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /* UserInterface Methods für de login */
     public function getRoles(): array
    {
       return ['ROLE_' . strtoupper($this->role->value)];
    }

    public function getUserIdentifier(): ?string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Wenn Sie sensible Daten temporär speichern, löschen Sie sie hier
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'userName' => $this->getUserName(),
            'email' => $this->getEmail(),
            'picturePath' => $this->getPicturePath(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            'role' => $this->getRole() ? $this->getRole()->value : null, // Wenn UserRole ein Enum ist, gib den Wert zurück
            'registerToken' => $this->getRegisterToken(),
            'active' => $this->isActive(),
        ];
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): static
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function isOnline(): ?bool
    {
        return $this->isOnline;
    }

    public function setOnline(bool $isOnline): static
    {
        $this->isOnline = $isOnline;

        return $this;
    }
}
