<?php

declare(strict_types=1);

namespace Darkwood\IaExceptionBundle\Tests;

use Darkwood\IaExceptionBundle\Model\ExceptionContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionContextTest extends TestCase
{
    public function testDiagnosticContextExcludesSensitiveExceptionDetails(): void
    {
        try {
            $this->raise('private@example.com');
        } catch (RuntimeException $exception) {
            $context = ExceptionContext::fromThrowable($exception);
            $serialized = json_encode($context->toArray(), JSON_THROW_ON_ERROR);

            self::assertSame(RuntimeException::class, $context->class);
            self::assertStringContainsString('raise', $context->trace);
            self::assertStringNotContainsString('private@example.com', $serialized);
            self::assertStringNotContainsString(__FILE__, $serialized);
        }
    }

    private function raise(string $email): never
    {
        throw new RuntimeException(sprintf('Cannot load account %s', $email));
    }
}
