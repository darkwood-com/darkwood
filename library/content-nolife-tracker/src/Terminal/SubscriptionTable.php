<?php

namespace App\Terminal;

use App\Account\Account;
use App\Subscription\Subscription;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SubscriptionTable
{
    /**
     * @param list<Subscription> $subscriptions
     * @param array<string, Account> $accounts
     */
    public static function render(SymfonyStyle $io, array $subscriptions, array $accounts): void
    {
        $io->table(
            ['ID', 'Provider', 'Account', 'Name', 'Billing', 'Status', 'Renews / expires'],
            array_map(static function (Subscription $subscription) use ($accounts): array {
                $account = $accounts[$subscription->accountId];
                $amount = null === $subscription->billingAmountMinor
                    ? $subscription->billingType->value
                    : sprintf('%.2f %s%s', $subscription->billingAmountMinor / 100, $subscription->currency, null === $subscription->billingInterval ? '' : '/'.$subscription->billingInterval->value);

                return [
                    $subscription->id,
                    $account->providerKey,
                    $subscription->accountId,
                    $subscription->displayName ?? $subscription->planName ?? '—',
                    $amount,
                    $subscription->status->value,
                    $subscription->renewsAt?->format('Y-m-d') ?? $subscription->expiresAt?->format('Y-m-d') ?? '—',
                ];
            }, $subscriptions),
        );
    }
}
