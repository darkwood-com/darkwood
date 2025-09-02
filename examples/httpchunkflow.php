<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Flow\Driver\AmpDriver;
use Flow\Driver\ReactDriver;
use Flow\Examples\Model\ChunkData;
use Flow\Examples\Model\HttpRequestData;
use Flow\Examples\Model\HttpResponseData;
use Flow\Examples\Model\UserData;
use Flow\Flow\YFlow;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\Job\YJob;
use Flow\JobInterface;

// Choose a driver
$driver = match (random_int(1, 2)) {
    1 => new AmpDriver(),
    2 => new ReactDriver(),
};

printf("Using %s driver\n", $driver::class);

// Y-combinator wrapper for recursive functions
$Ywrap = static function (callable $func, callable $wrapperFunc): JobInterface {
    $wrappedFunc = static fn ($recurse) => $wrapperFunc(static fn (...$args) => $func($recurse)(...$args));
    return new YJob($wrappedFunc);
};

// Memoization wrapper for caching results
$memoWrapperGenerator = static function (callable $f): Closure {
    static $cache = [];
    return static function ($y) use ($f, &$cache) {
        if (!isset($cache[$y])) {
            $cache[$y] = $f($y);
        }
        return $cache[$y];
    };
};

$Ymemo = static function (callable $f) use ($Ywrap, $memoWrapperGenerator): JobInterface {
    return $Ywrap($f, $memoWrapperGenerator);
};

// Job 1: Make initial HTTP request
$httpRequestJob = static function (HttpRequestData $requestData) use ($driver): HttpResponseData {
    printf("*. #%d - Making HTTP request to %s\n", $requestData->id, $requestData->url);

    // Simulate HTTP request delay
    $delay = random_int(1, 3);
    $driver->delay($delay);

    // Simulate different responses based on URL
    if (str_contains($requestData->url, '404')) {
        $statusCode = 404;
        $content = '{"error": "Not found"}';
    } else {
        $statusCode = 200;
        $content = json_encode([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com']
        ]);
    }

    printf("*. #%d - HTTP request completed with status %d (took %.01f seconds)\n",
           $requestData->id, $statusCode, $delay);

    return new HttpResponseData($requestData->id, $content, $statusCode);
};

// Job 2: Parse HTTP response and handle 404 errors
$parseResponseJob = static function (HttpResponseData $responseData) use ($driver): ChunkData {
    printf(".* #%d - Parsing HTTP response\n", $responseData->id);

    // Handle 404 error - retry with different URL if it's the first page
    if ($responseData->statusCode === 404) {
        $url = 'https://jsonplaceholder.typicode.com/users'; // Fallback URL
        printf(".* #%d - 404 error detected, retrying with fallback URL\n", $responseData->id);

        // Simulate retry delay
        $driver->delay(1);
        $responseData = new HttpResponseData($responseData->id, json_encode([
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']
        ]), 200);
    }

    $parsedData = json_decode($responseData->content, true);

    return new ChunkData(
        $responseData->id,
        $responseData->content,
        true, // isFirst
        true, // isLast
        $parsedData
    );
};

// Job 3: Process chunks recursively using Y-combinator
$processChunksJob = static function ($processChunks) {
    return static function (ChunkData $chunkData) use ($processChunks): ChunkData {
        printf("..* #%d - Processing chunks recursively with Y-combinator\n", $chunkData->id);

        if (!$chunkData->additionalRequests || !is_array($chunkData->additionalRequests)) {
            return $chunkData;
        }

        $additionalRequests = [];
        foreach ($chunkData->additionalRequests as $user) {
            $userId = $user['id'];

            // Create additional requests for each user
            $additionalRequests[] = new HttpRequestData(
                $chunkData->id * 100 + $userId,
                "https://jsonplaceholder.typicode.com/users/{$userId}/todos",
                ['user_id' => $userId, 'type' => 'todos']
            );

            $additionalRequests[] = new HttpRequestData(
                $chunkData->id * 100 + $userId + 1000,
                "https://jsonplaceholder.typicode.com/users/{$userId}/posts",
                ['user_id' => $userId, 'type' => 'posts']
            );
        }

        // Store additional requests in the chunk data
        $chunkData->additionalRequests = $additionalRequests;
        return $chunkData;
    };
};

// Job 4: Make additional HTTP requests with Y-combinator
$makeAdditionalRequestsJob = new YJob(static function ($makeRequests) use ($driver) {
    return static function (ChunkData $chunkData) use ($makeRequests, $driver): ChunkData {
        if (!$chunkData->additionalRequests || !is_array($chunkData->additionalRequests)) {
            return $chunkData;
        }

        $responses = [];
        foreach ($chunkData->additionalRequests as $request) {
            if ($request instanceof HttpRequestData) {
                printf("...* #%d - Making additional request to %s\n", $request->id, $request->url);

                // Simulate HTTP request with variable delay
                $delay = random_int(1, 3);
                $driver->delay($delay);

                // Simulate response based on request type
                $content = match ($request->options['type']) {
                    'todos' => json_encode([
                        ['id' => 1, 'title' => 'Todo 1', 'completed' => false],
                        ['id' => 2, 'title' => 'Todo 2', 'completed' => true],
                        ['id' => 3, 'title' => 'Todo 3', 'completed' => false]
                    ]),
                    'posts' => json_encode([
                        ['id' => 1, 'title' => 'Post 1', 'body' => 'Content 1'],
                        ['id' => 2, 'title' => 'Post 2', 'body' => 'Content 2']
                    ]),
                    default => '[]'
                };

                $responses[] = new HttpResponseData($request->id, $content, 200);
            }
        }

        // Store responses in chunk data
        $chunkData->additionalRequests = $responses;
        return $chunkData;
    };
});

// Job 5: Merge additional data with main response
$mergeDataJob = static function (ChunkData $chunkData) use ($driver): array {
    printf("....* Merging additional data with main response\n");

    // Get the original user data from the chunk
    $originalUsers = json_decode($chunkData->content, true);
    $additionalResponses = $chunkData->additionalRequests ?? [];

    if (!$originalUsers || !is_array($originalUsers)) {
        return [];
    }

    $mergedUsers = [];

    foreach ($originalUsers as $user) {
        $userId = $user['id'];

        // Find additional data for this user
        $todos = [];
        $posts = [];

        foreach ($additionalResponses as $response) {
            if ($response instanceof HttpResponseData) {
                $responseData = json_decode($response->content, true);

                // Check if this response belongs to the current user
                $responseUserId = intval(($response->id % 10000) / 100);
                if ($responseUserId === $userId) {
                    if ($response->id % 1000 < 100) { // todos
                        $todos = $responseData;
                    } else { // posts
                        $posts = $responseData;
                    }
                }
            }
        }

        $mergedUsers[] = new UserData(
            $userId,
            $user['name'],
            $user['email'],
            $todos,
            $posts
        );
    }

    return $mergedUsers;
};

// Job 6: Final processing and output
$finalizeJob = static function (array $mergedUsers): void {
    printf(".....* Finalizing results\n");

    foreach ($mergedUsers as $user) {
        printf("User #%d: %s (%s)\n", $user->id, $user->name, $user->email);
        printf("  - Todos: %d items\n", count($user->availabilities ?? []));
        printf("  - Posts: %d items\n", count($user->posts ?? []));

        // Show some details
        if (!empty($user->availabilities)) {
            printf("    Todo examples: %s\n", implode(', ', array_column($user->availabilities, 'title')));
        }
        if (!empty($user->posts)) {
            printf("    Post examples: %s\n", implode(', ', array_column($user->posts, 'title')));
        }
    }

    printf("Processing completed successfully!\n");
};

// Create the Flow with Y-combinator and async processing
$flow = (new FlowFactory())->create(static function () use (
    $httpRequestJob,
    $parseResponseJob,
    $processChunksJob,
    $makeAdditionalRequestsJob,
    $mergeDataJob,
    $finalizeJob
) {
    yield [$httpRequestJob];
    yield [$parseResponseJob];
    yield new YFlow($processChunksJob);
    yield [$makeAdditionalRequestsJob];
    yield [$mergeDataJob];
    yield [$finalizeJob];
}, ['driver' => $driver]);

// Test with different scenarios
$testScenarios = [
    // Normal case
    new HttpRequestData(1, 'https://jsonplaceholder.typicode.com/users'),

    // 404 case (will retry)
    new HttpRequestData(2, 'https://jsonplaceholder.typicode.com/users/404'),

    // Another normal case
    new HttpRequestData(3, 'https://jsonplaceholder.typicode.com/todos'),
];

printf("Starting HTTP chunk processing with Flow and Y-combinator...\n\n");

foreach ($testScenarios as $scenario) {
    $ip = new Ip($scenario);
    $flow($ip);
}

$flow->await();

printf("\nAll HTTP chunk processing completed!\n");
