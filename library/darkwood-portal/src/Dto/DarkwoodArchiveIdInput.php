<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Input for MCP tool get_darkwood_archive. Archive date ID (Y-m-d).
 */
final class DarkwoodArchiveIdInput
{
    public function __construct(
        #[ApiProperty(schema: ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$', 'description' => 'Actual calendar date YYYY-MM-DD; premium shared snapshot.'])]
        public string $id = '',
    ) {
    }
}
