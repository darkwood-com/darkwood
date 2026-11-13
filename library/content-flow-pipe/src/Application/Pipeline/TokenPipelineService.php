<?php

declare(strict_types=1);

namespace App\Application\Pipeline;

use App\Infrastructure\Flow\PipelineRunnerInterface;
use App\TokenFlow\PipelineContext;
use App\TokenFlow\PipelineExpressionParser;

final readonly class TokenPipelineService
{
    public function __construct(
        private PipelineExpressionParser $parser,
        private PipelineRunnerInterface $runner,
    ) {
    }

    public function run(string $pipelineExpr, string $inputKey, bool $debug): PipelineContext
    {
        $steps = $this->parser->parse($pipelineExpr);
        $context = new PipelineContext();
        $context->inputKey = $inputKey;
        $context->debugEnabled = $debug;

        return $this->runner->run($steps, $context);
    }
}
