<?php
namespace App\Service;

use App\Entity\User;
use App\Logger\CustomLogger;
use App\Enum\LogAction;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Environment;

class EmailService
{

    public function __construct(private MailerInterface $mailer, private Environment $twig, private CustomLogger $customLogger)
    {}

    public function sendConfirmationEmail(User $user): ?string
    {
      // Holen uns das Template
        $htmlContent = $this->twig->render('emails/registration_confirmation.html.twig', [
            'user' => $user
        ]);

        // Email Header bauen
        $emailDetails = [
            'from' => 'no-reply@myshop.com',
            'to' => $user->getEmail(),
            'subject' => 'Bestätigung Ihrer Registrierung',
            'html' => $htmlContent,
        ];        

        return $this->sendMail($emailDetails, 'Registrierungs-email gesendet:', 'Fehler beim Senden der Registrierungs Email: ');
    }

    public function sendPasswordResetEmail(User $user, string $token): ?string
    {
        // Holen uns das Template
        $htmlContent = $this->twig->render('emails/password_forgot.html.twig', [
            'user' => $user,
            'passwordToken' => $token
        ]);

        // Email Header mL WIEDR bauen
        $emailDetails = [
            'from' => 'no-reply@myshop.com',
            'to' => $user->getEmail(),
            'subject' => 'Passwort resetten',
            'html' => $htmlContent,
        ];   

        return $this->sendMail($emailDetails, 'Passwort Resetten Email gesendet', 'Fehler beim Senden der Password Resett Email');
    }

    private function sendMail(array $emailDetails, string $logTextSuccess, string $logTextFailed): ?string {
      $email = (new TemplatedEmail())
            ->from($emailDetails['from'])
            ->to($emailDetails['to'])
            ->subject($emailDetails['subject'])
            ->html($emailDetails['html']);

        try {
            $this->mailer->send($email);

            $emailDetailsString = print_r($emailDetails, true);
            $this->customLogger->logEvent(LogAction::EMAIL_SENT, $logTextSuccess . $emailDetailsString);

            return null;
        } catch (TransportExceptionInterface $e) {
            $this->customLogger->logEvent(LogAction::CRITICAL_ERROR, $logTextFailed . $e->getMessage());
            return $e->getMessage();
        }
    }
}