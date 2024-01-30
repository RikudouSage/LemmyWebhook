<?php

namespace App\Command;

use App\Entity\Scope;
use App\Entity\User;
use App\Service\RawWebhookParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create')]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RawWebhookParser $webhookParser,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                name: 'username',
                mode: InputArgument::REQUIRED,
                description: 'The username of the new user',
            )
            ->addOption(
                name: 'admin',
                mode: InputOption::VALUE_NONE,
                description: 'Whether to make the new user an admin who has access to everything.',
            )
            ->addOption(
                name: 'password',
                mode: InputOption::VALUE_REQUIRED,
                description: 'Provide the password instead of asking interactively for it. This might leak sensitive data.',
            )
            ->addOption(
                name: 'scope',
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                description: 'Add a scope that this user will have access to.',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $admin = $input->getOption('admin');
        $password = $input->getOption('password');
        $scopes = $input->getOption('scope');

        $io = new SymfonyStyle($input, $output);
        while (!$password) {
            $password = $io->askHidden('Password for the new user');
        }

        $user = (new User())
            ->setEnabled(true)
            ->setRoles($admin ? ['ROLE_ADMIN'] : ['ROLE_USER'])
            ->setUsername($username)
            ->setPassword($this->passwordHasher->hashPassword(new User(), $password))
        ;
        $this->entityManager->persist($user);

        foreach ($scopes as $scope) {
            if (!$this->webhookParser->isValidTable($scope)) {
                $io->error("'{$scope}' is not a valid scope");
                return Command::FAILURE;
            }
            $scopeEntity = (new Scope())
                ->setScope($scope)
                ->setGranted(true)
                ->setUser($user);
            $this->entityManager->persist($scopeEntity);
        }

        $this->entityManager->flush();

        $io->success('The user has been successfully created.');

        return Command::SUCCESS;
    }
}
