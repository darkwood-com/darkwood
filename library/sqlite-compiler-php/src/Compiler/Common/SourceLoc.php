<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/**
 * A position in a source file.
 */
final readonly class SourceLoc
{
  public function __construct(
    public int $fileId,
    public int $offset,
  ) {
  }

  /** An invalid/unknown location. */
  public static function unknown(): self
  {
    return new self(fileId: -1, offset: -1);
  }
}
