<?php

namespace App\Controller;

use App\Entity\Scope;
use App\Entity\User;
use App\Service\RawWebhookParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth')]
final class AuthorizationController extends AbstractController
{
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
