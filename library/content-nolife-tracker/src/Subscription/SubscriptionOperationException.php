<?php

namespace App\Subscription;

use App\Terminal\ExitCode;

final class SubscriptionOperationException extends \RuntimeException
{
    public function __construct(string $message, public readonly ExitCode $exitCode)
    {
        parent::__construct($message);
    }
}
