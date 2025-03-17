<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ExpressionParser;
use App\Service\RawWebhookParser;
use DateTimeImmutable;
use PDO;
use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:debug_expression')]
class DebugExpressionCommand extends Command
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly RawWebhookParser $rawWebhookParser,
        private readonly ExpressionParser $expressionParser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'expression',
            description: 'The expression to debug',
        );
        $this->addArgument(
            name: 'target-type',
            description: 'The type of the object to debug',
        );
        $this->addArgument(
            name: 'target-id',
            description: 'The id of the object to debug',
        );
        $this
            ->addOption('timestamp', mode: InputOption::VALUE_REQUIRED, default: (new DateTimeImmutable())->format('c'))
            ->addOption('operation', mode: InputOption::VALUE_REQUIRED, default: 'INSERT')
            ->addOption('schema', mode: InputOption::VALUE_REQUIRED, default: 'public')
            ->addOption('triggering-user', mode: InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expression = $input->getArgument('expression');
        $targetType = $input->getArgument('target-type');
        $targetId = (int) $input->getArgument('target-id');
        $triggeringUserId = $input->getOption('triggering-user');

        $rawData = [
            'timestamp' => (new DateTimeImmutable($input->getOption('timestamp')))->format('c'),
            'operation' => $input->getOption('operation'),
            'schema' => $input->getOption('schema'),
            'table' => $targetType,
        ];

        if ($targetType && $targetId) {
            $statement = $this->pdo->query("select * from {$targetType} where id = {$targetId}");
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $rawData['data'] = $result;
            $object = $this->rawWebhookParser->parse($rawData);
        }

        $value = $this->expressionParser->evaluate($expression, [
            'data' => $object ?? new stdClass(),
            'triggering_user' => $triggeringUserId,
        ]);

        $output->writeln("Result:");
        $output->writeln(json_encode($value, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }
}
