<?php

declare(strict_types=1);

/**
 * Text → lambda without calling Ollama (fake AI for demonstration).
 *
 * Run: php examples/text_to_lambda.php
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Domain\Capability\LanguageCapability;
use App\Domain\Port\LanguageToLambdaPromptBuilderInterface;
use App\Domain\Port\LocalAiClientInterface;

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
$expr = $cap->textToLambda('Send a hello email to the team');

echo $expr->source() . "\n";
