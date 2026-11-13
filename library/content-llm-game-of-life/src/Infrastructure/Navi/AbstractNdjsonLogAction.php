<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;

abstract readonly class AbstractNdjsonLogAction implements Action
{
    public function __construct(
        protected string $logFile,
    ) {
    }

    final public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $record = $this->buildRecord($context);
        $line = json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . "\n";

        $dir = \dirname($this->logFile);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ActionResult::continueWith($context, Event::fromName('life_llm.log_failed', ['reason' => 'mkdir']));
        }

        if (false === @file_put_contents($this->logFile, $line, \FILE_APPEND | \LOCK_EX)) {
            return ActionResult::continueWith($context, Event::fromName('life_llm.log_failed', ['reason' => 'write']));
        }

        return ActionResult::continueWith($context, Event::fromName('life_llm.log_written'));
    }

    /**
     * @return array<string,mixed>
     */
    abstract protected function buildRecord(Context $context): array;

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    protected function record(Context $context, string $event, array $payload): array
    {
        $record = [
            'schema_version' => 1,
            'at' => gmdate('c'),
            'run_id' => $context->get('run_id'),
            'channel' => $context->get('channel', 'cli'),
            'event' => $event,
            'payload' => $payload,
        ];

        foreach ($this->indexedFields($payload) as $key => $value) {
            $record[$key] = $value;
        }

        return $record;
    }

    /**
     * @param array<string,mixed> $payload
     *
     * @return array<string,mixed>
     */
    private function indexedFields(array $payload): array
    {
        $fields = [];
        foreach (['mode', 'dataset', 'seed', 'generation', 'program_size', 'sequence_hash'] as $key) {
            if (array_key_exists($key, $payload)) {
                $fields[$key] = $payload[$key];
            }
        }

        if (array_key_exists('best_fitness', $payload)) {
            $fields['fitness'] = $payload['best_fitness'];
        } elseif (array_key_exists('fitness', $payload)) {
            $fields['fitness'] = $payload['fitness'];
        } elseif (array_key_exists('final_fitness', $payload)) {
            $fields['fitness'] = $payload['final_fitness'];
        }

        return $fields;
    }
}
