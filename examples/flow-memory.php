<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Composer\InstalledVersions;
use Flow\FlowFactory;
use Flow\Ip;

$calculations = 0;
$result = [];
$flow = (new FlowFactory())->create(static function () use (&$calculations, &$result) {
    yield static function (array $state) use (&$calculations): array {
        ++$calculations;
        return $state + ['total' => array_sum($state['values'])];
    };
    yield static fn (array $state): array => $state + ['mean' => $state['total'] / count($state['values'])];
    yield static function (array $state) use (&$result): array {
        $result = $state + ['remaining' => 100 - $state['total']];
        return $result;
    };
});
$flow(new Ip(['values' => [4, 7, 9]]));
$flow->await();

$report = ['package' => InstalledVersions::getPrettyVersion('darkwood/flow'), 'calculations' => $calculations, 'result' => $result];
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
}
return $report;
