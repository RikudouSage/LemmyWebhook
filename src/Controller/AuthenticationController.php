<?php

namespace App\Controller;

use App\Dto\Request\RefreshTokenRequest;
use App\Dto\Request\LoginRequest;
use App\Dto\Request\RegisterRequest;
use App\Entity\Scope;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\AuthenticationManager;
use App\Service\RawWebhookParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth')]
final class AuthenticationController extends AbstractController
{
    #[Route('/register', name: 'app.auth.register', methods: [Request::METHOD_POST])]
    public function register(
        #[Autowire('%app.auth.api_registration_enabled%')]
        bool                              $enabled,
        EntityManagerInterface            $entityManager,
        #[MapRequestPayload] RegisterRequest $request,
        UserPasswordHasherInterface       $passwordHasher,
        RawWebhookParser $webhookParser,
    ): JsonResponse {
        if (!$enabled) {
            throw $this->createNotFoundException();
        }

        $user = (new User())
            ->setUsername($request->username)
            ->setPassword($passwordHasher->hashPassword(new User(), $request->password))
            ->setRoles(['ROLE_USER'])
            ->setEnabled(false);
        $entityManager->persist($user);

        foreach ($request->scopes as $scope) {
            if (!$webhookParser->isValidTable($scope)) {
                return new JsonResponse([
                    'error' => "The scope is not valid: '{$scope}'",
                ], Response::HTTP_BAD_REQUEST);
            }

            $scopeEntity = (new Scope())
                ->setScope($scope)
                ->setUser($user)
                ->setGranted(false)
            ;
            $entityManager->persist($scopeEntity);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Your account has been created, you must now wait for manual approval.',
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'app.auth.login', methods: [Request::METHOD_POST])]
    public function login(
        UserRepository                    $userRepository,
        #[MapRequestPayload] LoginRequest $request,
        UserPasswordHasherInterface       $passwordHasher,
        AuthenticationManager             $authenticationManager,
    ): JsonResponse {
        if (!$user = $userRepository->findOneBy([
            'username' => $request->username,
        ])) {
            throw new BadRequestHttpException('User not found');
        }

        if (!$passwordHasher->isPasswordValid($user, $request->password)) {
            throw new BadRequestHttpException('Invalid password');
        }

        if (!$user->isEnabled()) {
            throw new BadRequestHttpException('Account not enabled');
        }

        $authToken = $authenticationManager->createAuthenticationToken($user);
        $refreshToken = $authenticationManager->createRefreshToken($user);

        return new JsonResponse([
            'token' => (string) $authToken,
            'expires' => $authToken->getValidUntil()?->format('c'),
            'refreshToken' => (string) $refreshToken,
        ]);
    }

    #[Route('/refresh', name: 'app.auth.refresh', methods: [Request::METHOD_POST])]
    public function refresh(
        #[MapRequestPayload] RefreshTokenRequest $request,
        AuthenticationManager $authenticationManager,
        RefreshTokenRepository $refreshTokenRepository,
    ): JsonResponse {
        $refreshToken = $refreshTokenRepository->findOneBy([
            'token' => $request->refreshToken,
        ]);
        if ($refreshToken === null) {
            return new JsonResponse([
                'error' => 'Invalid token',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $refreshToken->getUser();
        $authToken = $authenticationManager->createAuthenticationToken($user);
        $newRefreshToken = $authenticationManager->createRefreshToken($user);
        $authenticationManager->invalidateRefreshToken($refreshToken);

        return new JsonResponse([
            'token' => (string) $authToken,
            'expires' => $authToken->getValidUntil()?->format('c'),
            'refreshToken' => (string) $newRefreshToken,
        ]);
    }
}
