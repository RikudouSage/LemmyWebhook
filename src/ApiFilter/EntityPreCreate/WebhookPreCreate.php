<?php

namespace App\ApiFilter\EntityPreCreate;

use App\Entity\Scope;
use App\Entity\User;
use App\Entity\Webhook;
use Rikudou\JsonApiBundle\ApiEntityEvents;
use Rikudou\JsonApiBundle\Events\EntityPreCreateEvent;
use Rikudou\JsonApiBundle\Events\EntityPreUpdateEvent;
use Rikudou\JsonApiBundle\Exception\JsonApiErrorException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[AsEventListener(event: ApiEntityEvents::PRE_CREATE, method: 'preCreate')]
#[AsEventListener(event: ApiEntityEvents::PRE_UPDATE, method: 'preUpdate')]
final readonly class WebhookPreCreate
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private Security $security,
    ) {
    }

    public function preCreate(EntityPreCreateEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Webhook) {
            return;
        }
        $this->handle($entity);
    }

    public function preUpdate(EntityPreUpdateEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Webhook) {
            return;
        }
        $this->handle($entity);
    }

    private function handle(Webhook $entity): void
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->security->getUser();
        assert($user instanceof User);
        $entity->setUser($user);

        $approvedScopes = [...$user->getScopes()
            ->filter(fn (Scope $scope) => $scope->isGranted())
            ->map(fn (Scope $scope) => $scope->getScope())];
        if (!in_array($entity->getObjectType(), $approvedScopes, true)) {
            throw new JsonApiErrorException("You don't have access to the '{$entity->getObjectType()}' scope.", Response::HTTP_FORBIDDEN);
        }
    }
}
