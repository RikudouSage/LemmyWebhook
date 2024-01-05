<?php

namespace App\Command;

use App\Service\InstallableSqlObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:manage-objects')]
final class ManageObjectsCommand extends Command
{
    public function __construct(
        private readonly InstallableSqlObjectManager $objectManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('install', mode: InputOption::VALUE_NONE)
            ->addOption('uninstall', mode: InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $install = $input->getOption('install');
        $uninstall = $input->getOption('uninstall');

        if (!$install && !$uninstall) {
            $io->error('You must either choose --install or --uninstall mode.');
            return Command::FAILURE;
        }

        if ($install && $uninstall) {
            $io->error('Only one of --install or --uninstall must be present.');
            return Command::FAILURE;
        }

        if ($install) {
            $io->comment('Installing DB objects...');
            $this->objectManager->installObjects();
        }
        if ($uninstall) {
            $io->comment('Uninstalling DB objects...');
            $this->objectManager->uninstallObjects();
        }

        $io->success('Finished!');
        return Command::SUCCESS;
    }
}
