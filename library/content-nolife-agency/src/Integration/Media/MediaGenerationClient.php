<?php

namespace App\Integration\Media;

interface MediaGenerationClient
{
    public function request(MediaGenerationRequest $request): MediaGenerationResult;
}
