<?php
namespace App\Form\User;

use App\Entity\User;
use App\Enum\UserRole;
use App\Form\Events\UserFormModifier;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserFormType extends AbstractType
{
    public function __construct(private Security $security)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
      // Das Role Feld ist abhängig von den eingeloggten User daten
       $user = $this->security->getUser();

       $userRole = $user ? $user->getRoles()[0] : 'ROLE_USER';

      // Bauen unseren Parentbuilder für die User Entity!
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Vorname:',
                'required' => false
            ])
             ->add('lastName', TextType::class, [
                'label' => 'Nachname:',
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Addresse:',
                'required' => false,
            ])
            ->add('oldPassword', PasswordType::class, [
                'label' => 'altes Passwort:',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'p-3 fs-5',
                    'user-form-field' => 'oldPassword',
                    'disabled' => 'disabled', // Macht das Feld nur lesbar
                    'value' => 'Blabliblubjoa!', // Setzt den Wert
                ]
            ])
            ->add('pictureUpload', FileType::class, [
                'label' => 'Profilbild (optional):',
                'required' => false,
                'mapped' => false, // Wird nicht direkt mit der Entität gemappt, der Pfad wird im Laufe des Prozesses erzeugt oder eben nicht
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Bitte uploaden Sie Ihr Profilbild nur als JPG oder PNG',
                    ]),
                ],
            ])
            ->add('generalError', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-error'],
                'error_bubbling'=>false
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Berechtigung:',
                'choices' => [
                    'Admin' => UserRole::ADMIN,
                    'User' => UserRole::USER,
                ],
                'choice_value' => function (?UserRole $role) { // Als String returnen 
                    return $role ? $role->value : '';
                },
                'choice_label' => function (UserRole $role) {
                    return $role->name;
                },
                'required' => false,
                'placeholder' => false,
                'data' => UserRole::tryFrom($userRole) ?: null, // als Enum für das formHandling übergeben sonst nix funzen tut!!
            ]);

        // Hier implementieren wir unser Event
        UserFormModifier::modifyForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'context' => 'default',
        ]);
    }
}