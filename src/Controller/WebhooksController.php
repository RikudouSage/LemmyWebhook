<?php

namespace App\Controller;

use App\Dto\Request\ImportWebhooksRequest;
use App\Exception\InvalidImportException;
use App\Service\WebhookImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/webhooks')]
final class WebhooksController extends AbstractController
{
    #[Route('/import', name: 'api.webhooks.import', methods: [Request::METHOD_POST])]
    public function import(
        #[MapRequestPayload] ImportWebhooksRequest $request,
        WebhookImporter $importer,
    ): JsonResponse {
        try {
            $importer->import($request->configuration);

            return new JsonResponse(status: Response::HTTP_NO_CONTENT);
        } catch (InvalidImportException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
