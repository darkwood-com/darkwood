<?php

namespace App\Terminal;

enum ExitCode: int
{
    case Success = 0;
    case Failure = 1;
    case InvalidInput = 2;
    case NotFound = 3;
    case Conflict = 4;
}
