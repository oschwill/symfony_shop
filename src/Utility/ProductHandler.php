<?php

namespace App\Utility;

use App\Entity\Product;
use App\Entity\ProductPictures;
use Doctrine\ORM\EntityManagerInterface;

class ProductHandler
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    // Setzt die allgemeinen Produktdaten (Titel, Beschreibung, Preis)
    public function setProductData(Product $product, $data, bool $isNew = true): void
    {
        $product->setTitle($data['data']['title']);
        $product->setDescription($data['data']['description']);
        $product->setPrice($data['data']['price']);
        
        if ($isNew) {
            $product->setCreatedAt(new \DateTimeImmutable());
        } else {
            $product->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    // Verarbeitet die Bilder und fügt sie dem Produkt hinzu
    public function processProductPictures(Product $product, array $pictures): void
    {
        foreach ($pictures as $picturePath) {
            $productPicture = new ProductPictures();
            $productPicture->setPicturePath($picturePath);
            $productPicture->setProduct($product);
            $this->entityManager->persist($productPicture);
        }
    }

    // Entfernt alle bestehenden Bilder des Produkts
    public function removeExistingPictures(Product $product): void
    {
        foreach ($product->getProductPictures() as $existingPicture) {
            $this->entityManager->remove($existingPicture);
        }
        $this->entityManager->flush(); // Sofortiges Löschen der Bilder
    }
}