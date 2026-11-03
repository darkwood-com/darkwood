<?php

namespace App\Subscription;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Canceled = 'canceled';
    case Unknown = 'unknown';
}
