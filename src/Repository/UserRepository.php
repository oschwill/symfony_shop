<?php

namespace App\Repository;

use App\Entity\User;
use App\Utility\UploadHandler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private UploadHandler $uploadHandler)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * FIND BY EMAIL
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function getAllActiveUsers(): array
    {
        // Hole alle aktiven Benutzer aus der Datenbank
        return $this->findBy(['active' => true]);
    }

    // Aggregation Pipeline / temporär
    public function findActiveUserByEmail(string $email): ?User
    {
    return $this->createQueryBuilder('u')
        ->where('u.email = :email')
        ->andWhere('u.active = true')
        ->andWhere('u.registerToken IS NULL')
        ->setParameter('email', $email)
        ->getQuery()
        ->getOneOrNullResult();
    }

    // Find by passwordResetToken
    public function findOneByResetTokenAndEmail(string $email, string $token) {
        return $this->findOneBy(['email' => $email, 'passwordResetToken' => $token]);
    }

    // einzelne Attribute für den User ändern
    public function updateUser(User $user, array $changes, $email = null): void
    {
        // Hole den aktuellen User aus der Datenbank
        $existingUser = $this->findOneByEmail($email);
        
        if (!$existingUser) {
            throw new \Exception('User not found');
        }


        // Wenn ein neuer Bildpfad übergeben wurde, das alte Bild löschen
        if (isset($changes['picturePath'])) {
            if (!$this->uploadHandler->removeUploadedFile($existingUser)) {
                throw new \Exception('Fehler beim Löschen des bereits bestehenden Profilbildes!!');
            }
        }  
        
        
        // Aktualisiere nur die geänderten Felder
        foreach ($changes as $key => $value) {
            // Alle Felder überspringen die nicht im changes Array sind
            $setter = 'set' . ucfirst($key);
            if (array_key_exists($key, $changes) && method_exists($existingUser, $setter)) {
                $existingUser->$setter($value);
            }
        }
        
        $this->_em->flush();
    }
}
