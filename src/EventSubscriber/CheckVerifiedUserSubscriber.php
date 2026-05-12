<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
Use App\Entity\User;
use App\Security\AccountNotVerifiedAuthenticationException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    public function __construct(RouterInterface $router, private RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }
    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();
        $user = $passport->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type.');
        }
        
        if (!$user->isVerified()) {
            throw new AccountNotVerifiedAuthenticationException();
        }
    }
    public function onLoginFailure(LoginFailureEvent $event)
    {
        if (!$event->getException() instanceof AccountNotVerifiedAuthenticationException) {
            return;
        }
        $request = $event->getRequest();
        $email = $request->getSession()->get(SecurityRequestAttributes::LAST_USERNAME);

        $response = new RedirectResponse($this->router->generate('app_verify_resend_email', ['email' => $email]));
        $event->setResponse($response);
    }
    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }
}