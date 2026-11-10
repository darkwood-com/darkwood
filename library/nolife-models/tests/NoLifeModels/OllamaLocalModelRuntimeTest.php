<?php

declare(strict_types=1);

namespace App\Tests\NoLifeModels;

use App\NoLifeModels\Infrastructure\Ollama\OllamaLocalModelRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class OllamaLocalModelRuntimeTest extends TestCase
{
    public function testListLocalModelsMapsTags(): void
    {
        $client = new MockHttpClient([
            new JsonMockResponse(['models' => [
                ['name' => 'llama3:latest', 'size' => 1000, 'modified_at' => 'x', 'digest' => 'd'],
            ]]),
        ]);
        $runtime = new OllamaLocalModelRuntime($client);
        $models = $runtime->listLocalModels();
        self::assertCount(1, $models);
        self::assertSame('llama3:latest', $models[0]->name);
    }
}
