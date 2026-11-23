<?php

declare(strict_types=1);

namespace App\Enum;

enum ArticleType: string
{
    case Manual = 'manual';
    case Auto = 'auto';
    case Auto = 'auto';
    case Watch = 'watch';
    case Release = 'release';
    case Creator = 'creator';
}
