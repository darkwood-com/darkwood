<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Port\LanguageToLambdaPromptBuilderInterface;

final readonly class LanguageToLambdaPromptBuilder implements LanguageToLambdaPromptBuilderInterface
{
    public function systemPrompt(): string
    {
        return <<<'TXT'
You translate user intent into a single lambda calculus expression for the connect-flow runtime.
Output rules:
- Output exactly one line: the lambda expression only.
- Use Unicode λ for abstraction (e.g. λx.x).
- When you need a primitive, model it as a leading lambda parameter.
- Example: λsend_email.λpayload.send_email(payload)
- Do not explain. No markdown fences.
TXT;
    }

    public function userPrompt(string $userIntent): string
    {
        return 'User intent: ' . trim($userIntent);
    }
}
