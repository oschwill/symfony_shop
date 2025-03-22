<?php
namespace App\Form\Events;

use App\Enum\UserRole;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserFormModifier
{
    public static function modifyForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $context = $options['context'];

            // Remove fields we not need
            if ($context === 'login') {
                $form->remove('firstName');
                $form->remove('lastName');
                $form->remove('userName');
                $form->remove('pictureUpload');
                $form->remove('role');
                $form->remove('oldPassword');
                $form->add('password', PasswordType::class, [
                  'label' => 'Passwort:',
                  'required' => false,
                ]);
              } elseif ($context === 'registration') {
                $form->remove('role');
                $form->remove('oldPassword');
                $form->add('password', PasswordType::class, [
                  'label' => 'Passwort:',
                  'required' => false,
                ]);

                $form->add('passwordRepeat', PasswordType::class, [
                    'label' => 'Passwort wiederholen:',
                    'required' => false,
                    'mapped' => false,
                ]);
              } elseif ($context === 'edit') {                
                //
                $form->add('password', PasswordType::class, [
                    'label' => 'Neues Passwort:',
                    'required' => false, // Passwort ist optional, außer es wird ausgefüllt
                    'constraints' => [
                        new Callback([
                            'callback' => [self::class, 'validatePassword'],
                        ]),
                    ],
                ]);

                $form->add('passwordRepeat', PasswordType::class, [
                    'label' => 'Passwort wiederholen:',
                    'required' => false,
                    'mapped' => false,
                    'constraints' => [
                        new Callback([
                            'callback' => [self::class, 'validatePasswordRepeat'],
                        ]),
                    ],
                ]);
             }
        });
    }

    // Passowrt auf der editUser Route seperat validieren    
    public static function validatePassword($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $passwordRepeat = $form->has('passwordRepeat') ? $form->get('passwordRepeat')->getData() : null;

        if (!empty($value) || !empty($passwordRepeat)) {
            if (empty($value)) {
                $context->buildViolation('Bitte geben Sie ein Passwort ein')
                    ->addViolation();
            } elseif (strlen($value) < 6) {
                $context->buildViolation('Das Passwort muss mindestens 6 Zeichen lang sein')
                    ->addViolation();
            }
        }
    }

    // PasswordWdh auf der editUser Route seperat validieren
    public static function validatePasswordRepeat($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $password = $form->get('password')->getData();

        if (!empty($password) || !empty($value)) {
            if (empty($value)) {
                $context->buildViolation('Bitte wiederholen Sie das Passwort')
                    ->addViolation();
            } elseif ($password !== $value) {
                $context->buildViolation('Die Passwörter stimmen nicht überein')
                    ->addViolation();
            }
        }
    }
}