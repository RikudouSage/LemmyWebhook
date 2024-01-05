<?php

namespace App\Enum;

enum DatabaseOperation: string
{
    case Update = 'UPDATE';
    case Insert = 'INSERT';
    case Delete = 'DELETE';
}
