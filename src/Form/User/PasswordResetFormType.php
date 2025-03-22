<?php

namespace App\Form\User;

use App\DTO\PasswordResetDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordResetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Neues Passwort:',
                'attr' => [
                    'class' => 'p-3 form-control fs-5',
                    'changepassword-form-field' => 'password',
                    'placeholder' => 'Dein Passwort'
                ]
            ])
            ->add('passwordRepeat', PasswordType::class, [
                'label' => 'Passwort wiederholen:',
                'attr' => [
                    'class' => 'p-3 form-control fs-5',
                    'changepassword-form-field' => 'passwordRepeat',
                    'placeholder' => 'Dein Passwort wiederholen'
                ]
            ])
            ->add('generalError', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-error'],
                'error_bubbling'=>false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PasswordResetDTO::class,
        ]);
    }
}