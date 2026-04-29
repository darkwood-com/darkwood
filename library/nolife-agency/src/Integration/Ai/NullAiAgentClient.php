<?php

namespace App\Integration\Ai;

final class NullAiAgentClient implements AiAgentClient
{
    public function name(): string
    {
        return 'null';
    }

    public function run(AiAgentRequest $request): AiAgentResponse
    {
        error_log('Using Null AI adapter');
        return AiAgentResponse::fromAgentOutput($request->agent->run($request->input, $request->context));
    }
}
