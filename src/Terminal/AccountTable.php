<?php

namespace App\Terminal;

use App\Account\Account;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AccountTable
{
    /**
     * @param list<Account> $accounts
     */
    public static function render(SymfonyStyle $io, array $accounts): void
    {
        $io->table(
            ['ID', 'Provider', 'Product', 'Alias', 'Registered', 'Authentication', 'Monitoring'],
            array_map(static fn (Account $account): array => [
                $account->id,
                $account->providerKey,
                $account->productKey,
                $account->alias ?? '—',
                'yes',
                $account->authenticationVerificationState->label(),
                $account->monitoringEnabled ? 'enabled' : 'disabled',
            ], $accounts),
        );
    }
}
