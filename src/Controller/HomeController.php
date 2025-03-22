<?php

namespace App\Controller;

use App\Service\RedirectAuthenticatedUserService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends BaseController
{
    public function __construct(
        private RedirectAuthenticatedUserService $redirectAuthenticatedUserService,
        private RequestStack $requestStack
    )
    {        
        // Parentclass Konstruktor aufrufen um die Weiterleitung zu setzen
        parent::__construct($redirectAuthenticatedUserService, $requestStack);
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // eingeloggter User
        if ($this->redirectResponse) {
            return $this->redirectResponse;
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'Shop',
            'welcome_message' => 'Willkommen'
        ]);
    }
}
