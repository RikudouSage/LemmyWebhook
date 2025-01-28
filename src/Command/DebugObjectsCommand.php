<?php

namespace App\Command;

use App\Service\InstallableSqlObjectManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand('app:debug-objects')]
final class DebugObjectsCommand extends Command
{
    public function __construct(
        #[TaggedIterator('app.installable_sql')]
        private iterable $objects,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('install', mode: InputOption::VALUE_NONE, description: 'Dump install queries')
            ->addOption('uninstall', mode: InputOption::VALUE_NONE, description: 'Dump uninstall queries')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queries = [];

        $manager = new InstallableSqlObjectManager(
            $this->objects,
            new class ($queries) extends Connection
            {
                private array $queries;

                public function __construct(array &$queries)
                {
                    $this->queries = &$queries;
                }

                public function executeStatement($sql, array $params = [], array $types = [])
                {
                    $this->queries[] = $sql;
                }
            }
        );

        if ($input->getOption("install")) {
            $manager->installObjects();
        } else if ($input->getOption("uninstall")) {
            $manager->uninstallObjects();
        }

        $output->writeln(
            array_map(function (string $query) {
                if (!str_ends_with($query, ';')) {
                    $query .= ';';
                }

                return $query;
            }, $queries)
        );

        return Command::SUCCESS;
    }
}
