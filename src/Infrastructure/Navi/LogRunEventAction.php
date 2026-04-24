<?php

declare(strict_types=1);

namespace App\Infrastructure\Navi;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;

final readonly class LogRunEventAction implements Action
{
    public function __construct(
        private string $logFile,
    ) {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('life_llm.log_run_event');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $record = [
            'at' => gmdate('c'),
            'run_id' => $context->get('run_id'),
            'channel' => $context->get('channel', 'cli'),
            'event' => $context->get('event', 'unknown'),
            'payload' => $context->get('payload', []),
        ];

        $line = json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . "\n";
        $dir = \dirname($this->logFile);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ActionResult::continueWith($context, Event::fromName('life_llm.log_failed', ['reason' => 'mkdir']));
        }

        if (false === @file_put_contents($this->logFile, $line, \FILE_APPEND | \LOCK_EX)) {
            return ActionResult::continueWith($context, Event::fromName('life_llm.log_failed', ['reason' => 'write']));
        }

        return ActionResult::continueWith($context, Event::fromName('life_llm.run_event_logged'));
    }
}
