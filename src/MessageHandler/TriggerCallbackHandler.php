<?php

namespace App\MessageHandler;

use App\Entity\WebhookResponse;
use App\Message\CleanupExpiredRowsMessage;
use App\Message\TriggerCallbackMessage;
use App\Repository\WebhookRepository;
use App\Service\ExpressionParser;
use App\Service\Signer\WebhookSignerFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class TriggerCallbackHandler
{
    private const string NAMESPACE = '71e6fa30-3fb7-419c-974b-b5b9a750e4f1';

    public function __construct(
        private HttpClientInterface $httpClient,
        private ExpressionParser $expressionParser,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
        private WebhookRepository $webhookRepository,
        private WebhookSignerFactory $webhookSigner,
    ) {
    }

    public function __invoke(TriggerCallbackMessage $message): void
    {
        if (random_int(0, 100) === 50) {
            $this->messageBus->dispatch(new CleanupExpiredRowsMessage(), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }

        $webhook = $this->webhookRepository->find($message->webhook->getId())
            ?? throw new LogicException("Could not find the webhook with ID {$message->webhook->getId()}");
        $data = $message->data;

        if (($enhancedFilter = $webhook->getEnhancedFilter()) && !$this->expressionParser->evaluate($enhancedFilter, ['data' => $data, 'triggering_user' => $webhook->getUser()?->getId()])) {
            error_log('Data did not pass enhanced filter');
            return;
        }

        $requestOptions = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
        if ($headers = $webhook->getHeaders()) {
            $requestOptions['headers'] = array_merge($requestOptions['headers'], $headers);
        }

        $bodyExpression = $webhook->getBodyExpression();
        if ($bodyExpression !== null) {
            $requestOptions['body'] = json_encode($this->expressionParser->evaluate($bodyExpression, ['data' => $data, 'triggering_user' => $webhook->getUser()?->getId()]));
        }

        $requestOptions['headers']['webhook-id'] = (string) Uuid::v5(
            Uuid::fromString(self::NAMESPACE),
            base64_encode(
                serialize($message->data) . serialize($message->webhook),
            ),
        );
        $requestOptions['headers']['webhook-timestamp'] = time();

        if ($signer = $this->webhookSigner->findSigner($webhook->getSigningMode())) {
            $requestOptions['headers']['webhook-signature'] = $signer->sign(
                signingKey: $webhook->getSigningKey() ?? throw new LogicException('Cannot sign webhook without a signing key'),
                messageId: $requestOptions['headers']['webhook-id'],
                body: $requestOptions['body'],
                timestamp: $requestOptions['headers']['webhook-timestamp'],
            );
        }

        $response = $this->httpClient->request(
            $webhook->getMethod()->value,
            $webhook->getUrl(),
            $requestOptions,
        );

        $responseLog = (new WebhookResponse())
            ->setBody($response->getContent($webhook->shouldRetryOnFailure()))
            ->setStatusCode($response->getStatusCode())
            ->setWebhook($webhook)
            ->setValidUntil(new DateTimeImmutable('+6 hours'))
            ->setHeaders($response->getHeaders($webhook->shouldRetryOnFailure()))
        ;
        if ($webhook->shouldLogResponses()) {
            $this->entityManager->persist($responseLog);
            $this->entityManager->flush();
        }

        error_log("Webhook has been sent, status code: {$responseLog->getStatusCode()}");
    }
}
