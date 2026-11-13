<?php

declare(strict_types=1);

namespace App\Flow;

use App\Token\ContentClassifier;
use App\Token\ContextOptimizer;
use App\Token\ContextPacket;
use App\Token\ContextStore;
use App\Token\SignalEvaluator;
use App\Token\TokenEstimator;
use Flow\FlowFactory;
use Flow\FlowInterface;
use Flow\Ip;

final class TokenPipelineFactory
{
    public function __construct(
        private readonly ContentClassifier $classifier,
        private readonly TokenEstimator $estimator,
        private readonly ContextOptimizer $optimizer,
        private readonly SignalEvaluator $signalEvaluator,
        private readonly ContextStore $store = new ContextStore(),
        private readonly FlowFactory $flowFactory = new FlowFactory(),
    ) {}

    /**
     * @return FlowInterface<mixed>
     */
    public function create(): FlowInterface
    {
        $classifier = $this->classifier;
        $estimator = $this->estimator;
        $optimizer = $this->optimizer;
        $signalEvaluator = $this->signalEvaluator;
        $store = $this->store;

        return $this->flowFactory->create(static function () use ($classifier, $estimator, $optimizer, $signalEvaluator, $store) {
            yield static function (ContextPacket $packet): ContextPacket {
                $packet->addTrace('LOAD', $packet->sourcePath !== '' ? $packet->sourcePath : 'inline', strlen($packet->raw));

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($classifier): ContextPacket {
                $packet->type = $classifier->classify($packet->raw);
                $packet->addTrace('CLASSIFY', $packet->type->value);

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($estimator): ContextPacket {
                $packet->rawMetrics = $estimator->measure($packet->raw);
                $packet->addTrace(
                    'MEASURE_RAW',
                    null,
                    $packet->rawMetrics->bytes,
                    $packet->rawMetrics->estimatedTokens,
                );

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($optimizer): ContextPacket {
                assert($packet->type !== null);
                $optimizer->optimize($packet);
                $packet->addTrace(
                    'OPTIMIZE',
                    sprintf('%s refs=%d', $packet->type->value, count($packet->references)),
                    strlen($packet->optimized),
                );

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($estimator): ContextPacket {
                $packet->optimizedMetrics = $estimator->measure($packet->optimized);
                $packet->addTrace(
                    'MEASURE_OPT',
                    sprintf(
                        'markers~%d referenced~%d',
                        $packet->referenceMarkerTokens,
                        $packet->referencedTokens,
                    ),
                    $packet->optimizedMetrics->bytes,
                    $packet->optimizedMetrics->estimatedTokens,
                );

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($signalEvaluator): ContextPacket {
                $packet->missingSignals = $signalEvaluator->missing($packet->optimized, $packet->expectedSignals);
                $packet->retainedHighlights = $signalEvaluator->retained($packet->expectedSignals, $packet->missingSignals);
                $packet->signalPass = $packet->expectedSignals === [] || $packet->missingSignals === [];
                $packet->addTrace(
                    'SIGNAL',
                    $packet->signalPass ? 'PASS' : 'FAIL',
                );

                return $packet;
            };

            yield static function (ContextPacket $packet) use ($store): ContextPacket {
                $missing = [];
                foreach ($packet->references as $ref) {
                    $loaded = $store->get($ref->id);
                    if ($loaded === null || $loaded->content !== $ref->content) {
                        $missing[] = $ref->id;
                    }
                }
                $packet->missingRecoveries = $missing;
                $packet->recoveryPass = $missing === [];
                $packet->addTrace(
                    'RECOVERY',
                    $packet->recoveryPass ? 'PASS' : 'FAIL',
                );

                return $packet;
            };
        });
    }

    public function run(ContextPacket $packet): ContextPacket
    {
        $flow = $this->create();
        $result = FlowCollector::run($flow, new Ip($packet));
        assert($result instanceof ContextPacket);

        return $result;
    }
}
