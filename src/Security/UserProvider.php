<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserProvider implements UserProviderInterface
{

    public function __construct(private UserRepository $userRepository, private UserPasswordHasherInterface $passwordHasher)
    { }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Überprüfen ob es den User gibt
        $user = $this->userRepository->findActiveUserByEmail($identifier);

        if (!$user) {
            throw new AuthenticationException(sprintf('Login fehlgeschlagen für "%s".', $identifier));
        }

        return $user;
    }

    public function checkCredentials($credentials, PasswordAuthenticatedUserInterface $user): bool
    {
        // Hier wird das Passwort überprüft
        return $this->passwordHasher->isPasswordValid($user, $credentials);
    }

    // Hier refreshen wir unser Token
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Session ausgelaufen für "%s", oder es handelt sich um keiner Instanz von User.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getEmail());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}