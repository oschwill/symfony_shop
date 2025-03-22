<?php

namespace App\Controller;

use App\Form\User\LoginFormType;
use App\Form\User\PasswordResetFormType;
use App\Form\User\RegistrationFormType;
use App\Service\AuthService;
use App\Utility\ErrorHandler;
use App\Service\FlashSessionService;
use App\Service\RedirectAuthenticatedUserService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use App\DTO\PasswordResetDTO;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController extends BaseController
{
    public function __construct(
        private FlashSessionService $flashSessionService, 
        private AuthService $authService,
        private ErrorHandler $errorHandler,  
        private RedirectAuthenticatedUserService $redirectAuthenticatedUserService,
        private RequestStack $requestStack,
        private Security $security
    )
    {        
        // Parentclass Konstruktor aufrufen um die Weiterleitung zu setzen
        parent::__construct($redirectAuthenticatedUserService, $requestStack);
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        // eingeloggter User
        if ($this->redirectResponse) {
            return $this->redirectResponse;
        }        

        /* Auf Parameter überprüfen ob die Registrierung abgeschlossen ist */
        $isRegistrationComplete = $request->query->get('reg') === 'true';

        if ($isRegistrationComplete) {        
            $flashMessages = $this->flashSessionService->getFlash(FlashSessionService::REGISTRATION_SUCCESS);             

            if (empty($flashMessages)) {                
                // Keine Nachricht vorhanden die URL wurde manuell aufgerufen
                return $this->redirectToRoute('app_register'); // Redirect zur normalen registry Seite
            } else {
                // Erfolgreiche Registrierung, geben anderes Template aus
                return $this->render('auth/registration_success.html.twig', [
                    'controller_name' => 'Registrierung erfolgreich',
                    'message' => $flashMessages[0] 
                ]);
            }
       }

        // Erstelle das Formular und übergebe die Entity        
        $form = $this->createForm(RegistrationFormType::class, null, [
            'validation_groups' => ['registration'], // Validierungsgruppe setzen
        ]);

        // Handle den Submit
        $form->handleRequest($request);

        // Recaptcha API KEYS
        $recaptchaSiteKey = $_ENV['RECAPTCHA_SITE_KEY'];
        $recaptchaSecretKey = $_ENV['RECAPTCHA_SECRET_KEY'];

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            // Das Upload Picture aus der Form seperat holen
            $pictureFile = $form->get('pictureUpload')->getData();
            // Daten aus der Form holen
            $user = $form->getData();   
           
            // Recaptcha API CALL 
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            // IP Holen für Recaptcha
            $clientIp = $request->getClientIp();

            // Service aufrufen und wenn erfolgreich weiterleiten auf Erfolsseite...
            if ($this->authService->registerUser($user, $pictureFile, $form,  $recaptchaResponse, $clientIp)) {
                // Wir setzen einen Flash der dann beim nächsten Aufruf de register Seite verfügbar ist und redirecten
                $this->flashSessionService->addFlash(FlashSessionService::REGISTRATION_SUCCESS);

                return $this->redirectToRoute('app_register', ['reg' => 'true']);
            }               
        }

        return $this->render('auth/register.html.twig', [
            'controller_name' => 'Registrierung',
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => $recaptchaSiteKey,
        ]);
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, Security $security, SessionInterface $session): Response
    {  
        // eingeloggter User
        if ($this->redirectResponse) {
            return $this->redirectResponse;
        }

        // Session wieder löschen nach Weiterleitung von der Verifizierung und die Modal box erscheinen lassen
        if ($session->has('is_verified')) {
            $this->flashSessionService->addFlash(FlashSessionService::REGISTRATION_VERIFY_SUCCESS);  
             // Entferne die Session Variable 'is_verified'
             $session->remove('is_verified');
             // Nochmal redirecten
             return $this->redirectToRoute('app_login');
        }

        // Erstelle das Formular und übergebe den context        
        $form = $this->createForm(LoginFormType::class, null, [
            'validation_groups' => ['login'], // Validierungsgruppe setzen
        ]);
        // Eingabefeld email Eingabe holen
        // $lastUsername = $authenticationUtils->getLastUsername(); // funzt nicht
        $lastUsername = $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);

        $flashMessage = $this->flashSessionService->getFlash(FlashSessionService::LOGIN_FAILED);
        if (!empty($flashMessage)) {
            $this->errorHandler->addErrorToForm($form, 'generalError', 'Der Login ist fehlgeschlagen.');
        }


        return $this->render('auth/login.html.twig', [
            'controller_name' => 'Login',
            'email' => $lastUsername,
            'loginForm' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Der Logout wird von Symfony gehandhabt? 
    }

    #[Route('/verify/{token}/{email}', name: 'app_verify_email', methods: ['GET'])]
    public function verify(string $token, string $email,  SessionInterface $session): Response
    {
        // Prüfen ob der link ein weiteres mal aufgerufen wird
        if ($session->has('link_expired')) {
            // Benutzer wurde bereits verifiziert, leiten zur Login-Seite weiter
            return $this->redirectToRoute('app_login');
        }

        // Token und Email verarbeiten
        $isFullyRegistered = $this->authService->verifyUserToken($token, $email);

        if ($isFullyRegistered) {
            $this->flashSessionService->addFlash(FlashSessionService::REGISTRATION_VERIFY_SUCCESS);           
            $session->set('is_verified', true);
            $session->set('link_expired', true);
            // Weiterleitung zur Login-Seite nach Ablauf des Timers
            return $this->render('auth/verify_email.html.twig', [
                'controller_name' => 'Verifizierung erfolgreich',
                'timer_duration' => 5, // Redirect Timer :)
            ]);
        } else {
            // Fehlgeschlagene Verifizierung
            $this->flashSessionService->addFlash(FlashSessionService::REGISTRATION_VERIFY_FAILED);
             return $this->render('auth/verify_email.html.twig', [
                'controller_name' => 'Verifizierung fehlgeschlagen',
            ]);
        }        
    }

    #[Route('/change_password/{token}/{email}', name: 'app_change_password', methods: ['POST', 'GET'])]
    public function changePassword(string $token, string $email, Request $request): Response
    { 
        // checken ob Token und Email existieren, wenn nicht dann leiten wir auf die Hauptseite um
        if (!$this->authService->checkPasswordChangeToken($email, $token)) {
            return $this->redirectToRoute('app_home');
        }

        // temporäres Data Transfer Object Einbinden
        $passwordResetDTO = new PasswordResetDTO();
        // Erstelle das Formular und übergebe den context        
        $form = $this->createForm(PasswordResetFormType::class, $passwordResetDTO);

         // Handle den Submit
        $form->handleRequest($request);
       
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $userData = $form->getData(); 

            if ($this->authService->changeAuthPassword($userData, $form, $email)) {                
                
                $this->flashSessionService->addFlash(FlashSessionService::CHANGE_PASSWORD_SUCCESS);
                return $this->redirectToRoute('app_login');
            }
            
        }

         return $this->render('auth/resetPassword.html.twig', [
                'controller_name' => 'Ändere dein Passwort',
                'resetForm' => $form->createView(),                
            ]);    
    }
}
