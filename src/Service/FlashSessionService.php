<?php 
namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Yaml\Yaml;

class FlashSessionService
{
    public const REGISTRATION_SUCCESS = 'registration_success';
    public const REGISTRATION_ERROR = 'registration_error';
    public const REGISTRATION_VERIFY_SUCCESS = 'registration_verify_success';
    public const REGISTRATION_VERIFY_FAILED = 'registration_verify_failed';
    public const LOGIN_FAILED = 'login_failed';
    public const CHANGE_PASSWORD_SUCCESS = 'change_password_success';
    public const USER_EDIT_SUCCESS = 'user_edit_success';

    private FlashBagInterface $flashBag;
    private array $messages;

    public function __construct(private RequestStack $requestStack)
    {
        $this->messages = Yaml::parseFile(__DIR__.'/../../config/flash_messages.yaml')['flash_messages'];

        // Zugriff auf die FlashBag über die aktuelle Anfrage im RequestStack
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->hasSession()) {
            /** @var \Symfony\Component\HttpFoundation\Request $request */
            $this->flashBag = $request->getSession()->getFlashBag(); // 
        } else {
            throw new \RuntimeException('No active session found.');
        }
    }

    public function addFlash(string $type): void
    {
        if ($this->flashBag) {
            $message = $this->messages[$type] ?? 'Unknown message type';
            $this->flashBag->add($type, $message);
        }       
    }

    public function getFlash(string $type): array
    {
        return $this->flashBag->get($type);
    }
}