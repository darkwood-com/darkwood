<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Job\ExcerptJob;
use App\Job\FetchJob;
use App\Job\ParseJob;
use App\Job\ValidateJob;
use App\Port\DocumentSource;
use Flow\DriverInterface;
use Flow\FlowFactory;
use Flow\FlowInterface;

final class ExcerptWorkflowFactory
{
    /**
     * @param DriverInterface<mixed, mixed> $driver
     *
     * @return FlowInterface<mixed>
     */
    public function create(
        DriverInterface $driver,
        DocumentSource $source,
        int $maxExcerptLength = 120,
        int $minExcerptLength = 20,
    ): FlowInterface {
        return (new FlowFactory($driver))->create(static function () use ($source, $maxExcerptLength, $minExcerptLength) {
            yield new FetchJob($source);
            yield new ParseJob();
            yield new ExcerptJob($maxExcerptLength);
            yield new ValidateJob($minExcerptLength);
        });
    }
}
