<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType: string
{
    case NONE = 'none';
    case SKELETON = 'skeleton';
    case MCP = 'mcp';

    public function getDescription(): string
    {
        return match ($this) {
            self::NONE => 'No project, just Joplin note',
            self::SKELETON => 'Symfony skeleton project',
            self::MCP => 'Symfony project with php-llm/mcp-bundle',
        };
    }
}
