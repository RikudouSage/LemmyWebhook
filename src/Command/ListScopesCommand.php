<?php

namespace App\Command;

use App\Attribute\RawDataType;
use ReflectionObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand('app:scope:list')]
final class ListScopesCommand extends Command
{
    /**
     * @param iterable<object> $types
     */
    public function __construct(
        #[TaggedIterator('app.raw_data_type')]
        private readonly iterable $types,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $types = array_map(function (object $object): string {
            $reflection = new ReflectionObject($object);
            $attribute = $reflection->getAttributes(RawDataType::class)[0]->newInstance();
            assert($attribute instanceof RawDataType);

            return $attribute->table;
        }, [...$this->types]);

        $io->writeln($types);

        return Command::SUCCESS;
    }
}
