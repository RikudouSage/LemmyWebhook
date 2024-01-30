<?php

namespace App\Command;

use App\Repository\ScopeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:scope:check')]
final class CheckScopesCommand extends Command
{
    public function __construct(
        private readonly ScopeRepository $scopeRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        do {
            $scopes = $this->scopeRepository->findBy([
                'granted' => false,
            ]);
            if (!count($scopes)) {
                $io->success('There are no more scope requests to handle.');
                return Command::SUCCESS;
            }
            $choices = [];
            foreach ($scopes as $scope) {
                $choices[] = "scope: {$scope->getScope()}, user: {$scope->getUser()->getUserIdentifier()}, id: {$scope->getId()}";
            }
            $scopeToHandle = $io->askQuestion(new ChoiceQuestion('Which scope do you want to handle (you can use arrow to navigate)', $choices));
            $id = (int) trim(substr($scopeToHandle, strpos($scopeToHandle, 'id: ') + strlen('id: ')));
            $approve = $io->askQuestion(new ChoiceQuestion('Approve/Reject', ['approve', 'reject'])) === 'approve';
            $scope = $this->scopeRepository->find($id);
            assert($scope !== null);
            if ($approve) {
                $scope->setGranted(true);
                $this->entityManager->persist($scope);
            } else {
                $this->entityManager->remove($scope);
            }
            $this->entityManager->flush();
        } while (true);
    }
}
