<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Export;

enum ExportFormat: string
{
    case Json = 'json';
    case Csv = 'csv';
    case Markdown = 'md';
}
