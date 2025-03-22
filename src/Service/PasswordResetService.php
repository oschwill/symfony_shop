<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PasswordResetService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private MailerInterface $mailer
    ) {}

    public function sendPasswordResetEmail(User $user): void
    {
        $resetToken = bin2hex(random_bytes(32));
        $user->setPasswordResetToken($resetToken); // Assume the User entity has this method
        // You may want to persist the token to the database here

        $resetUrl = $this->urlGenerator->generate('app_reset_password', [
            'token' => $resetToken
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from('noreply@yourapp.com')
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->text("Click here to reset your password: $resetUrl");

        $this->mailer->send($email);
    }
}