<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home', methods: [Request::METHOD_GET])]
    public function home(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
        ]);
    }
}
