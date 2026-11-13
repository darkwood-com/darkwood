<?php

declare(strict_types=1);

namespace App\Http;

use FrameworkX\ErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;

/**
 * Replaces Framework X's default HTML error pages with safe JSON responses.
 *
 * Why this exists: FrameworkX\ErrorHandler always embeds exception class, message,
 * and file:line in HTML 500 bodies (no debug/production switch). For a JSON API
 * we subclass ErrorHandler so App bootstrap treats us as the real error middleware
 * (plain middleware ahead of ErrorHandler still gets a default HTML ErrorHandler
 * unshifted in front).
 *
 * Logs full details locally; never returns stack traces, absolute paths, or raw
 * exception internals to clients.
 */
final class JsonErrorHandler extends ErrorHandler
{
    public function __construct(
        private readonly string $logFile,
    ) {
        parent::__construct();
    }

    /**
     * @return ResponseInterface|PromiseInterface<ResponseInterface>|\Generator
     */
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            return $this->jsonFromThrowable($e);
        }

        if ($response instanceof ResponseInterface) {
            return $response;
        }

        if ($response instanceof PromiseInterface) {
            return $response->then(
                function ($value) {
                    if ($value instanceof ResponseInterface) {
                        return $value;
                    }

                    return $this->jsonError(500, 'invalid_handler_response', 'Request handler returned an invalid response.');
                },
                fn (\Throwable $e) => $this->jsonFromThrowable($e)
            );
        }

        if ($response instanceof \Generator) {
            return $this->jsonCoroutine($response);
        }

        return $this->jsonError(500, 'invalid_handler_response', 'Request handler returned an invalid response.');
    }

    private function jsonCoroutine(\Generator $generator): \Generator
    {
        do {
            try {
                if (!$generator->valid()) {
                    $response = $generator->getReturn();
                    if ($response instanceof ResponseInterface) {
                        return $response;
                    }

                    return $this->jsonError(500, 'invalid_handler_response', 'Request handler returned an invalid response.');
                }
            } catch (\Throwable $e) {
                return $this->jsonFromThrowable($e);
            }

            $promise = $generator->current();
            if (!$promise instanceof PromiseInterface) {
                return $this->jsonError(500, 'invalid_coroutine_yield', 'Request handler yielded an invalid value.');
            }

            try {
                $next = yield $promise;
            } catch (\Throwable $e) {
                try {
                    $generator->throw($e);
                    continue;
                } catch (\Throwable $inner) {
                    return $this->jsonFromThrowable($inner);
                }
            }

            try {
                $generator->send($next);
            } catch (\Throwable $e) {
                return $this->jsonFromThrowable($e);
            }
        } while (true);
    }

    private function jsonFromThrowable(\Throwable $e): ResponseInterface
    {
        $this->logThrowable($e);

        return $this->jsonError(500, 'internal_error', 'An unexpected error occurred.');
    }

    public function jsonError(int $status, string $code, string $message, array $extra = []): ResponseInterface
    {
        return Response::json([
            'error' => [
                'code' => $code,
                'message' => $message,
                ...$extra,
            ],
        ])->withStatus($status);
    }

    private function logThrowable(\Throwable $e): void
    {
        $line = sprintf(
            "%s ERROR %s: %s in %s:%d\n%s\n",
            date('c'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
