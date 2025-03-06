<?php

namespace Bocum\EventSubscriber;

use Bocum\Event\UserRegisteredEvent;
use Bocum\Service\MailerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerService $mailerService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::class => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = $event->getUser();
        $this->mailerService->sendEmail($user->getEmail(), 'Welcome! ' . $user->getEmail(), 'Thank you for registering ' . $user->getEmail());
    }
}
