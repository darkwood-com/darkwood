<?php

declare(strict_types=1);

namespace App\Compiler\Preproc;

use App\Compiler\Common\SourceLoc;
use App\Compiler\Common\Token;
use App\Compiler\Common\TokenKind;

/** C99 keywords. */
final class Lexer
{
  /** @var list<string> */
  private const array C_KEYWORDS = [
    'auto', 'break', 'case', 'char', 'const', 'continue',
    'default', 'do', 'double', 'else', 'enum', 'extern',
    'float', 'for', 'goto', 'if', 'inline', 'int', 'long',
    'register', 'restrict', 'return', 'short', 'signed',
    'sizeof', 'static', 'struct', 'switch', 'typedef',
    'union', 'unsigned', 'void', 'volatile', 'while',
    '_Bool', '_Complex', '_Imaginary', '_Alignas',
    '_Alignof', '_Atomic', '_Generic', '_Noreturn',
    '_Static_assert', '_Thread_local',
    '__asm', '__asm__', '__attribute__', '__attribute',
    '__typeof', '__typeof__', '__extension__', '__inline',
    '__inline__', '__const', '__const__', '__volatile',
    '__volatile__', '__restrict', '__restrict__', '__signed',
    '__signed__', '__unaligned',
  ];

  /** Multi-char punctuators sorted by length (longest first). */
  private const array MULTI_CHAR_PUNCTS = [
    // 3-char
    '<<=', '>>=', '...', ':::',
    // 2-char
    '->', '++', '--', '<<', '>>', '<=', '>=', '==', '!=',
    '&&', '||', '+=', '-=', '*=', '/=', '%=', '&=', '|=', '^=', '##', '::',
  ];

  private readonly string $source;
  private readonly int $fileId;
  private int $pos;
  private int $length;

  /** @var list<Token> */
  private array $tokens = [];

  public function __construct(string $source, int $fileId, int $startOffset = 0)
  {
    $this->source = $source;
    $this->fileId = $fileId;
    $this->pos = $startOffset;
    $this->length = strlen($source);
  }

  /**
   * Tokenize the entire input and return the token array (ending with Eof).
   *
   * @return list<Token>
   */
  public function tokenize(): array
  {
    while ($this->pos < $this->length) {
      $b = $this->byteAt($this->pos);

      // Line continuation: backslash-newline → skip both
      if ($b === 0x5C && $this->pos + 1 < $this->length && $this->byteAt($this->pos + 1) === 0x0A) {
        $this->pos += 2;
        continue;
      }
      // Also handle \r\n
      if ($b === 0x5C && $this->pos + 2 < $this->length
        && $this->byteAt($this->pos + 1) === 0x0D && $this->byteAt($this->pos + 2) === 0x0A) {
        $this->pos += 3;
        continue;
      }

      // Skip whitespace
      if ($this->isWhitespace($b)) {
        ++$this->pos;
        continue;
      }

      // Skip line comment
      if ($b === 0x2F && $this->pos + 1 < $this->length && $this->byteAt($this->pos + 1) === 0x2F) { // //
        $this->skipLineComment();
        continue;
      }

      // Skip block comment
      if ($b === 0x2F && $this->pos + 1 < $this->length && $this->byteAt($this->pos + 1) === 0x2A) { // /*
        $this->skipBlockComment();
        continue;
      }

      // Prefixed char/string literal: L' u' U' L" u" U" (but NOT u8)
      if ($this->isLiteralPrefix($b) && $this->pos + 1 < $this->length) {
        $next = $this->byteAt($this->pos + 1);
        if ($next === 0x27) { // '
          $this->lexCharLiteral();
          continue;
        }
        if ($next === 0x22) { // "
          $this->lexStringLiteral();
          continue;
        }
      }

      // Identifier or keyword
      if ($this->isIdentStart($b)) {
        $this->lexIdentifier();
        continue;
      }

      // Number (integer or float)
      if ($this->isDigit($b) || ($b === 0x2E && $this->pos + 1 < $this->length && $this->isDigit($this->byteAt($this->pos + 1)))) {
        $this->lexNumber();
        continue;
      }

      // Char literal (without prefix — prefixed ones handled above)
      if ($b === 0x27) { // '
        $this->lexCharLiteral();
        continue;
      }

      // String literal (without prefix — prefixed ones handled above)
      if ($b === 0x22) { // "
        $this->lexStringLiteral();
        continue;
      }

      // Punctuator
      $this->lexPunctuator();
    }

    $this->tokens[] = new Token(
      kind: TokenKind::Eof,
      spelling: '',
      loc: new SourceLoc(fileId: $this->fileId, offset: $this->pos),
    );

    return $this->tokens;
  }

  private function currentLoc(): SourceLoc
  {
    return new SourceLoc(fileId: $this->fileId, offset: $this->pos);
  }

  private function byteAt(int $index): int
  {
    return ord($this->source[$index]);
  }

  private function isWhitespace(int $b): bool
  {
    return $b === 0x20 || $b === 0x09 || $b === 0x0A || $b === 0x0D || $b === 0x0B || $b === 0x0C;
  }

  private function isDigit(int $b): bool
  {
    return $b >= 0x30 && $b <= 0x39;
  }

  private function isHexDigit(int $b): bool
  {
    return ($b >= 0x30 && $b <= 0x39) || ($b >= 0x41 && $b <= 0x46) || ($b >= 0x61 && $b <= 0x66);
  }

  private function isIdentStart(int $b): bool
  {
    return ($b >= 0x41 && $b <= 0x5A) || ($b >= 0x61 && $b <= 0x7A) || $b === 0x5F;
  }

  private function isIdentCont(int $b): bool
  {
    return $this->isIdentStart($b) || $this->isDigit($b);
  }

  /** Check if byte is a literal prefix character: L, u, U (not u8 for now). */
  private function isLiteralPrefix(int $b): bool
  {
    return $b === 0x4C || $b === 0x75 || $b === 0x55; // L u U
  }

  private function skipLineComment(): void
  {
    $this->pos += 2; // skip //
    while ($this->pos < $this->length && $this->byteAt($this->pos) !== 0x0A) {
      ++$this->pos;
    }
  }

  private function skipBlockComment(): void
  {
    $this->pos += 2; // skip /*
    while ($this->pos < $this->length) {
      if ($this->byteAt($this->pos) === 0x2A && $this->pos + 1 < $this->length && $this->byteAt($this->pos + 1) === 0x2F) {
        $this->pos += 2;
        return;
      }
      ++$this->pos;
    }
    // Unterminated comment: just stop (return EOF)
  }

  private function lexIdentifier(): void
  {
    $start = $this->pos;
    while ($this->pos < $this->length && $this->isIdentCont($this->byteAt($this->pos))) {
      ++$this->pos;
    }
    $spelling = $this->bytesToString($start, $this->pos);
    $kind = in_array($spelling, self::C_KEYWORDS, true) ? TokenKind::Keyword : TokenKind::Identifier;
    $this->tokens[] = new Token(
      kind: $kind,
      spelling: $spelling,
      loc: new SourceLoc(fileId: $this->fileId, offset: $start),
    );
  }

  private function lexNumber(): void
  {
    $start = $this->pos;

    // Check for hex/binary prefix
    if ($this->byteAt($this->pos) === 0x30 && $this->pos + 1 < $this->length) {
      $next = $this->byteAt($this->pos + 1);
      if ($next === 0x78 || $next === 0x58) { // 0x or 0X — hex number
        $this->pos += 2;
        $this->lexHexNumber($start);
        return;
      }
      if ($next === 0x62 || $next === 0x42) { // 0b or 0B — binary
        $this->pos += 2;
        while ($this->pos < $this->length && ($this->byteAt($this->pos) === 0x30 || $this->byteAt($this->pos) === 0x31)) {
          ++$this->pos;
        }
        $this->lexIntegerSuffix();
        $this->tokens[] = new Token(
          kind: TokenKind::IntegerLiteral,
          spelling: $this->bytesToString($start, $this->pos),
          loc: new SourceLoc(fileId: $this->fileId, offset: $start),
        );
        return;
      }
    }

    // Decimal / octal number
    $isFloat = false;

    // Integer part
    while ($this->pos < $this->length && $this->isDigit($this->byteAt($this->pos))) {
      ++$this->pos;
    }

    // Fractional part
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x2E) { // .
      $isFloat = true;
      ++$this->pos;
      while ($this->pos < $this->length && $this->isDigit($this->byteAt($this->pos))) {
        ++$this->pos;
      }
    }

    // Exponent
    if ($this->pos < $this->length && ($this->byteAt($this->pos) === 0x65 || $this->byteAt($this->pos) === 0x45)) { // e or E
      $isFloat = true;
      ++$this->pos;
      if ($this->pos < $this->length && ($this->byteAt($this->pos) === 0x2B || $this->byteAt($this->pos) === 0x2D)) { // + or -
        ++$this->pos;
      }
      while ($this->pos < $this->length && $this->isDigit($this->byteAt($this->pos))) {
        ++$this->pos;
      }
    }

    if ($isFloat) {
      // Float suffix
      if ($this->pos < $this->length && in_array($this->byteAt($this->pos), [0x66, 0x46, 0x6C, 0x4C], true)) {
        // f F l L
        ++$this->pos;
      }
      $this->tokens[] = new Token(
        kind: TokenKind::FloatLiteral,
        spelling: $this->bytesToString($start, $this->pos),
        loc: new SourceLoc(fileId: $this->fileId, offset: $start),
      );
    } else {
      $this->lexIntegerSuffix();
      $this->tokens[] = new Token(
        kind: TokenKind::IntegerLiteral,
        spelling: $this->bytesToString($start, $this->pos),
        loc: new SourceLoc(fileId: $this->fileId, offset: $start),
      );
    }
  }

  /** Handle hex number after 0x prefix. */
  private function lexHexNumber(int $start): void
  {
    $isFloat = false;

    // Hex digits
    while ($this->pos < $this->length && $this->isHexDigit($this->byteAt($this->pos))) {
      ++$this->pos;
    }

    // Hex float: fractional part
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x2E) { // .
      $isFloat = true;
      ++$this->pos;
      while ($this->pos < $this->length && $this->isHexDigit($this->byteAt($this->pos))) {
        ++$this->pos;
      }
    }

    // Hex float: exponent (p or P)
    if ($this->pos < $this->length && ($this->byteAt($this->pos) === 0x70 || $this->byteAt($this->pos) === 0x50)) { // p or P
      $isFloat = true;
      ++$this->pos;
      if ($this->pos < $this->length && ($this->byteAt($this->pos) === 0x2B || $this->byteAt($this->pos) === 0x2D)) { // + or -
        ++$this->pos;
      }
      while ($this->pos < $this->length && $this->isDigit($this->byteAt($this->pos))) {
        ++$this->pos;
      }
    }

    if ($isFloat) {
      // Float suffix
      if ($this->pos < $this->length && in_array($this->byteAt($this->pos), [0x66, 0x46, 0x6C, 0x4C], true)) {
        ++$this->pos;
      }
      $this->tokens[] = new Token(
        kind: TokenKind::FloatLiteral,
        spelling: $this->bytesToString($start, $this->pos),
        loc: new SourceLoc(fileId: $this->fileId, offset: $start),
      );
    } else {
      $this->lexIntegerSuffix();
      $this->tokens[] = new Token(
        kind: TokenKind::IntegerLiteral,
        spelling: $this->bytesToString($start, $this->pos),
        loc: new SourceLoc(fileId: $this->fileId, offset: $start),
      );
    }
  }

  /** Consume an integer suffix (U, L, UL, LL, ULL, etc., case-insensitive, any order). */
  private function lexIntegerSuffix(): void
  {
    $sawU = false;
    $sawL = 0;
    while ($this->pos < $this->length) {
      $b = $this->byteAt($this->pos);
      if (($b === 0x55 || $b === 0x75) && !$sawU) { // U or u
        $sawU = true;
        ++$this->pos;
      } elseif (($b === 0x4C || $b === 0x6C) && $sawL < 2) { // L or l
        ++$sawL;
        ++$this->pos;
      } else {
        break;
      }
    }
  }

  private function lexCharLiteral(): void
  {
    $start = $this->pos;
    // Skip prefix (L, u, U) if present
    if (in_array($this->byteAt($this->pos), [0x4C, 0x75, 0x55], true)) { // L u U
      ++$this->pos;
    }
    // Opening quote
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x27) { // '
      ++$this->pos;
    }
    // Content: skip until closing quote, handling escapes
    while ($this->pos < $this->length && $this->byteAt($this->pos) !== 0x27) { // not '
      if ($this->byteAt($this->pos) === 0x5C && $this->pos + 1 < $this->length) { // backslash escape
        $this->pos += 2;
      } else {
        ++$this->pos;
      }
    }
    // Closing quote
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x27) {
      ++$this->pos;
    }
    $this->tokens[] = new Token(
      kind: TokenKind::CharLiteral,
      spelling: $this->bytesToString($start, $this->pos),
      loc: new SourceLoc(fileId: $this->fileId, offset: $start),
    );
  }

  private function lexStringLiteral(): void
  {
    $start = $this->pos;
    // Skip prefix (L, u, U) if present
    if (in_array($this->byteAt($this->pos), [0x4C, 0x75, 0x55], true)) { // L u U
      ++$this->pos;
    }
    // Opening quote
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x22) { // "
      ++$this->pos;
    }
    // Content: skip until closing quote, handling escapes
    while ($this->pos < $this->length && $this->byteAt($this->pos) !== 0x22) { // not "
      if ($this->byteAt($this->pos) === 0x5C && $this->pos + 1 < $this->length) { // backslash escape
        $this->pos += 2;
      } else {
        ++$this->pos;
      }
    }
    // Closing quote
    if ($this->pos < $this->length && $this->byteAt($this->pos) === 0x22) {
      ++$this->pos;
    }
    $this->tokens[] = new Token(
      kind: TokenKind::StringLiteral,
      spelling: $this->bytesToString($start, $this->pos),
      loc: new SourceLoc(fileId: $this->fileId, offset: $start),
    );
  }

  private function lexPunctuator(): void
  {
    $start = $this->pos;

    // Try longest match (3-char, then 2-char, then 1-char)
    if ($this->pos + 3 <= $this->length) {
      $three = $this->bytesToString($this->pos, $this->pos + 3);
      if (in_array($three, self::MULTI_CHAR_PUNCTS, true)) {
        $this->pos += 3;
        $this->tokens[] = new Token(
          kind: TokenKind::Punct,
          spelling: $three,
          loc: new SourceLoc(fileId: $this->fileId, offset: $start),
        );
        return;
      }
    }
    if ($this->pos + 2 <= $this->length) {
      $two = $this->bytesToString($this->pos, $this->pos + 2);
      if (in_array($two, self::MULTI_CHAR_PUNCTS, true)) {
        $this->pos += 2;
        $this->tokens[] = new Token(
          kind: TokenKind::Punct,
          spelling: $two,
          loc: new SourceLoc(fileId: $this->fileId, offset: $start),
        );
        return;
      }
    }

    // Single character
    $ch = $this->bytesToString($this->pos, $this->pos + 1);
    ++$this->pos;
    $this->tokens[] = new Token(
      kind: TokenKind::Punct,
      spelling: $ch,
      loc: new SourceLoc(fileId: $this->fileId, offset: $start),
    );
  }

  private function bytesToString(int $from, int $to): string
  {
    return substr($this->source, $from, $to - $from);
  }
}
