<?php
namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

// Mit AsEventListener wird der Listener automatisch registriert, also keine services Gedöns Einträge ab Version 6.x
class UserLoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    // Abchecken ob der User sich eingeloggt hat, dann das login Datum in die DB hauen. Wird automatisch aufgerufen
    #[AsEventListener(event: 'security.interactive_login')]
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTimeImmutable());
            $user->setOnline(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

     #[AsEventListener(event: 'Symfony\Component\Security\Http\Event\LogoutEvent')]
      public function onLogout(LogoutEvent $event): void
      {
          $token = $event->getToken();
          if ($token === null) {
              return;
          }

          $user = $token->getUser();
          if ($user instanceof User) {
              $user->setOnline(false);
              $this->entityManager->persist($user);
              $this->entityManager->flush();
          }
      }
}