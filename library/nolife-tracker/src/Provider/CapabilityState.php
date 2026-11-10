<?php

namespace App\Provider;

enum CapabilityState: string
{
    case Implemented = 'implemented';
    case NotImplemented = 'not_implemented';
    case Unsupported = 'unsupported';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Implemented => 'Available',
            self::NotImplemented => 'Not implemented',
            self::Unsupported => 'Unsupported',
            self::Unknown => 'Unknown',
        };
    }
}
