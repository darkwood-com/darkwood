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
The runtime accepts only a small lambda syntax.
Output rules:
- Output exactly one non-empty line: the lambda expression only.
- Use Unicode λ for abstraction (example: λx.x).
- Use only runtime-safe syntax: λ, letters, digits, dot, parentheses, and spaces.
- Identifier names must be alphanumeric only: no underscore, hyphen, colon, quotes, JSON, or markdown.
- When you need a primitive, model it as a leading lambda parameter with an alphanumeric name.
- Prefer sendemail, fetchdata, getprofile over send_email, fetch-data, or get.profile.
- Use lambda application only, for example sendemail(payload) or sendemail payload.
- Do not output string literals, object literals, array literals, or inline numbers such as (λx.x)(5).
- When a value is needed, abstract over it as a parameter instead of embedding it inline.
- Examples:
- λx.x
- λsendemail.λpayload.sendemail(payload)
- λfetchdata.λquery.fetchdata(query)
- Do not explain. No markdown fences. No surrounding text.
TXT;
    }

    public function userPrompt(string $userIntent): string
    {
        return 'User intent: ' . trim($userIntent);
    }
}
