<?php

namespace App\DataFixtures;

use App\Entity\ProductPictures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductPicturesFixtures extends Fixture implements DependentFixtureInterface
{
    private int $productCount = 15;   
    private int $picturesPerProduct = 3;

    public function load(ObjectManager $manager): void
    {
        for ($i=0; $i < $this->productCount; $i++) { 
           $product = $this->getReference(sprintf('product_%d', $i));

           for ($j=0; $j < $this->picturesPerProduct; $j++) { 
              $productPicture = new ProductPictures();
              $productPicture->setPicturePath($this->getRandomImageUrl()); // Moment
              $productPicture->setProduct($product);

              $manager->persist($productPicture);
            }
        }          
        $manager->flush();
    }    

    public function getDependencies()
    {
        return [
            ProductFixtures::class,
        ];
    }

    private function getRandomImageUrl(): string
    {
        $id = rand(100, 350);
        // Return a random image URL from Picsum
        // $width = rand(200, 300);// Random Breite
        // $height = rand(300, 800); // Random height 
        return sprintf('https://picsum.photos/id/%d/2160/3840', $id );
    }
}
