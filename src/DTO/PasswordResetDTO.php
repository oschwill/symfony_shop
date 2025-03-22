<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetDTO
{
    #[Assert\NotBlank(message: "Das Passwort darf nicht leer sein.")]
    #[Assert\Length(min: 6, minMessage: "Das Passwort muss mindestens {{ limit }} Zeichen lang sein.")]
    private $password;

    #[Assert\NotBlank(message: "Bitte wiederholen Sie das Passwort.")]
    #[Assert\EqualTo(propertyPath: "password", message: "Die Passwörter stimmen nicht überein.")]
    private $passwordRepeat;

    // Getter und Setter
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPasswordRepeat(): ?string
    {
        return $this->passwordRepeat;
    }

    public function setPasswordRepeat(string $passwordRepeat): self
    {
        $this->passwordRepeat = $passwordRepeat;
        return $this;
    }
}