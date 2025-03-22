<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
  private UserPasswordHasherInterface $passwordHasher;

  // public function __invoke(UserPasswordHasherInterface $passwordHasher)
  // {
  //     $this->passwordHasher = $passwordHasher;
  // }

  public function __construct(UserPasswordHasherInterface $passwordHasher)
  {
    if ($passwordHasher === null) {
        throw new \InvalidArgumentException('UserPasswordHasherInterface is required.');
    }
    $this->passwordHasher = $passwordHasher;
  }

  // Factory Methode
   public static function create(UserPasswordHasherInterface $passwordHasher): self
  {
      $fixture = new self($passwordHasher);
      return $fixture;
  }

  public function load(ObjectManager $manager): void
  {
      /* BUILD INITIAL SUPER DUPER USER, WITH SUPER DUPER RIGHTS hust */
      $user = new User();
      $user->setFirstName('Super');
      $user->setLastName('User');
      $user->generateUserName();

      $plainPassword = getenv('SUPER_USER_PASSWORD');

      $hashedPassword = $this->passwordHasher->hashPassword($user, sprintf('%s', $plainPassword));
      $user->setPassword($hashedPassword);

      /* SET CREATE DATE AND ROLE */
      $user->setCreatedAt(new \DateTime(date("Y-m-d h:i:sa")));
      $user->setRole(UserRole::ADMIN);
      
      /* SET ADMIN FAKE EMAIL */
      $user->setEmail('suser@admin.de');

      /* SET ACTIVE STATUS */
      $user->setActive(true);


      /* load datt data into database */
      $manager->persist($user);
      $manager->flush();

      /* Lets get the reference for the products */
      $this->addReference('user_1', $user);
  }

  private function getFirstLetter($name) {
    return mb_substr($name, 0, 1);
  }
}
