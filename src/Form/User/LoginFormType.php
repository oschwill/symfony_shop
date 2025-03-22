<?php
namespace App\Form\User;

use App\Form\User\UserFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends UserFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Form builden anhand des Parent Builders
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Erst den Parent Resolver mal aufrufen, sonst klappt et nicht mit dem überschreiben
        parent::configureOptions($resolver); 
        // Und dann überschreiben!! Wir setzen den context registration
        $resolver->setDefaults([
            'context' => 'login',
            'validation_groups' => ['login'],
        ]);
    }
}