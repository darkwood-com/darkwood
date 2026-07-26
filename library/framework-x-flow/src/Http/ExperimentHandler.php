<?php

declare(strict_types=1);

namespace App\Http;

use App\Experiment\AsyncExecutionExperiment;
use App\Flow\EmbeddedReactDriver;
use App\Flow\EmbeddedReactFlowRunner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Promise\PromiseInterface;

final class ExperimentHandler
{
    public function __construct(
        private readonly AsyncExecutionExperiment $experiment,
    ) {
    }

    /**
     * @return PromiseInterface<ResponseInterface>
     */
    public function __invoke(ServerRequestInterface $request): PromiseInterface
    {
        $driver = new EmbeddedReactDriver();
        $runner = new EmbeddedReactFlowRunner($driver);

        return $this->experiment->runAsync($driver, $runner, 0.05, 3)
            ->then(static fn (array $result) => JsonResponse::ok($result));
    }
}
