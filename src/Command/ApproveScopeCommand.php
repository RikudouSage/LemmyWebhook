<?php

namespace App\Command;

use App\Entity\Scope;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:scope:approve')]
final class ApproveScopeCommand extends Command
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
                description: 'The username to approve the scopes for.'
            )
            ->addOption(
                name: 'reject',
                mode: InputOption::VALUE_NONE,
                description: 'Reject the scopes instead of approving them.',
            )
            ->addOption(
                name: 'scope',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'The scopes to approve.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $approve = !$input->getOption('reject');
        $scopes = $input->getOption('scope');

        $io = new SymfonyStyle($input, $output);
        if (!count($scopes)) {
            $io->error('There are no scopes specified, nothing to do.');
            return Command::FAILURE;
        }

        $user = $this->userRepository->loadUserByIdentifier($username);
        if (!$user) {
            $io->error('The user does not exist.');
            return Command::FAILURE;
        }

        foreach ($scopes as $scope) {
            $scopeEntity = $user->findScopeByType($scope);
            if ($scopeEntity === null) {
                $io->warning("The user haven't requested access to the '{$scope}' entity.");
                if ($approve) {
                    $io->warning('Adding the scope to the user now. If that is not what you wanted, please reject the scope manually.');
                    $scopeEntity = (new Scope())
                        ->setScope($scope)
                        ->setUser($user)
                        ->setGranted(true)
                    ;
                } else {
                    continue;
                }
            }
            if ($approve) {
                $scopeEntity->setGranted(true);
                $this->entityManager->persist($scopeEntity);
            } else {
                $this->entityManager->remove($scopeEntity);
            }
        }

        $this->entityManager->flush();

        $io->success('Successfully finished.');

        return Command::SUCCESS;
    }
}
