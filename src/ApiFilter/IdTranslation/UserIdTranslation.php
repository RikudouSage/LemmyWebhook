<?php

namespace App\ApiFilter\IdTranslation;

use App\Entity\User;
use Rikudou\JsonApiBundle\ApiEvents;
use Rikudou\JsonApiBundle\Events\RouterPreroutingEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(ApiEvents::PREROUTING, method: 'prerouting')]
final readonly class UserIdTranslation
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function prerouting(RouterPreroutingEvent $event): void
    {
        if ($event->getController()->getClass() === User::class && $event->getId() === 'me') {
            $user = $this->security->getUser();
            if (!$user instanceof User) {
                return;
            }
            $event->setId($user->getId());
        }
    }
}
