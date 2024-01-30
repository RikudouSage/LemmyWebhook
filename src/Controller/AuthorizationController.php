<?php

namespace App\Controller;

use App\Attribute\RawDataType;
use App\Entity\Scope;
use App\Entity\User;
use App\Service\RawWebhookParser;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/auth')]
final class AuthorizationController extends AbstractController
{
    /**
     * @param iterable<object> $types
     */
    #[Route('/scopes', name: 'app.authorization.scope_list', methods: [Request::METHOD_GET])]
    public function scopeList(
        #[TaggedIterator('app.raw_data_type')]
        iterable $types,
        AuthorizationCheckerInterface $authorizationChecker,
    ): JsonResponse {
        $user = $this->getUser();
        assert($user instanceof User);

        return new JsonResponse(array_map(function (object $type) use ($user, $authorizationChecker) {
            $reflection = new ReflectionObject($type);
            $attribute = $reflection->getAttributes(RawDataType::class)[0]->newInstance();
            assert($attribute instanceof RawDataType);

            return [
                'scope' => $attribute->table,
                'granted' => $authorizationChecker->isGranted('ROLE_ADMIN')
                    ? true
                    : ($user->findScopeByType($attribute->table)?->isGranted() ?? false),
            ];
        }, [...$types]));
    }

    #[Route('/scope-request/{scope}', name: 'app.authorization.scope_request.create', methods: [Request::METHOD_POST])]
    public function createScopeRequest(
        string $scope,
        EntityManagerInterface $entityManager,
        RawWebhookParser $webhookParser,
    ): JsonResponse {
        $user = $this->getUser();
        assert($user instanceof User);

        if ($user->findScopeByType($scope)) {
            return new JsonResponse([
                'message' => "Request for scope '{$scope}' is already pending approval.",
            ]);
        }

        if (!$webhookParser->isValidTable($scope)) {
            return new JsonResponse([
                'error' => "Invalid scope: '{$scope}'",
            ], Response::HTTP_BAD_REQUEST);
        }

        $scopeEntity = (new Scope())
            ->setScope($scope)
            ->setUser($user)
            ->setGranted(false)
        ;
        $entityManager->persist($scopeEntity);
        $entityManager->flush();

        return new JsonResponse([
            'message' => "Successfully asked for scope '{$scope}'. Please wait for manual review.",
        ], Response::HTTP_CREATED);
    }
}
