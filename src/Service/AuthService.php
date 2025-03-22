<?php

namespace App\Service;

use App\DTO\PasswordResetDTO;
use App\Entity\User;
use App\Enum\LogAction;
use App\Enum\UserRole;
use App\Logger\CustomLogger;
use App\Utility\UploadHandler;
use App\Utility\ErrorHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class AuthService {

  public function __construct(
    private TokenStorageInterface $tokenStorage,
    private EventDispatcherInterface $eventDispatcher,
    private EntityManagerInterface $entityManager,
    private UserPasswordHasherInterface $passwordHasher,
    private ValidatorInterface $validator,
    private ErrorHandler $errorHandler,
    private CustomLogger $customLogger,
    private MailerInterface $mailer,
    private RequestStack $requestStack,
    private Environment $twig,
    private EmailService $emailService,
    private UploadHandler $uploadHandler,
    )
    {}

  public function registerUser(User $user, ?UploadedFile $pictureFile, FormInterface $form, string $recaptchaResponse, string $clientIp): bool{      
    try {  
        // Transaction starten damit notfalls rollbacked werden kann
        $this->entityManager->getConnection()->beginTransaction();

        
        // ReCaptcha Validierung durchführen
        if (!$this->validateRecaptcha($recaptchaResponse, $clientIp)) {
            $this->errorHandler->addErrorToForm($form, 'generalError', 'Roboter haben keinen Zutritt. Bitte versuchen Sie es erneut.');
            return false;
        }

        // Bevor wir aber mit der Registry Prozedur beginnen lass uns mal checken ob die Email schon existiert
        if ($this->isEmailAvailable($user->getEmail())) {    
            $this->errorHandler->addErrorToForm($form, 'email', 'Diese E-Mail-Adresse wird bereits verwendet.');
            return false;
        }
          
        // Nun restliche Validierungen durchführen
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
          foreach ($errors as $error) {
            $this->errorHandler->addErrorToForm($form, $error->getPropertyPath(), $error->getMessage());
          }
          return false;
        }

        // Wir createn das aktuelle Datum noch und setzten die Role manuell auf user und erzeugen den username
        $user->setCreatedAt(new \DateTime()); 
        $user->setRole(UserRole::USER);
        $user->generateUserName(); 

        // Passwort hashen
        $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        // Token erzeugen
        $user->setRegisterToken($user->generateToken());

        // Login Status setzen
        $user->setActive(false);
        
        // Bild-Upload, falls vorhanden
        $this->uploadHandler->handleProfilePictureUpload($user, $pictureFile);

        // Und ab die Post, und das commiten nicht vergessen
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // EMAIL SENDEN AN DEN USER!       
        if ( $error = $this->emailService->sendConfirmationEmail($user)) {      
          throw new \Exception("Email Processing Request:". $error);          
        }
        
        // Commiten
        $this->entityManager->getConnection()->commit();
        // Und die Prozedur noch loggen
        $this->customLogger->logEvent(LogAction::USER_REGISTER, $user->toArray());

        return true;

      } catch (\Exception $e) {
          // Datenbankeintrag zurück rollen
           $this->entityManager->getConnection()->rollBack();
          // Fehlerprotokollierung oder zusätzliche Fehlerbehandlung
          $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler bei der Registrierung: %s', $e->getMessage() ));

          // Fehlermeldung an den User werfen
          $this->errorHandler->addErrorToForm($form, 'generalError', 'Die Registrierung ist fehlgeschlagen.');

          return false;
        }      
  }

  public function verifyUserToken(string $token, string $email): bool
  {
      // Finde den Benutzer anhand der E-Mail und des Tokens
      try {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email, 'registerToken' => $token]);        

        // // Token löschen und den User auf active setzten
        $user->setActive(true);
        $user->setRegisterToken(null);
        
        // //  speichern
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $user_log = ["user_id" => $user->getId(), "token" => $user->getRegisterToken(), "active" => $user->isActive()];

        $this->customLogger->logEvent(LogAction::USER_UPDATE, $user_log);
        
        return true;
      } catch (\Throwable $th) {
        return false;
      }
  }

  private function isEmailAvailable(string $email): bool
  {
     return $this->entityManager->getRepository(User::class)
                                            ->findOneByEmail($email) !== null;
  }

  public function runForgotPasswordProcess(User $user): bool {  
    try {
      // ERstellen und holen uns das Token
        $token = $this->createPasswordForgotToken($user);
  
        // Und Senden die Email      
        $this->emailService->sendPasswordResetEmail($user, $token);

        return true;
    } catch (\Exception $e) {
       // Fehlerprotokollierung oder zusätzliche Fehlerbehandlung
       $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Fehler beim Anfordern einer Passwort vergessen Email: %s', $e->getMessage()));
       return false;
    }
  }

  private function createPasswordForgotToken(User $user): string {
      // Token createn
      $resetToken = bin2hex(random_bytes(32));
      $user->setPasswordResetToken($resetToken); 

      $this->entityManager->persist($user);
      $this->entityManager->flush();

      return $resetToken;
  }

  public function checkPasswordChangeToken(string $email, string $token) : bool{
      if (!$this->entityManager->getRepository(User::class)->findOneByResetTokenAndEmail($email, $token)) {
        return false;      
      }

      return true;
  }

  private function validateRecaptcha(string $recaptchaResponse, string $clientIp): bool
  {
      $recaptchaSecretKey = $_ENV['RECAPTCHA_SECRET_KEY'];
      $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';

      $response = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecretKey . '&response=' . $recaptchaResponse . '&remoteip=' . $clientIp);
      $responseData = json_decode($response);

      return isset($responseData->success) && $responseData->success;
  }

  public function changeAuthPassword(PasswordResetDTO $userData, FormInterface $form, string $email ): bool {
    
    try {
      // Validieren
      $errors = $this->validator->validate($userData);

      if (count($errors) > 0) {
        foreach ($errors as $error) {
          $this->errorHandler->addErrorToForm($form, $error->getPropertyPath(), $error->getMessage());
        }
        return false;
      }

      // User holen
      $getUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

      if (!$getUser) {
          // Benutzer wurde nicht gefunden
          $this->errorHandler->addErrorToForm($form, 'generalError', 'Benutzer nicht gefunden. Bitte kontaktieren Sie den Support!');
          return false;
      }
      
      // Passwort hashen
      $hashedPassword = $this->passwordHasher->hashPassword($getUser, $userData->getPassword());
      $getUser->setPassword($hashedPassword);

      // Token clearn
      $getUser->setPasswordResetToken(null);

      // persistieren
      $this->entityManager->persist($getUser);
      $this->entityManager->flush();
      
      $this->customLogger->logEvent(LogAction::USER_UPDATE, sprintf('Passwort reset erfolgreich für %s', $getUser->getEmail()));

      return true;
    } catch (\Exception $e) {
      $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, sprintf('Passwort reset fehlgeschlagen für %s, Fehlermeldung: %s', $getUser->getEmail(), $e->getMessage()));
      // ErrorMeldung werfen
      $this->errorHandler->addErrorToForm($form, 'generalError', 'Das Ändern des Passwortes ist fehlgeschlagen . Bitte kontaktieren Sie den Support!');

      return false;
    }
  }
}