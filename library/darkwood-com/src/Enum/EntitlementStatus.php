<?php

declare(strict_types=1);

namespace App\Enum;

enum EntitlementStatus: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case Refunded = 'refunded';
}
