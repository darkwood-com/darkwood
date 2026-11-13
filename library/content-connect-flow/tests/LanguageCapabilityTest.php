<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Capability\LanguageCapability;
use App\Domain\Port\LanguageToLambdaPromptBuilderInterface;
use App\Domain\Port\LocalAiClientInterface;
use PHPUnit\Framework\TestCase;

final class LanguageCapabilityTest extends TestCase
{
    public function testReturnsLambdaFromStubbedModel(): void
    {
        $ai = new class implements LocalAiClientInterface {
            public function generate(string $systemPrompt, string $userPrompt): string
            {
                return 'λsend_email.λpayload.send_email(payload)';
            }
        };
        $prompts = new class implements LanguageToLambdaPromptBuilderInterface {
            public function systemPrompt(): string
            {
                return 'sys';
            }

            public function userPrompt(string $userIntent): string
            {
                return $userIntent;
            }
        };

        $cap = new LanguageCapability($ai, $prompts);
        $expr = $cap->textToLambda('do something');

        self::assertSame('λsend_email.λpayload.send_email(payload)', $expr->source());
    }
}
