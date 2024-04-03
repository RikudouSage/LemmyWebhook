<?php

namespace App\Command;

use App\Entity\Webhook;
use App\Message\TriggerCallbackMessage;
use App\Repository\WebhookRepository;
use App\Service\DuplicateChecker;
use App\Service\ExpressionParser;
use App\Service\RawWebhookParser;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('app:postgres-webhook-listener')]
final class PostgresWebhookListenerCommand extends Command
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly RawWebhookParser $parser,
        private readonly WebhookRepository $webhookRepository,
        private readonly ExpressionParser $expressionParser,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
        private readonly DuplicateChecker $duplicateChecker,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->pdo->exec('LISTEN "rikudou_event"');
        while (true) {
            $result = $this->pdo->pgsqlGetNotify(PDO::FETCH_ASSOC, 1_000);
            if (!$result) {
                if ($output->isVeryVerbose()) {
                    $io->comment('No event received');
                }
                continue;
            }
            if (is_numeric($result['payload'])) {
                $query = $this->pdo->prepare('select payload from rikudou_webhooks_large_payloads where id = :id');
                $query->bindParam('id', $result['payload']);
                $query->execute();
                $payload = $query->fetch(PDO::FETCH_ASSOC);
                $payload = json_decode($payload['payload'], true);
                $query = $this->pdo->prepare('delete from rikudou_webhooks_large_payloads where id = :id');
                $query->bindParam('id', $result['payload']);
                $query->execute();
            } else {
                $payload = json_decode($result['payload'], true);
            }
            $data = $this->parser->parse($payload);

            if ($this->duplicateChecker->isDuplicate($data)) {
                continue;
            }
            $this->duplicateChecker->markAsProcessed($data);

            /** @var Webhook[] $webhooks */
            $webhooks = [
                ...$this->webhookRepository->findBy([
                    'objectType' => $data->table,
                    'operation' => $data->operation,
                    'enabled' => true,
                ]),
                ...$this->webhookRepository->findBy([
                    'objectType' => $data->table,
                    'operation' => null,
                    'enabled' => true,
                ]),
            ];
            if (!count($webhooks)) {
                if ($output->isVeryVerbose()) {
                    $io->comment("No webhook for '{$data->operation->value}' on '{$data->table}', skipping.");
                }
                continue;
            }

            if ($output->isVeryVerbose()) {
                $io->comment(sprintf("Found %d webhooks for '%s' on '%s'", count($webhooks), $data->operation->value, $data->table));
            }
            $i = 1;
            foreach ($webhooks as $webhook) {
                if ($output->isVeryVerbose()) {
                    $io->comment("Processing webhook #{$i}");
                }

                if (!$webhook->getUrl()) {
                    $io->error("Webhook with id '{$webhook->getId()}' does not have a URL, skipping.");
                    continue;
                }

                $filterExpression = $webhook->getFilterExpression();
                try {
                    if ($filterExpression !== null && !$this->expressionParser->evaluate($filterExpression, ['data' => $data, 'triggering_user' => $webhook->getUser()?->getId()])) {
                        if ($output->isVeryVerbose()) {
                            $io->comment("The filter expression did not evaluate to true, skipping webhook {$i}");
                        }
                        continue;
                    }
                } catch (SyntaxError) {
                    $io->error("There's a syntax error in filter expression for webhook with ID {$webhook->getId()}");
                    continue;
                }

                $this->messageBus->dispatch(new TriggerCallbackMessage($webhook, $data));
                if ($output->isVerbose()) {
                    $io->comment("Sending webhook with id '{$webhook->getId()}' to message bus");
                }

                ++$i;
            }
            $this->entityManager->clear();
        }
    }
}
