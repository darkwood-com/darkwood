<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/** Diagnostic severity levels. */
enum DiagnosticSeverity: int
{
  case Note = 0;
  case Warning = 1;
  case Error = 2;
}

/** A single diagnostic message. */
final readonly class Diagnostic
{
  public function __construct(
    public DiagnosticSeverity $severity,
    public string $message,
    public SourceLoc $loc,
  ) {
  }

  public function description(): string
  {
    return $this->severity->name . ': ' . $this->message;
  }

  public function format(SourceManager $sm): string
  {
    $pos = $sm->lineCol($this->loc);
    $file = $sm->name($this->loc->fileId);
    $severity = strtolower($this->severity->name);

    return sprintf('%s:%d:%d: %s: %s', $file, $pos['line'], $pos['col'], $severity, $this->message);
  }

  public function __toString(): string
  {
    return $this->description();
  }
}

/** Collects diagnostics during compilation. */
final class DiagnosticEngine
{
  /** @var list<Diagnostic> */
  public array $diagnostics = [];

  public int $maxErrors = 20;

  private int $errorCount = 0;

  public function __construct()
  {
  }

  public function hasErrors(): bool
  {
    return $this->errorCount > 0;
  }

  public function error(string $message, SourceLoc $loc): void
  {
    if ($this->errorCount >= $this->maxErrors) {
      return;
    }

    $this->diagnostics[] = new Diagnostic(
      severity: DiagnosticSeverity::Error,
      message: $message,
      loc: $loc,
    );
    ++$this->errorCount;
  }

  public function warning(string $message, SourceLoc $loc): void
  {
    $this->diagnostics[] = new Diagnostic(
      severity: DiagnosticSeverity::Warning,
      message: $message,
      loc: $loc,
    );
  }

  public function note(string $message, SourceLoc $loc): void
  {
    $this->diagnostics[] = new Diagnostic(
      severity: DiagnosticSeverity::Note,
      message: $message,
      loc: $loc,
    );
  }

  public function clear(): void
  {
    $this->diagnostics = [];
    $this->errorCount = 0;
  }
}
