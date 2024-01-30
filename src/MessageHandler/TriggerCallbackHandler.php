<?php

namespace App\MessageHandler;

use App\Message\CleanupExpiredTokensMessage;
use App\Message\TriggerCallbackMessage;
use App\Service\ExpressionParser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class TriggerCallbackHandler
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ExpressionParser $expressionParser,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(TriggerCallbackMessage $message): void
    {
        if (random_int(0, 100) === 50) {
            $this->messageBus->dispatch(new CleanupExpiredTokensMessage(), [
                new DispatchAfterCurrentBusStamp(),
            ]);
        }

        $webhook = $message->webhook;
        $data = $message->data;

        if (($enhancedFilter = $webhook->getEnhancedFilter()) && !$this->expressionParser->evaluate($enhancedFilter, ['data' => $data])) {
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
            $requestOptions['json'] = $this->expressionParser->evaluate($bodyExpression, ['data' => $data]);
        }

        $this->httpClient->request(
            $webhook->getMethod()->value,
            $webhook->getUrl(),
            $requestOptions,
        );
        error_log('Webhook has been sent');
    }
}
