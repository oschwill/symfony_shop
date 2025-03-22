<?php

namespace App\Controller;

use App\Form\User\EditUserFormType;
use App\Service\FlashSessionService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

class UserController extends AbstractController
{
    public function __construct(private UserService $userService, private FlashSessionService $flashSessionService, private Security $security )
    {}

    #[Route('/user/{email}', name: 'app_edit_user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editUser(Request $request): Response
    {
        // Erstelle das Formular und übergebe die Entity        
        $form = $this->createForm(EditUserFormType::class);

        // Handle den Submit
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('pictureUpload')->getData();
            // Daten aus der Form holen
            $user = $form->getData();  
            $hasChangedPassword = false;
        
            if ($this->userService->editUserFN($user, $pictureFile, $form, $hasChangedPassword)) {
                $this->flashSessionService->addFlash(FlashSessionService::USER_EDIT_SUCCESS);
                if ($hasChangedPassword) {
                    // Hier ein Stopp machen
                    $this->flashSessionService->addFlash(FlashSessionService::CHANGE_PASSWORD_SUCCESS);
                    
                    return $this->redirectToRoute('app_show_logout_alert');
                }

                 $userEmail = $user->getEmail();

                // Weiterleitung zur Route mit der E-Mail als Parameter
                return $this->redirectToRoute('app_edit_user', ['email' => $userEmail]);
            }
        }

        return $this->render('user/editUserData.html.twig', [
            'controller_name' => 'Meine Daten',
            'userForm' => $form->createView(),
        ]);
    }

    #[Route('/users', name: 'app_users')]
    #[IsGranted('ROLE_USER')]
    public function showAllUsers(): Response
    {
        $users = $this->userService->showAllUsers();

        return $this->render('user/showAllUsers.html.twig', [
            'controller_name' => 'Alle Benutzer',
            'users' => $users,
        ]);
    }

    #[Route('/show-logout-alert', name: 'app_show_logout_alert')]
    public function showLogoutAlert(): Response
    {
        return $this->render('user/show_logout_alert.html.twig', [
            'controller_name' => 'Passwort geändert'
        ]);
    }
}
