<?php
namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

class PasswordResetController extends AbstractController
{
    private $limiter;

    public function __construct(private AuthService $authService, private RateLimiterFactory $passwordResetLimiter)
    {}

    #[Route('/api/v1/password-reset', name: 'api_password_reset', methods: ['POST'])]
    public function requestPasswordReset(Request $request, UserRepository $userRepository): JsonResponse
    {     
        try {
            // Die API darf nur einmal pro Minute gezündet werden
            $this->limiter = $this->passwordResetLimiter->create($request->getClientIp());
            if (!$this->limiter->consume(1)->isAccepted()) {
                throw new \Exception('Zu viele Anfragen. Bitte versuchen Sie es später erneut', 429);
            }
            // Unsere Daten als Json abfangen
            $data = json_decode($request->getContent(), true);
            $email = $data['email'] ?? null;
    
            if (!$email) {
                return new JsonResponse(['error' => 'Bitte geben Sie eine Emailadresse an'], 400);
            }
    
            $user = $userRepository->findOneByEmail($email);
    
            if (!$user) {
                // Keinen Hinweis drauf geben ob die Email zum user nicht existiert!!
                return new JsonResponse(['message' => 'Die Email wurde erfolgreich versendet'], 200);
            }
    
            if (!$this->authService->runForgotPasswordProcess($user)) {
                return new JsonResponse(['error' => 'Fehler beim Versenden der Email!'], 401);
            } 
            
            return new JsonResponse(['message' => 'Die Email wurde erfolgreich versendet']);
           
        } catch (\Exception $e) {  
            $statusCode = $e->getCode() ?: 500;       
            return new JsonResponse(['error' => $e->getMessage()],  $statusCode);
        }
    }
}