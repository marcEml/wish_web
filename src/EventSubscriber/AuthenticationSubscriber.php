<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private array $publicRoutes;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;

        // Define the route names that don't require authentication
        $this->publicRoutes = [
            'app_landing',
            'app_authentication_login',
            'app_authentication_signin',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ensure you act only on the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $route = $request->attributes->get('_route');

        // If public or no route, stop execution
        if (!$route || in_array($route, $this->publicRoutes)) {
            return;
        }

        $sessionCookie = $request->cookies->get('user_session');

        if (!$sessionCookie) {
            // $loginUrl = $this->router->generate('app_authentication_login');
            // $event->setResponse(new RedirectResponse($loginUrl));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
