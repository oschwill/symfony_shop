<?php
namespace App\Security;

use App\Enum\LogAction;
use App\Logger\CustomLogger;
use App\Service\FlashSessionService;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as HasherUserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
      private HasherUserPasswordHasherInterface $userPasswordHasher, 
      private UrlGeneratorInterface $urlGenerator, 
      private UserProviderInterface $userProvider,
      private CustomLogger $customLogger,
      private FlashSessionService $flashSessionService
      ){}

    public function authenticate(Request $request): Passport
    {
        // Die Daten sind unter 'login_form' im Request gespeichert
        $loginFormData = $request->request->all()['login_form'];
        
        // Sicherstellen, dass login_form Daten vorhanden sind und sicher darauf zugreifen
        $email = $loginFormData['email'] ?? null;
        $password = $loginFormData['password'] ?? null;
        $csrfToken = $request->request->get('_csrf_token') ?? null;
        $rememberMe = $request->request->get('_remember_me', false); 

        // Cross site Token
        $badges = [
            new CsrfTokenBadge('authenticate', $csrfToken)
        ];

        // Cookie Token
        if ($rememberMe) {
            $badges[] = new RememberMeBadge();
        }

        // Speichern der E-Mail in die Session...
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Create a new Passport instance
        return new Passport(
          // UserBadge nutzt unseren UserProvider
            new UserBadge($email, function($userIdentifier) {
                // Wir können dann unsere User Daten returnen (SESSION)
                return $this->userProvider->loadUserByIdentifier($userIdentifier);
            }),
            // CsrfTokenBadge schützt vor Cross-Site Request Forgery (CSRF)-Angriffen
            new PasswordCredentials($password),
            $badges
            // [new CsrfTokenBadge('authenticate', $csrfToken)]
            // [new RememberMeBadge()]
        );
    }    

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        // Loggen!!
         $user = $token->getUser();
         // Handelt es sich um eine Instanz vom Typ UserInterface?
        if ($user instanceof UserInterface) {
            $userEmail = $user->getUserIdentifier(); // 
            $userRole = implode(', ', $user->getRoles());
            
            // Loggen der Benutzerdaten
            $this->customLogger->logEvent(LogAction::USER_LOGIN, [
                'message' => 'User erfolgreich eingeloggt.',
                'user_login' => [
                    'user_email' => $userEmail,
                    'role' => $userRole,
                ],
            ]);
        }

        // dump($user->getRoles());
        // die();

        // Weiterleitung an die ShopSeite
        return new RedirectResponse($targetPath ?? $this->urlGenerator->generate('app_shop'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Loggen und flashen fürs Errorhandling
        $this->flashSessionService->addFlash(FlashSessionService::LOGIN_FAILED);
        $this->customLogger->logEvent(LogAction::USER_LOGIN_ERROR, 'User Login fehlgeschlagen.'. $exception->getMessage());

        // Auf der Login Seite bleiben und Fehler werfen
        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}