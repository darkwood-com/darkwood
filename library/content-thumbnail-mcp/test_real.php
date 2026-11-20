<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use App\Bridge\Replicate\Client;
use App\Tool\ThumbnailTool;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpClient\HttpClient;

$apiKey = getenv('REPLICATE_API_KEY');
if (false === $apiKey || '' === $apiKey) {
    fwrite(STDERR, "Set REPLICATE_API_KEY to run the live test.\n");
    exit(1);
}

$tool = new ThumbnailTool(new Client(HttpClient::create(), new Clock(), $apiKey));
echo $tool->generateThumbnailImage('John Doe').PHP_EOL;
