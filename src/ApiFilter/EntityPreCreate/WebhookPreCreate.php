<?php

namespace App\ApiFilter\EntityPreCreate;

use App\Entity\User;
use App\Entity\Webhook;
use Rikudou\JsonApiBundle\ApiEntityEvents;
use Rikudou\JsonApiBundle\Events\EntityPreCreateEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsEventListener(event: ApiEntityEvents::PRE_CREATE, method: 'preCreate')]
final readonly class WebhookPreCreate
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private Security $security,
    ) {
    }

    public function preCreate(EntityPreCreateEvent $event): void
    {
        if (!$event->getEntity() instanceof Webhook) {
            return;
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);
        $entity = $event->getEntity();
        $entity->setUser($user);
    }
}
