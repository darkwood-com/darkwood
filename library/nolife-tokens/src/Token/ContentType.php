<?php

declare(strict_types=1);

namespace App\Token;

enum ContentType: string
{
    case Text = 'text';
    case Log = 'log';
    case Json = 'json';
    case GitDiff = 'git_diff';
    case FileList = 'file_list';
}
