<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\LogAction;
use App\Logger\CustomLogger;
use App\Utility\UploadHandler;
use App\Utility\ErrorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService {
  public function __construct(
    private EntityManagerInterface $entityManager,
    private Security $security, 
    private CustomLogger $customLogger, 
    private ErrorHandler $errorHandler, 
    private UploadHandler $uploadHandler, 
    private ValidatorInterface $validator,
    private UserPasswordHasherInterface $passwordHasher,
    private TokenStorageInterface $tokenStorage  
  )
    {}

  public function showAllUsers() {
    return $this->entityManager->getRepository(User::class)->getAllActiveUsers();
  }

  public function editUserFN(User $user, ?UploadedFile $pictureFile, FormInterface $form, bool &$hasChangedPassword ): bool {
    try {    
      $this->entityManager->getConnection()->beginTransaction();

      $UserSession = $this->security->getUser();

      if (!$UserSession) {
        throw new \Exception('Keine User Session vorhanden!?');
      }

      // Checken ob es Änderungen gab
      $changes = $this->getDifferences($UserSession, $user);
      // Picture Upload vorhanden?
      
      $this->uploadHandler->handleProfilePictureUpload($user, $pictureFile, $changes);

      // Ist ein neues PW vorhanden?
      if (!empty($user->getPassword())){
          // Passwort hashen
          $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword()); 
          // Stelle sicher, dass das neue Passwort in die Datenbank geschrieben wird
          $changes['password'] = $hashedPassword;
      }

      $errors = $this->validator->validate($changes);
        if (count($errors) > 0) {
          foreach ($errors as $error) {
            $this->errorHandler->addErrorToForm($form, $error->getPropertyPath(), $error->getMessage());
          }
          return false;
      }

      // und nun machen wir die changes:
      if (!empty($changes)) {
        // Update Datum setten
        $changes['updatedAt'] = new \DateTimeImmutable();
        $this->entityManager->getRepository(User::class)->updateUser($user, $changes, $UserSession->getEmail());
        $this->entityManager->getConnection()->commit();

        $this->customLogger->logEvent(LogAction::USER_UPDATE, sprintf('Der Benutzer %s wurde geändert, folgende Änderungen wurden gemacht: %s', $user->getEmail(), json_encode($changes)));

        // Wir müssen das fucking token noch aktualisieren!
        // Aktualisiere das Token im Sicherheitskontext, falls erforderlich
        $this->updateUserToken($user);
      }

      if (isset($changes['password'])) {
          $hasChangedPassword = true;
      }

      return true;
    } catch (\Exception $e) {
      $this->entityManager->getConnection()->rollBack();
      // Error Werfen
      $this->errorHandler->addErrorToForm($form, 'generalError', 'Fehler beim Ändern Ihrer Userdaten.');
      // Loggen
      $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler beim Ändern der Userdaten => %s', $e->getMessage()));
      //throw $th;
      return false;
    }
  }

  private function getDifferences(User $currentUser, User $updatedUser): array
  {
      $changes = [];

      // Liste der zu überprüfenden Eigenschaften, die ausgeschlossen werden sollen
      $excludedProperties = ['picturePath', 'createdAt', 'updatedAt', 'password', 'passwordWdh', 'userName', 'id', 'products', 'active', 'roles', 'lastLoginAt', 'online'];

      // Verwenden der ReflectionClass, um die Methoden der User-Klasse zu durchlaufen , Reflection class for the win!!
      $reflectionClass = new \ReflectionClass(User::class);
      $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

      foreach ($methods as $method) {
          $methodName = $method->getName();

          // Nur Getter Methoden betrachten
          if (str_starts_with($methodName, 'get') || str_starts_with($methodName, 'is')) {
              $propertyName = lcfirst(preg_replace('/^(get|is)/', '', $methodName));

              // Überprüfen, ob die Eigenschaft ausgeschlossen werden soll
              if (!in_array($propertyName, $excludedProperties, true)) {
                  $currentValue = $currentUser->$methodName();
                  $updatedValue = $updatedUser->$methodName();

                  // Wenn die Werte unterschiedlich sind, fügen wir sie zu den Änderungen hinzu
                  if ($currentValue !== $updatedValue) {
                      $changes[$propertyName] = $updatedValue;
                  }
              }
          }
      }

      return $changes;
  }

  private function updateUserToken(User $user): void {
    $currentUser = $this->security->getUser();
    $token = $this->tokenStorage->getToken();

    if ($currentUser && $token) {
        // Get the firewall name from the current token
        $firewallName = $token instanceof PostAuthenticationToken ? $token->getFirewallName() : 'main';

        // Create a new token with updated roles
        $newToken = new PostAuthenticationToken(
            $currentUser,
            $firewallName,
            $user->getRoles() // Updated roles
        );

        // Set the new token in the security context
        $this->tokenStorage->setToken($newToken);
    }
  }
}