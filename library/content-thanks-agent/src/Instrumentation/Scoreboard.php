<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Model\AgentResult;
use App\Model\RunReport;
use Symfony\Component\Console\Style\SymfonyStyle;

final class Scoreboard
{
    /**
     * @param list<AgentResult> $results
     */
    public function buildReport(
        string $runId,
        bool $withThanks,
        string $driver,
        float $wallTimeMs,
        array $results,
        CostLedger $ledger,
    ): RunReport {
        $totalValue = 0;
        $totalCost = 0.0;
        $totalMessages = 0;
        $totalTokens = 0;
        $usefulWork = 0;

        foreach ($results as $result) {
            $totalValue += $result->value;
            $totalCost += $result->cost;
            $totalMessages += $result->messages;
            $totalTokens += $result->tokens;
            if ($result->didWork) {
                ++$usefulWork;
            }
        }

        $snapshot = $ledger->snapshot();
        $totalCost += (float) $snapshot['scheduling']['cost'];

        return new RunReport(
            runId: $runId,
            withThanks: $withThanks,
            driver: $driver,
            wallTimeMs: $wallTimeMs,
            results: $results,
            flowMetrics: $snapshot,
            totalValue: $totalValue,
            totalCost: $totalCost,
            totalMessages: $totalMessages,
            totalTokens: $totalTokens,
            usefulWorkCount: $usefulWork,
        );
    }

    public function render(SymfonyStyle $io, RunReport $report): void
    {
        $io->title('Thanks Agent — Scoreboard');
        $io->writeln(sprintf('Run: %s | Driver: %s | With Thanks: %s', $report->runId, $report->driver, $report->withThanks ? 'yes' : 'no'));
        $io->writeln(sprintf('Wall time: %.2f ms', $report->wallTimeMs));
        $io->newLine();

        $rows = [];
        foreach ($report->results as $result) {
            $rows[] = [
                $result->agentId->value,
                (string) $result->value,
                sprintf('%.6f', $result->cost),
                (string) $result->messages,
                (string) $result->tokens,
                sprintf('%.1f', $result->latencyMs),
                $result->didWork ? 'yes' : 'NO',
                mb_strimwidth($result->output, 0, 40, '…'),
            ];
        }

        $io->table(
            ['Agent', 'Value', 'Cost', 'Messages', 'Tokens', 'Latency ms', 'Work', 'Output'],
            $rows,
        );

        $io->section('Totals');
        $io->listing([
            sprintf('Value produced: %d', $report->totalValue),
            sprintf('Resources consumed (cost): %.6f', $report->totalCost),
            sprintf('Messages: %d', $report->totalMessages),
            sprintf('Tokens: %d', $report->totalTokens),
            sprintf('Useful work units: %d', $report->usefulWorkCount),
            sprintf(
                'Cost / successful outcome: %s',
                $report->flowMetrics['totals']['costPerSuccessfulOutcome'] ?? 'n/a',
            ),
            sprintf(
                'Flow events — push:%d pull:%d pop:%d async:%d',
                $report->flowMetrics['scheduling']['push'],
                $report->flowMetrics['scheduling']['pull'],
                $report->flowMetrics['scheduling']['pop'],
                $report->flowMetrics['scheduling']['async'],
            ),
        ]);

        $io->warning($report->toArray()['verdict']);
    }
}
