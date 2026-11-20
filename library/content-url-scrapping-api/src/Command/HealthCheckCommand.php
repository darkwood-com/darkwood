<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('monitor:health', 'Runs Health Checks')]
final class HealthCheckCommand
{
    public const CHECKS = [
        'php_extensions' => 'Extension Loaded',
        'disk_usage' => 'Disk Usage',
    ];

    public function __invoke(
        OutputInterface $output,
        #[Argument(description: 'Run only the named check.', name: 'checkName')]
        ?string $checkName = null,
        #[Option(description: 'Return a Nagios-compatible status code.')]
        bool $nagios = false,
    ): int {
        if (null !== $checkName && !isset(self::CHECKS[$checkName])) {
            $output->writeln('Unknown health check.');

            return $nagios ? 3 : Command::FAILURE;
        }

        $severity = 0;
        foreach (self::CHECKS as $id => $label) {
            if (null !== $checkName && $id !== $checkName) {
                continue;
            }

            $status = 'disk_usage' === $id ? self::diskStatus() : self::extensionStatus();
            $output->writeln(['OK', 'WARNING', 'CRITICAL', 'UNKNOWN'][$status].' '.$label);
            $severity = max($severity, $status);
        }

        return $nagios ? $severity : (2 <= $severity ? Command::FAILURE : Command::SUCCESS);
    }

    private static function extensionStatus(): int
    {
        return extension_loaded('json') && extension_loaded('pdo') ? 0 : 2;
    }

    private static function diskStatus(): int
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        if (false === $free || false === $total || $total <= 0) {
            return 3;
        }

        $used = 100 * ($total - $free) / $total;
        if ($used >= 90) {
            return 2;
        }

        return $used >= 70 ? 1 : 0;
    }
}
