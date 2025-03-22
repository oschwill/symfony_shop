<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    private int $productCount = 15;   


    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        // Get the user reference
        $user = $this->getReference('user_1');

        for ($i = 0; $i < $this->productCount; $i++) {
            $product = new Product();
            $product->setTitle($faker->word());
            $product->setDescription($faker->sentence());
            $product->setPrice($faker->randomFloat(2, 10, 1000)); // Random price between 10 and 1000Tables        
            $product->setCreatedAt(new \DateTimeImmutable()); // Set current date and time
            $product->setUpdatedAt(null); // Optional, set as null
            $product->setCreatedFrom($user);

            $manager->persist($product);

            $this->addReference(sprintf('product_%d', $i), $product);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
