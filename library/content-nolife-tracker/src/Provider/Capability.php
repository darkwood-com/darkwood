<?php

namespace App\Provider;

enum Capability: string
{
    case AccountDiscovery = 'account_discovery';
    case AuthenticationStatus = 'authentication_status';
    case QuotaRetrieval = 'quota_retrieval';
    case TokenUsage = 'token_usage';
    case BillingCredits = 'billing_credits';
    case ResetTimestamps = 'reset_timestamps';
    case HistoricalUsage = 'historical_usage';

    public function label(): string
    {
        return match ($this) {
            self::AccountDiscovery => 'Account discovery',
            self::AuthenticationStatus => 'Authentication status',
            self::QuotaRetrieval => 'Quota retrieval',
            self::TokenUsage => 'Token usage',
            self::BillingCredits => 'Billing and credits',
            self::ResetTimestamps => 'Reset timestamps',
            self::HistoricalUsage => 'Historical usage',
        };
    }
}
