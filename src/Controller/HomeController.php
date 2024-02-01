<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home', methods: [Request::METHOD_GET])]
    public function home(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route('/flags', name: 'app.flags', methods: [Request::METHOD_GET])]
    public function flags(
        #[Autowire('%app.auth.api_registration_enabled%')]
        bool $registrationEnabled,
        AuthorizationCheckerInterface $authorizationChecker,
        Connection $connection
    ): JsonResponse {
        $flags = [
            'registrations' => $registrationEnabled,
        ];
        if ($this->getUser()) {
            $flags = [...$flags, ...[
                'admin' => $authorizationChecker->isGranted('ROLE_ADMIN'),
            ]];
        }

        return new JsonResponse($flags);
    }
}
