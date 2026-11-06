<?php

namespace App\Support;

class SlideShareProvider
{
    public function capabilities(): array
    {
        return ['create' => 'unavailable_platform_incident', 'updateMetadata' => 'assisted', 'replaceDocument' => 'unconfirmed', 'getPublication' => 'manual', 'delete' => 'not_implemented', 'upload_url' => 'https://fr.slideshare.net/upload'];
    }
}
