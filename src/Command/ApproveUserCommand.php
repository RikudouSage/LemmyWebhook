<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:user:approve')]
final class ApproveUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                name: 'username',
                mode: InputArgument::REQUIRED,
                description: 'The username to approve.',
            )
            ->addOption(
                name: 'reject',
                mode: InputOption::VALUE_NONE,
                description: 'Reject the user registration instead of approving it.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $enabled = !$input->getOption('reject');

        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->findOneBy([
            'username' => $username,
        ]);
        if ($user === null) {
            $io->error('The user does not exist.');
            return Command::FAILURE;
        }

        if ($user->isEnabled() === $enabled) {
            $io->warning('The user is already in the requested state.');
            return Command::SUCCESS;
        }

        $user->setEnabled($enabled);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('The user has been successfully ' . ($enabled ? 'approved' : 'disapproved'));

        return Command::SUCCESS;
    }
}
