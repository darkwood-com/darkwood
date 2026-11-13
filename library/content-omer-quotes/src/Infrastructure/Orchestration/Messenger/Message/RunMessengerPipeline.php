<?php
declare(strict_types=1);
namespace App\Infrastructure\Orchestration\Messenger\Message;
final readonly class RunMessengerPipeline
{
    public function __construct(public string $runId) {}
}
