<?php
namespace App\Utility;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class ErrorHandler
{
    public function __construct() {}

    // Error Message erzeugen
    public function addErrorToForm(Form $form, string $property, string $message): void
    {
        $form->get($property)?->addError(new FormError($message));
    }

    /**
     * TO BE CONTINUED...?
     */
}