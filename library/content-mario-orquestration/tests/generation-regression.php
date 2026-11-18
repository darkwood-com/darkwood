<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Mario\Infrastructure\AI\MarioAgentOrchestrator;
use App\Mario\Infrastructure\AI\MarioMultiAgentFactory;

$orchestrator = new MarioAgentOrchestrator(new MarioMultiAgentFactory());
$fixture = $orchestrator->orchestrate('Original level', true);
if (!$fixture['fake_mode'] || !$fixture['validation']->isValid()) {
    throw new RuntimeException('Explicit offline fixture failed.');
}
$validate = new ReflectionMethod($orchestrator, 'validatePayload');
$invalid = $fixture['payload'];
$invalid['terrain'][0]['w'] = $invalid['width'] + 1;
if ($validate->invoke($orchestrator, $invalid)->isValid()) {
    throw new RuntimeException('Terrain overflow was accepted.');
}
try {
    $orchestrator->orchestrate('', true);
    throw new RuntimeException('Empty prompt was accepted.');
} catch (InvalidArgumentException) {
}
unset($_ENV['OPENAI_API_KEY'], $_SERVER['OPENAI_API_KEY']);
putenv('OPENAI_API_KEY');
try {
    $orchestrator->orchestrate('Original level', false);
    throw new LogicException('Missing key silently entered fixture mode.');
} catch (RuntimeException $exception) {
    if (!str_contains($exception->getMessage(), 'OPENAI_API_KEY is required')) {
        throw $exception;
    }
}
echo "PASS fixture, terrain bounds, prompt validation and missing-key failure\n";
