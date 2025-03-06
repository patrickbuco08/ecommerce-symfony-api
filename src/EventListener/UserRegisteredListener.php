<?php

namespace Bocum\EventListener;

use Bocum\Event\UserRegisteredEvent;
use Bocum\Service\MailerService;

class UserRegisteredListener
{
    public function __construct(
        private MailerService $mailerService
    ) {}

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = $event->getUser();
        $this->mailerService->sendEmail($user->getEmail(), 'Welcome!' . $user->getEmail(), 'Thank you for registering!' . $user->getEmail());
    }
}
