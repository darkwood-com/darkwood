<?php

namespace App\Subscription;

enum EntitlementAccess: string
{
    case Unknown = 'unknown';
    case Declared = 'declared';
    case Verified = 'verified';
}
