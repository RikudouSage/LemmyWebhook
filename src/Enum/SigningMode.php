<?php

namespace App\Enum;

enum SigningMode: string
{
    case None = 'none';
    case Symmetric = 'symmetric';
    case Asymmetric = 'asymmetric';
}
