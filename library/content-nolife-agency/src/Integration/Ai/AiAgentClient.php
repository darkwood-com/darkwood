<?php

namespace App\Integration\Ai;

interface AiAgentClient
{
    public function name(): string;

    public function run(AiAgentRequest $request): AiAgentResponse;
}
