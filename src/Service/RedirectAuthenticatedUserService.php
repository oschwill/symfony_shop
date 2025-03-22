<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class RedirectAuthenticatedUserService
{
    public function __construct(
      private SecurityBundleSecurity $security,
       private RouterInterface $router
    )
    {}

    public function redirectIfAuthenticated(string $targetRoute): ?RedirectResponse
    {
      // Wenn User eingeloggt dann weiterleiten an die übergebene Target Route!
        if ($this->security->getUser()) {
            return new RedirectResponse($this->router->generate($targetRoute));
        }

        return null;
    }
}