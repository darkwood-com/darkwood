<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Model\DocumentRequest;
use App\Port\InMemoryDocumentSource;
use App\Workflow\ExcerptWorkflowFactory;
use App\Workflow\FlowCollector;
use Flow\Driver\AmpDriver;
use Flow\Driver\FiberDriver;
use Flow\Ip;

$driverName = $argv[1] ?? 'fiber';
$driver = match ($driverName) {
    'amp' => new AmpDriver(),
    'fiber' => new FiberDriver(),
    default => throw new InvalidArgumentException('Use fiber or amp'),
};

$html = file_get_contents(__DIR__ . '/../fixtures/article.html');
if ($html === false) {
    throw new RuntimeException('Missing fixtures/article.html');
}

$source = new InMemoryDocumentSource([
    'fixture://article' => $html,
]);

$flow = (new ExcerptWorkflowFactory())->create($driver, $source);
$result = FlowCollector::run($flow, new Ip(new DocumentRequest('fixture://article')));

printf("Driver : %s\n", $driver::class);
printf("Title  : %s\n", $result->title);
printf("Excerpt: %s\n", $result->excerpt);
