<?php

namespace App\Entity;

use App\Repository\ProductPicturesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPicturesRepository::class)]
class ProductPictures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picturePath = null;

    #[ORM\ManyToOne(inversedBy: 'productPictures')]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    // To Array Getter Methode
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'picturePath' => $this->picturePath,
            'productId' => $this->product->getId()
        ];
    }
}
