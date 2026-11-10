<?php

declare(strict_types=1);

namespace App\Demo;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Demo observability only — not part of darkwood/flow.
 */
final class Trace
{
    public function __construct(
        private readonly ?OutputInterface $output = null,
    ) {
    }

    /**
     * @param array<string, scalar|null> $context
     */
    public function emit(string $event, array $context = []): void
    {
        $parts = [];
        foreach ($context as $key => $value) {
            $parts[] = sprintf('%s=%s', $key, $this->formatValue($value));
        }

        $line = sprintf('[%s]%s', $event, $parts === [] ? '' : ' '.implode(' ', $parts));

        if ($this->output !== null) {
            $this->output->writeln($line);

            return;
        }

        echo $line.PHP_EOL;
    }

    private function formatValue(bool|float|int|string|null $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
