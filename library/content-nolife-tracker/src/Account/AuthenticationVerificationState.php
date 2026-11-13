<?php

namespace App\Account;

enum AuthenticationVerificationState: string
{
    case NotConfigured = 'not_configured';
    case NotChecked = 'not_checked';
    case Verified = 'verified';
    case Invalid = 'invalid';
    case Unavailable = 'unavailable';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::NotConfigured => 'Not configured',
            self::NotChecked => 'Not checked',
            self::Verified => 'Verified',
            self::Invalid => 'Invalid',
            self::Unavailable => 'Unavailable',
            self::Error => 'Error',
        };
    }
}
