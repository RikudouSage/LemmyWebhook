<?php

namespace App\Command;

use App\Dto\RawData\RawData;
use App\Enum\DatabaseOperation;
use App\Message\TriggerCallbackMessage;
use App\Repository\WebhookRepository;
use App\Service\RawWebhookParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Component\Uid\Uuid;

#[AsCommand('app:debug:webhook')]
final class DebugWebhookCommand extends Command
{
    public function __construct(
        private readonly WebhookRepository   $webhookRepository,
        private readonly MessageBusInterface $messageBus, private readonly RawWebhookParser $rawWebhookParser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('webhook-id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $webhook = $this->webhookRepository->find($input->getArgument('webhook-id'));
        if (!$webhook) {
            $io->error('Webhook not found');
            return Command::FAILURE;
        }

        $rawData = $this->rawWebhookParser->parse([
            'timestamp' => date('c'),
            'operation' => DatabaseOperation::Insert->value,
            'schema' => 'public',
            'table' => 'comment',
            'data' => [
                'id' => random_int(1, PHP_INT_MAX),
            ],
            'previous' => null,
        ]);
        $this->messageBus->dispatch(new TriggerCallbackMessage($webhook, $rawData), [
            new TransportNamesStamp('sync'),
        ]);

        return Command::SUCCESS;
    }
}
