<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Der Titel darf nicht leer sein.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Der Titel muss mindestens {{ limit }} Zeichen lang sein.",
        maxMessage: "Der Titel darf nicht länger als {{ limit }} Zeichen sein."
    )]
    private string $title;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Die Beschreibung darf nicht länger als {{ limit }} Zeichen sein."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Der Preis darf nicht leer sein.")]
    #[Assert\Positive(message: "Der Preis muss größer als Null sein.")]
    private string $price;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'products', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Keinen Ersteller gefunden.")]
    private User $createdFrom;

    /**
     * @var Collection<int, ProductPictures>
     */
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductPictures::class, fetch: 'EAGER')]
    private Collection $productPictures;

    public function __construct()
    {
        $this->productPictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
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

    public function getCreatedFrom(): ?User
    {
        return $this->createdFrom;
    }

    public function setCreatedFrom(?User $createdFrom): static
    {
        $this->createdFrom = $createdFrom;

        return $this;
    }

    /**
     * @return Collection<int, ProductPictures>
     */
    public function getProductPictures(): Collection
    {
        return $this->productPictures;
    }

    public function addProductPicture(ProductPictures $productPicture): static
    {
        if (!$this->productPictures->contains($productPicture)) {
            $this->productPictures->add($productPicture);
            $productPicture->setProduct($this);
        }

        return $this;
    }

    public function removeProductPicture(ProductPictures $productPicture): static
    {
        if ($this->productPictures->removeElement($productPicture)) {
            // set the owning side to null (unless already changed)
            if ($productPicture->getProduct() === $this) {
                $productPicture->setProduct(null);
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        $createdFrom = $this->getCreatedFrom();

        return [
            'id' => $this->getId(),
            'artNr' => 'art-' . $this->getId() . '00',
            'title' => $this->getTitle(),
            'price' => $this->getPrice(),
            'description' => $this->getDescription(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'createdFrom' => $createdFrom ? $createdFrom->getUsername() : null,
        ];
    }
}
