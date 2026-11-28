<?php

declare(strict_types=1);

namespace Darkwood\IaExceptionBundle\Model;

use Throwable;

use function array_slice;

/**
 * Serializable exception context for async AI analysis (stored in cache keyed by error_id).
 */
final class ExceptionContext
{
    public function __construct(
        public string $class,
        public string $message,
        public string $file,
        public int $line,
        public string $trace,
    ) {}

    /**
     * @return array{class: string, message: string, file: string, line: int, trace: string}
     */
    public function toArray(): array
    {
        return [
            'class' => $this->class,
            'message' => $this->message,
            'file' => $this->file,
            'line' => $this->line,
            'trace' => $this->trace,
        ];
    }

    /**
     * @param array{class: string, message: string, file: string, line: int, trace: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            class: $data['class'] ?? '',
            message: $data['message'] ?? '',
            file: $data['file'] ?? '',
            line: (int) ($data['line'] ?? 0),
            trace: $data['trace'] ?? '',
        );
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        $trace = [];
        foreach (array_slice($throwable->getTrace(), 0, 15) as $frame) {
            $class = $frame['class'] ?? '';
            $trace[] = (str_contains($class, '@anonymous') ? 'anonymous class' : $class)
                . ($frame['type'] ?? '') . $frame['function'];
        }

        return new self(
            class: get_debug_type($throwable),
            message: '',
            file: '',
            line: $throwable->getLine(),
            trace: implode("\n", $trace),
        );
    }
}
