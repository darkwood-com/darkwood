<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentProvider: string
{
    case Bonzai = 'bonzai';
    case Xsolla = 'xsolla';
    case Steam = 'steam';
    case Discord = 'discord';
}
