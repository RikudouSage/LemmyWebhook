<?php

namespace App\Service;

use App\SqlObject\DependentInstallableObject;
use App\SqlObject\InstallableSqlObject;
use Doctrine\DBAL\Connection;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class InstallableSqlObjectManager
{
    /**
     * @param iterable<InstallableSqlObject> $objects
     */
    public function __construct(
        #[TaggedIterator('app.installable_sql')]
        private iterable $objects,
        private Connection $connection,
    ) {
    }

    public function installObjects(): void
    {
        $sorted = $this->getSortedObjects($this->objects);
        foreach ($sorted as $object) {
            $object->install($this->connection);
        }
    }

    public function uninstallObjects(): void
    {
        $sorted = array_reverse($this->getSortedObjects($this->objects));
        foreach ($sorted as $object) {
            $object->uninstall($this->connection);
        }
    }

    /**
     * @param iterable<InstallableSqlObject> $objects
     * @return array<InstallableSqlObject>
     */
    private function getSortedObjects(iterable $objects): array
    {
        if (!is_countable($objects)) {
            $objects = [...$objects];
        }

        $result = [];
        $doneList = [];

        while (count($objects) > count($result)) {
            $doneSomething = false;
            foreach ($objects as $object) {
                if (isset($doneList[$object::class])) {
                    continue;
                }

                $resolved = true;
                if ($object instanceof DependentInstallableObject && count($object->getDependencies())) {
                    foreach ($object->getDependencies() as $dependency) {
                        if (!isset($doneList[$dependency])) {
                            $resolved = false;
                            break;
                        }
                    }
                }
                if ($resolved) {
                    $doneList[$object::class] = true;
                    $result[] = $object;
                    $doneSomething = true;
                }
            }

            if (!$doneSomething) {
                throw new RuntimeException('Unresolvable dependency');
            }
        }

        return $result;
    }
}
