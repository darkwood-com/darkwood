<?php

namespace App\Account;

use App\Terminal\ExitCode;

final class AccountOperationException extends \RuntimeException
{
    public function __construct(string $message, public readonly ExitCode $exitCode)
    {
        parent::__construct($message);
    }
}
