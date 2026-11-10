<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/**
 * Owns source file contents and maps byte offsets to line/column positions.
 */
final class SourceManager
{
  /** @var list<array{name: string, contents: string}> */
  private array $files = [];

  /** @var array<int, list<int>> byte offsets of line starts, keyed by fileId */
  private array $lineStarts = [];

  public function __construct()
  {
  }

  /**
   * Load a file from disk and return its fileId.
   */
  public function load(string $path): int
  {
    $contents = file_get_contents($path);
    if ($contents === false) {
      throw new \RuntimeException("Failed to read source file: {$path}");
    }

    $fileId = count($this->files);
    $this->files[] = ['name' => $path, 'contents' => $contents];

    return $fileId;
  }

  /**
   * Register in-memory contents (for testing / synthetic input).
   */
  public function register(string $name, string $contents): int
  {
    $fileId = count($this->files);
    $this->files[] = ['name' => $name, 'contents' => $contents];

    return $fileId;
  }

  public function contents(int $fileId): string
  {
    return $this->files[$fileId]['contents'];
  }

  public function name(int $fileId): string
  {
    if ($fileId < 0 || $fileId >= count($this->files)) {
      return '<unknown>';
    }

    return $this->files[$fileId]['name'];
  }

  /**
   * Drop all registered sources (and line-start indexes).
   * Safe to call between independent compilations on a shared Compiler.
   */
  public function reset(): void
  {
    $this->files = [];
    $this->lineStarts = [];
  }

  /**
   * Convert a byte offset within a file to (line, column), both 1-based.
   *
   * @return array{line: int, col: int}
   */
  public function lineCol(SourceLoc $loc): array
  {
    if ($loc->fileId < 0 || $loc->fileId >= count($this->files)) {
      return ['line' => 0, 'col' => 0];
    }

    $starts = $this->lineStarts($loc->fileId);
    $offset = max(0, $loc->offset);
    $contents = $this->files[$loc->fileId]['contents'];
    $offset = min($offset, strlen($contents));

    // Binary search: largest line-start index with start <= offset.
    $lo = 0;
    $hi = count($starts) - 1;
    while ($lo <= $hi) {
      $mid = intdiv($lo + $hi, 2);
      if ($starts[$mid] <= $offset) {
        $lo = $mid + 1;
      } else {
        $hi = $mid - 1;
      }
    }
    $lineIdx = max(0, $hi);
    $lineStart = $starts[$lineIdx];

    return ['line' => $lineIdx + 1, 'col' => $offset - $lineStart + 1];
  }

  /**
   * @return list<int>
   */
  private function lineStarts(int $fileId): array
  {
    if (isset($this->lineStarts[$fileId])) {
      return $this->lineStarts[$fileId];
    }

    $contents = $this->files[$fileId]['contents'];
    $starts = [0];
    $len = strlen($contents);
    for ($i = 0; $i < $len; ++$i) {
      if ($contents[$i] === "\n") {
        $starts[] = $i + 1;
      }
    }

    return $this->lineStarts[$fileId] = $starts;
  }

  /**
   * Get the full line text containing the given location.
   */
  public function lineText(SourceLoc $loc): string
  {
    if ($loc->fileId < 0 || $loc->fileId >= count($this->files)) {
      return '';
    }

    $contents = $this->files[$loc->fileId]['contents'];
    $start = $loc->offset;

    while ($start > 0 && ord($contents[$start - 1]) !== 0x0A) {
      --$start;
    }

    $end = $loc->offset;
    $len = strlen($contents);

    while ($end < $len && ord($contents[$end]) !== 0x0A) {
      ++$end;
    }

    return substr($contents, $start, $end - $start);
  }

  /**
   * Extract a substring from the given range.
   */
  public function slice(SourceLoc $from, SourceLoc $to): string
  {
    if ($from->fileId !== $to->fileId || $from->fileId < 0) {
      return '';
    }

    $contents = $this->files[$from->fileId]['contents'];
    $s = max(0, $from->offset);
    $e = min(strlen($contents), $to->offset);

    if ($s > $e) {
      return '';
    }

    return substr($contents, $s, $e - $s);
  }
}
