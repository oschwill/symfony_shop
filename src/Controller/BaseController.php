<?php
namespace App\Controller;

use App\Service\RedirectAuthenticatedUserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class BaseController extends AbstractController
{
    protected ?RedirectResponse $redirectResponse = null;

    public function __construct(RedirectAuthenticatedUserService $redirectAuthenticatedUserService, RequestStack $requestStack)
    {
        // aktuelle Route holen
        $currentRoute = $requestStack->getCurrentRequest()->attributes->get('_route');

        // Hier hauen wir alle Routen rein auf die ein eingeloggter User weitergeleitet werden soll
        $routesToCheck = ['app_login', 'app_register', 'app_home'];

        // Wenn aktuelle Route sich in routesToCheck befindet soll er die Weiterleitung einleiten...
        if (in_array($currentRoute, $routesToCheck)) {
            $this->redirectResponse = $redirectAuthenticatedUserService->redirectIfAuthenticated('app_shop');
        }
    }
}