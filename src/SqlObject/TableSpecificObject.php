<?php

namespace App\SqlObject;

interface TableSpecificObject
{
    public function getTable(): string;
}
