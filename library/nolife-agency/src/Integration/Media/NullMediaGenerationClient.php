<?php

namespace App\Integration\Media;

final class NullMediaGenerationClient implements MediaGenerationClient
{
    public function request(MediaGenerationRequest $request): MediaGenerationResult
    {
        return new MediaGenerationResult(
            requestId: sprintf('local-media-%s-%s', $request->kind, $request->runId),
            status: 'noop',
            metadata: [
                'kind' => $request->kind,
                'payload' => $request->payload,
                'context' => $request->context,
            ],
        );
    }
}
