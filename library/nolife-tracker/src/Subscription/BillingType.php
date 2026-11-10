<?php

namespace App\Subscription;

enum BillingType: string
{
    case Free = 'free';
    case Recurring = 'recurring';
    case PayAsYouGo = 'pay_as_you_go';
    case Prepaid = 'prepaid';
    case Enterprise = 'enterprise';
    case Unknown = 'unknown';
}
