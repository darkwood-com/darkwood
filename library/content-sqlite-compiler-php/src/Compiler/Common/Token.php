<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/** A single C token. */
final readonly class Token
{
  /**
   * @param list<string> $hideSet Names that should NOT be expanded for this token (blue-painting).
   */
  public function __construct(
    public TokenKind $kind,
    public string $spelling,
    public SourceLoc $loc,
    public array $hideSet = [],
  ) {
  }

  /**
   * Create a copy of this token with an expanded hide set.
   *
   * @param list<string> $newHideSet
   */
  public function withHideSet(array $newHideSet): self
  {
    if ($newHideSet === $this->hideSet) {
      return $this;
    }

    return new self(
      kind: $this->kind,
      spelling: $this->spelling,
      loc: $this->loc,
      hideSet: $newHideSet,
    );
  }
}
