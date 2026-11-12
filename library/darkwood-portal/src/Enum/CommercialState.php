<?php

declare(strict_types=1);

namespace App\Enum;

enum CommercialState: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Refunded = 'refunded';
    case Failed = 'failed';
}
