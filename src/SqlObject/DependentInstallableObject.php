<?php

namespace App\SqlObject;

interface DependentInstallableObject
{
    /**
     * @return array<class-string<InstallableSqlObject>>
     */
    public function getDependencies(): array;
}
