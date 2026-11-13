<?php

declare(strict_types=1);

namespace App\Compiler\Preproc;

use App\Compiler\Common\DiagnosticEngine;
use App\Compiler\Common\SourceLoc;
use App\Compiler\Common\SourceManager;
use App\Compiler\Common\Token;
use App\Compiler\Common\TokenKind;

/** Preprocessor failure (rare — most missing includes are silently skipped). */
final class PreprocessorError extends \RuntimeException
{
}

/**
 * Preprocessor: handles #include, #define, conditionals, macro expansion.
 * Takes raw source bytes → produces an expanded C token stream.
 */
final class Preprocessor
{
  /** @var array<string, string> */
  private const array PREDEFINED_MACROS = [
    '__STDC__' => '1',
    '__STDC_VERSION__' => '199901L',
    '__STDC_HOSTED__' => '1',
    '__APPLE__' => '1',
    '__MACH__' => '1',
    '__arm64__' => '1',
    '__aarch64__' => '1',
    '__LITTLE_ENDIAN__' => '1',
    '__arm__' => '1',
    '__APPLE_CC__' => '1',
    '__SIZEOF_INT__' => '4',
    '__SIZEOF_LONG__' => '8',
    '__SIZEOF_LONG_LONG__' => '8',
    '__SIZEOF_SHORT__' => '2',
    '__SIZEOF_CHAR__' => '1',
    '__SIZEOF_FLOAT__' => '4',
    '__SIZEOF_DOUBLE__' => '8',
    '__SIZEOF_POINTER__' => '8',
    '__SIZEOF_SIZE_T__' => '8',
    '__SIZEOF_LONG_DOUBLE__' => '8',
    '__ORDER_LITTLE_ENDIAN__' => '1234',
    '__BYTE_ORDER__' => '__ORDER_LITTLE_ENDIAN__',
    '__INT_MAX__' => '2147483647',
    '__LONG_MAX__' => '9223372036854775807L',
    '__LONG_LONG_MAX__' => '9223372036854775807LL',
    '__DARWIN_C_LEVEL' => '900000',
    '__DARWIN_C_FULL' => '900000',
    '__DARWIN_C_ANSI' => '900000',
    '_POSIX_C_SOURCE' => '200809L',
    '_DARWIN_FEATURE_CLOCK_GETTIME' => '0',
    '_DARWIN_C_ANSI' => '1',
    '__STDC_NO_ATOMICS__' => '1',
    '__STDC_NO_THREADS__' => '1',
    '__ENVIRONMENT_MAC_OS_X_VERSION_MIN_REQUIRED__' => '140000',
    '__DARWIN_C_SOURCE' => '1',
    '_DARWIN_C_SOURCE' => '1',
    '_DARWIN_UNLIMITED_SELECT' => '1',
  ];

  private readonly MacroTable $macroTable;
  private readonly MacroExpander $expander;

  /**
   * @param list<string> $includePaths
   * @param array<string, string> $predefines
   */
  public function __construct(
    private readonly SourceManager $sm,
    private readonly array $includePaths,
    private array $predefines,
    private readonly DiagnosticEngine $diags = new DiagnosticEngine(),
  ) {
    $this->macroTable = new MacroTable();
    $this->expander = new MacroExpander($this->macroTable, $this->diags, $this->sm);

    foreach (self::PREDEFINED_MACROS as $name => $value) {
      $tokens = $this->lexTokensWithoutEof($value);
      $this->macroTable->define(new Macro(
        name: $name,
        isFunctionLike: false,
        isVariadic: false,
        params: [],
        body: $tokens,
        loc: SourceLoc::unknown(),
      ));
    }

    foreach ($this->predefines as $name => $value) {
      $tokens = $this->lexTokensWithoutEof($value);
      $this->macroTable->define(new Macro(
        name: $name,
        isFunctionLike: false,
        isVariadic: false,
        params: [],
        body: $tokens,
        loc: SourceLoc::unknown(),
      ));
    }
  }

  /**
   * Preprocess the given file and return the expanded token stream.
   *
   * @return list<Token>
   */
  public function preprocess(int $fileId): array
  {
    $bytes = $this->sm->contents($fileId);
    $rawTokens = $this->lexTokens($bytes, $fileId, 0);

    return $this->processTokens($rawTokens, $fileId);
  }

  public function isMacroDefined(string $name): bool
  {
    return $this->macroTable->isDefined($name);
  }

  /** Conditional compilation state. */
  private static function condState(
    bool $parentActive,
    bool $anyBranchTaken,
    bool $currentActive,
    bool $seenElse,
  ): array {
    return [
      'parentActive' => $parentActive,
      'anyBranchTaken' => $anyBranchTaken,
      'currentActive' => $currentActive,
      'seenElse' => $seenElse,
    ];
  }

  /**
   * @param list<Token> $tokens
   * @return list<Token>
   */
  private function processTokens(array $tokens, int $currentFileId): array
  {
    $output = [];
    $i = 0;
    /** @var list<array{parentActive: bool, anyBranchTaken: bool, currentActive: bool, seenElse: bool}> */
    $condStack = [];

    $isActive = static function () use (&$condStack): bool {
      foreach ($condStack as $state) {
        if (!$state['currentActive']) {
          return false;
        }
      }
      return true;
    };

    while ($i < count($tokens)) {
      $token = $tokens[$i];

      if ($token->kind === TokenKind::Punct && $token->spelling === '#' && $this->isAtLineStart($output, $tokens, $i)) {
        [$directiveTokens, $nextI] = $this->collectDirectiveTokens($tokens, $i + 1);
        $dirName = $directiveTokens[0]->spelling ?? '';

        switch ($dirName) {
          case 'if':
            $rest = array_slice($directiveTokens, 1);
            $parentActive = $isActive();
            if ($parentActive) {
              $preprocessed = $this->processDefined($rest);
              $expanded = $this->expander->expand($preprocessed);
              $value = $this->evalConstExpr($expanded);
              $condStack[] = self::condState($parentActive, $value !== 0, $value !== 0, false);
            } else {
              $condStack[] = self::condState(false, true, false, false);
            }
            break;

          case 'ifdef':
            $rest = array_slice($directiveTokens, 1);
            $parentActive = $isActive();
            $defined = isset($rest[0]) && $rest[0]->kind === TokenKind::Identifier
              && $this->macroTable->isDefined($rest[0]->spelling);
            $condStack[] = self::condState($parentActive, $defined, $parentActive && $defined, false);
            break;

          case 'ifndef':
            $rest = array_slice($directiveTokens, 1);
            $parentActive = $isActive();
            $defined = isset($rest[0]) && $rest[0]->kind === TokenKind::Identifier
              && $this->macroTable->isDefined($rest[0]->spelling);
            $condStack[] = self::condState($parentActive, !$defined, $parentActive && !$defined, false);
            break;

          case 'elif':
            if ($condStack !== []) {
              $state = $condStack[count($condStack) - 1];
              if ($state['parentActive'] && !$state['anyBranchTaken'] && !$state['seenElse']) {
                $rest = array_slice($directiveTokens, 1);
                $preprocessed = $this->processDefined($rest);
                $expanded = $this->expander->expand($preprocessed);
                $value = $this->evalConstExpr($expanded);
                $state['currentActive'] = $value !== 0;
                if ($value !== 0) {
                  $state['anyBranchTaken'] = true;
                }
              } else {
                $state['currentActive'] = false;
              }
              $condStack[count($condStack) - 1] = $state;
            }
            break;

          case 'else':
            if ($condStack !== []) {
              $state = $condStack[count($condStack) - 1];
              if ($state['parentActive'] && !$state['anyBranchTaken'] && !$state['seenElse']) {
                $state['currentActive'] = true;
                $state['anyBranchTaken'] = true;
              } else {
                $state['currentActive'] = false;
              }
              $state['seenElse'] = true;
              $condStack[count($condStack) - 1] = $state;
            }
            break;

          case 'endif':
            if ($condStack !== []) {
              array_pop($condStack);
            }
            break;

          case 'define':
          case 'undef':
          case 'include':
          case 'pragma':
          case 'line':
          case 'error':
          case 'warning':
            if ($isActive()) {
              $this->processDirective($directiveTokens, $currentFileId, $output);
            }
            break;

          default:
            if ($isActive()) {
              $this->processDirective($directiveTokens, $currentFileId, $output);
            }
            break;
        }

        $i = $nextI;
        continue;
      }

      if ($isActive()) {
        $batch = [];
        while ($i < count($tokens)) {
          $t = $tokens[$i];
          if ($t->kind === TokenKind::Punct && $t->spelling === '#' && $this->isAtLineStart($output, $tokens, $i)) {
            break;
          }
          $batch[] = $t;
          ++$i;
        }
        if ($batch !== []) {
          array_push($output, ...$this->expander->expand($batch));
        }
        continue;
      }

      ++$i;
    }

    return $output;
  }

  /**
   * @param list<Token> $output
   * @param list<Token> $tokens
   */
  private function isAtLineStart(array $output, array $tokens, int $idx): bool
  {
    if ($idx === 0) {
      return true;
    }

    $prevToken = $tokens[$idx - 1];
    $currToken = $tokens[$idx];

    if ($prevToken->loc->fileId !== $currToken->loc->fileId) {
      return true;
    }
    if ($prevToken->loc->fileId < 0) {
      return true;
    }

    $contents = $this->sm->contents($prevToken->loc->fileId);
    $prevEnd = $prevToken->loc->offset + strlen($prevToken->spelling);
    $currStart = $currToken->loc->offset;
    $len = strlen($contents);

    if ($prevEnd >= $len || $currStart > $len) {
      return true;
    }

    $j = $prevEnd;
    while ($j < $currStart) {
      if (ord($contents[$j]) === 0x0A) {
        if ($j > 0 && ord($contents[$j - 1]) === 0x5C) {
          ++$j;
          continue;
        }
        return true;
      }
      ++$j;
    }

    $pos = $currToken->loc->offset - 1;
    while ($pos >= 0) {
      $b = ord($contents[$pos]);
      if ($b === 0x0A) {
        return true;
      }
      if ($b !== 0x20 && $b !== 0x09 && $b !== 0x0D) {
        return false;
      }
      --$pos;
    }

    return true;
  }

  /**
   * @param list<Token> $tokens
   * @return array{0: list<Token>, 1: int}
   */
  private function collectDirectiveTokens(array $tokens, int $startIdx): array
  {
    $result = [];
    $i = $startIdx;

    while ($i < count($tokens) && $tokens[$i]->kind !== TokenKind::Eof) {
      $result[] = $tokens[$i];
      ++$i;

      if ($i < count($tokens) && $tokens[$i]->kind !== TokenKind::Eof && $i > $startIdx) {
        $last = $result[count($result) - 1];
        $prevEnd = $last->loc->offset + strlen($last->spelling);
        $currStart = $tokens[$i]->loc->offset;
        if ($currStart > $prevEnd && $last->loc->fileId === $tokens[$i]->loc->fileId) {
          $contents = $this->sm->contents($last->loc->fileId);
          $contentsLen = strlen($contents);
          if ($prevEnd < $contentsLen && $currStart <= $contentsLen) {
            $j = $prevEnd;
            while ($j < $currStart) {
              if (ord($contents[$j]) === 0x0A) {
                if ($j > 0 && ord($contents[$j - 1]) === 0x5C) {
                  ++$j;
                  continue;
                }
                return [$result, $i];
              }
              ++$j;
            }
          }
        }
      }
    }

    return [$result, $i];
  }

  /**
   * @param list<Token> $tokens
   * @param list<Token> $output
   */
  private function processDirective(array $tokens, int $currentFileId, array &$output): void
  {
    if ($tokens === []) {
      return;
    }

    $first = $tokens[0];

    if ($first->kind === TokenKind::Eof || ($first->kind === TokenKind::Punct && $first->spelling === '#')) {
      return;
    }

    if ($first->kind !== TokenKind::Identifier) {
      return;
    }

    $directive = $first->spelling;
    $rest = array_slice($tokens, 1);

    switch ($directive) {
      case 'define':
        $this->processDefine($rest);
        break;
      case 'undef':
        $this->processUndef($rest);
        break;
      case 'include':
        $this->processInclude($rest, $currentFileId, $output);
        break;
      case 'if':
      case 'ifdef':
      case 'ifndef':
      case 'elif':
      case 'else':
      case 'endif':
        break;
      case 'pragma':
      case 'line':
        break;
      case 'error':
        $msg = implode(' ', array_map(static fn (Token $t): string => $t->spelling, $rest));
        $this->diags->error("#error {$msg}", $first->loc);
        break;
      case 'warning':
        $msg = implode(' ', array_map(static fn (Token $t): string => $t->spelling, $rest));
        $this->diags->warning("#warning {$msg}", $first->loc);
        break;
      default:
        break;
    }
  }

  /**
   * @param list<Token> $tokens
   */
  private function processDefine(array $tokens): void
  {
    if ($tokens === [] || $tokens[0]->kind !== TokenKind::Identifier) {
      return;
    }

    $nameToken = $tokens[0];
    $rest = array_slice($tokens, 1);
    $isFunctionLike = false;
    $isVariadic = false;
    /** @var list<string> $params */
    $params = [];

    if (isset($rest[0]) && $rest[0]->kind === TokenKind::Punct && $rest[0]->spelling === '(') {
      $nameEnd = $nameToken->loc->offset + strlen($nameToken->spelling);
      if ($rest[0]->loc->offset === $nameEnd) {
        $isFunctionLike = true;
        $rest = array_slice($rest, 1);

        while ($rest !== []) {
          $t = $rest[0];
          if ($t->kind === TokenKind::Punct && $t->spelling === ')') {
            $rest = array_slice($rest, 1);
            break;
          }
          if ($t->kind === TokenKind::Punct && $t->spelling === ',') {
            $rest = array_slice($rest, 1);
            continue;
          }
          if ($t->kind === TokenKind::Punct && $t->spelling === '...') {
            $isVariadic = true;
            $rest = array_slice($rest, 1);
            if (isset($rest[0]) && $rest[0]->kind === TokenKind::Punct && $rest[0]->spelling === ')') {
              $rest = array_slice($rest, 1);
            }
            break;
          }
          if ($t->kind === TokenKind::Identifier) {
            $params[] = $t->spelling;
            $rest = array_slice($rest, 1);
            if (isset($rest[0]) && $rest[0]->kind === TokenKind::Punct && $rest[0]->spelling === '...') {
              $isVariadic = true;
              $rest = array_slice($rest, 1);
              if (isset($rest[0]) && $rest[0]->kind === TokenKind::Punct && $rest[0]->spelling === ')') {
                $rest = array_slice($rest, 1);
              }
              break;
            }
          } else {
            $rest = array_slice($rest, 1);
          }
        }
      }
    }

    $this->macroTable->define(new Macro(
      name: $nameToken->spelling,
      isFunctionLike: $isFunctionLike,
      isVariadic: $isVariadic,
      params: $params,
      body: $rest,
      loc: $nameToken->loc,
    ));
  }

  /**
   * @param list<Token> $tokens
   */
  private function processUndef(array $tokens): void
  {
    if ($tokens === [] || $tokens[0]->kind !== TokenKind::Identifier) {
      return;
    }
    $this->macroTable->undef($tokens[0]->spelling);
  }

  /**
   * @param list<Token> $tokens
   * @param list<Token> $output
   */
  private function processInclude(array $tokens, int $currentFileId, array &$output): void
  {
    if ($tokens === []) {
      return;
    }

    $first = $tokens[0];

    if ($first->kind === TokenKind::Punct && $first->spelling === '<') {
      $pathParts = [];
      $i = 1;
      while ($i < count($tokens) && !($tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === '>')) {
        $pathParts[] = $tokens[$i]->spelling;
        ++$i;
      }
      $this->includeFile(implode('', $pathParts), true, $currentFileId, $output);
      return;
    }

    if ($first->kind === TokenKind::StringLiteral) {
      $path = $first->spelling;
      if (str_starts_with($path, '"')) {
        $path = substr($path, 1);
      }
      if (str_ends_with($path, '"')) {
        $path = substr($path, 0, -1);
      }
      $this->includeFile($path, false, $currentFileId, $output);
      return;
    }

    $expanded = $this->expander->expand($tokens);
    if (isset($expanded[0]) && $expanded[0]->kind === TokenKind::StringLiteral) {
      $path = $expanded[0]->spelling;
      if (str_starts_with($path, '"')) {
        $path = substr($path, 1);
      }
      if (str_ends_with($path, '"')) {
        $path = substr($path, 0, -1);
      }
      $this->includeFile($path, false, $currentFileId, $output);
    }
  }

  /**
   * @param list<Token> $output
   */
  private function includeFile(string $path, bool $isSystem, int $currentFileId, array &$output): void
  {
    $resolvedPath = null;

    if (str_starts_with($path, '/')) {
      if (is_file($path)) {
        $resolvedPath = $path;
      }
    }

    if ($resolvedPath === null && !$isSystem) {
      $currentDir = dirname($this->sm->name($currentFileId));
      $candidate = $currentDir . '/' . $path;
      if (is_file($candidate)) {
        $resolvedPath = $candidate;
      }
    }

    if ($resolvedPath === null) {
      foreach ($this->includePaths as $incPath) {
        $candidate = $incPath . '/' . $path;
        if (is_file($candidate)) {
          $resolvedPath = $candidate;
          break;
        }
      }
    }

    if ($resolvedPath === null && $isSystem) {
      return;
    }

    if ($resolvedPath === null) {
      return;
    }

    $fileId = $this->sm->load($resolvedPath);
    $bytes = $this->sm->contents($fileId);
    $rawTokens = $this->lexTokens($bytes, $fileId, 0);
    $processed = $this->processTokens($rawTokens, $fileId);

    foreach ($processed as $t) {
      if ($t->kind !== TokenKind::Eof) {
        $output[] = $t;
      }
    }
  }

  /**
   * Process `defined(X)` / `defined X` in a #if expression, replacing with 1 or 0.
   * Must be done before macro expansion, per C99.
   *
   * @param list<Token> $tokens
   * @return list<Token>
   */
  private function processDefined(array $tokens): array
  {
    $result = [];
    $i = 0;
    $count = count($tokens);

    while ($i < $count) {
      $token = $tokens[$i];

      if ($token->kind === TokenKind::Identifier && in_array($token->spelling, [
        '__has_feature',
        '__has_extension',
        '__has_builtin',
        '__has_attribute',
      ], true)) {
        ++$i;
        if ($i < $count && $tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === '(') {
          $depth = 0;
          while ($i < $count) {
            if ($tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === '(') {
              ++$depth;
            } elseif ($tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === ')') {
              --$depth;
              if ($depth === 0) {
                ++$i;
                break;
              }
            }
            ++$i;
          }
        }
        $result[] = new Token(kind: TokenKind::IntegerLiteral, spelling: '0', loc: $token->loc);
        continue;
      }

      if ($token->kind === TokenKind::Identifier && $token->spelling === 'defined') {
        ++$i;
        if ($i < $count && $tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === '(') {
          ++$i;
          if ($i < $count && $tokens[$i]->kind === TokenKind::Identifier) {
            $name = $tokens[$i]->spelling;
            ++$i;
            if ($i < $count && $tokens[$i]->kind === TokenKind::Punct && $tokens[$i]->spelling === ')') {
              ++$i;
            }
            $value = $this->macroTable->isDefined($name) ? '1' : '0';
            $result[] = new Token(kind: TokenKind::IntegerLiteral, spelling: $value, loc: $token->loc);
            continue;
          }
        } elseif ($i < $count && $tokens[$i]->kind === TokenKind::Identifier) {
          $name = $tokens[$i]->spelling;
          ++$i;
          $value = $this->macroTable->isDefined($name) ? '1' : '0';
          $result[] = new Token(kind: TokenKind::IntegerLiteral, spelling: $value, loc: $token->loc);
          continue;
        }
        $result[] = new Token(kind: TokenKind::IntegerLiteral, spelling: '0', loc: $token->loc);
        continue;
      }

      $result[] = $token;
      ++$i;
    }

    return $result;
  }

  /**
   * @param list<Token> $tokens
   */
  private function evalConstExpr(array $tokens): int
  {
    $pos = 0;
    return $this->parseConditionalExpr($tokens, $pos);
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseConditionalExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseLogicalOrExpr($tokens, $pos);
    if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '?') {
      ++$pos;
      $mid = $this->parseConditionalExpr($tokens, $pos);
      if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === ':') {
        ++$pos;
      }
      $right = $this->parseConditionalExpr($tokens, $pos);
      return $left !== 0 ? $mid : $right;
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseLogicalOrExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseLogicalAndExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '||') {
      ++$pos;
      $right = $this->parseLogicalAndExpr($tokens, $pos);
      $left = ($left !== 0 || $right !== 0) ? 1 : 0;
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseLogicalAndExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseBitwiseOrExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '&&') {
      ++$pos;
      $right = $this->parseBitwiseOrExpr($tokens, $pos);
      $left = ($left !== 0 && $right !== 0) ? 1 : 0;
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseBitwiseOrExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseBitwiseXorExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '|') {
      ++$pos;
      $left |= $this->parseBitwiseXorExpr($tokens, $pos);
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseBitwiseXorExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseBitwiseAndExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '^') {
      ++$pos;
      $left ^= $this->parseBitwiseAndExpr($tokens, $pos);
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseBitwiseAndExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseEqualityExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '&') {
      ++$pos;
      $left &= $this->parseEqualityExpr($tokens, $pos);
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseEqualityExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseRelationalExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct
      && ($tokens[$pos]->spelling === '==' || $tokens[$pos]->spelling === '!=')) {
      $op = $tokens[$pos]->spelling;
      ++$pos;
      $right = $this->parseRelationalExpr($tokens, $pos);
      $left = $op === '==' ? ($left === $right ? 1 : 0) : ($left !== $right ? 1 : 0);
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseRelationalExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseShiftExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct
      && in_array($tokens[$pos]->spelling, ['<', '>', '<=', '>='], true)) {
      $op = $tokens[$pos]->spelling;
      ++$pos;
      $right = $this->parseShiftExpr($tokens, $pos);
      $left = match ($op) {
        '<' => $left < $right ? 1 : 0,
        '>' => $left > $right ? 1 : 0,
        '<=' => $left <= $right ? 1 : 0,
        '>=' => $left >= $right ? 1 : 0,
        default => $left,
      };
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseShiftExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseAddExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct
      && ($tokens[$pos]->spelling === '<<' || $tokens[$pos]->spelling === '>>')) {
      $op = $tokens[$pos]->spelling;
      ++$pos;
      $right = $this->parseAddExpr($tokens, $pos);
      if ($op === '<<') {
        $left <<= $right;
      } else {
        $left >>= $right;
      }
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseAddExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseMulExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct
      && ($tokens[$pos]->spelling === '+' || $tokens[$pos]->spelling === '-')) {
      $op = $tokens[$pos]->spelling;
      ++$pos;
      $right = $this->parseMulExpr($tokens, $pos);
      if ($op === '+') {
        $left += $right;
      } else {
        $left -= $right;
      }
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseMulExpr(array $tokens, int &$pos): int
  {
    $left = $this->parseUnaryExpr($tokens, $pos);
    while ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct
      && in_array($tokens[$pos]->spelling, ['*', '/', '%'], true)) {
      $op = $tokens[$pos]->spelling;
      ++$pos;
      $right = $this->parseUnaryExpr($tokens, $pos);
      $left = match ($op) {
        '*' => $left * $right,
        '/' => $right !== 0 ? intdiv($left, $right) : 0,
        '%' => $right !== 0 ? $left % $right : 0,
        default => $left,
      };
    }
    return $left;
  }

  /**
   * @param list<Token> $tokens
   */
  private function parseUnaryExpr(array $tokens, int &$pos): int
  {
    if ($pos >= count($tokens)) {
      return 0;
    }

    $token = $tokens[$pos];
    if ($token->kind === TokenKind::Punct) {
      switch ($token->spelling) {
        case '!':
          ++$pos;
          return $this->parseUnaryExpr($tokens, $pos) !== 0 ? 0 : 1;
        case '~':
          ++$pos;
          return ~$this->parseUnaryExpr($tokens, $pos);
        case '-':
          ++$pos;
          return -$this->parseUnaryExpr($tokens, $pos);
        case '+':
          ++$pos;
          return $this->parseUnaryExpr($tokens, $pos);
      }
    }

    if ($token->kind === TokenKind::Identifier && $token->spelling === 'defined') {
      ++$pos;
      if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === '(') {
        ++$pos;
        if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Identifier) {
          $name = $tokens[$pos]->spelling;
          ++$pos;
          if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === ')') {
            ++$pos;
          }
          return $this->macroTable->isDefined($name) ? 1 : 0;
        }
      } elseif ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Identifier) {
        $name = $tokens[$pos]->spelling;
        ++$pos;
        return $this->macroTable->isDefined($name) ? 1 : 0;
      }
      return 0;
    }

    return $this->parsePrimary($tokens, $pos);
  }

  /**
   * @param list<Token> $tokens
   */
  private function parsePrimary(array $tokens, int &$pos): int
  {
    if ($pos >= count($tokens)) {
      return 0;
    }

    $token = $tokens[$pos];
    ++$pos;

    if ($token->kind === TokenKind::Punct && $token->spelling === '(') {
      $val = $this->parseConditionalExpr($tokens, $pos);
      if ($pos < count($tokens) && $tokens[$pos]->kind === TokenKind::Punct && $tokens[$pos]->spelling === ')') {
        ++$pos;
      }
      return $val;
    }

    if ($token->kind === TokenKind::IntegerLiteral) {
      return $this->parseIntLiteral($token->spelling);
    }

    if ($token->kind === TokenKind::CharLiteral) {
      return $this->parseCharLiteralValue($token->spelling);
    }

    if ($token->kind === TokenKind::Identifier) {
      if ($token->spelling === 'true') {
        return 1;
      }
      return 0;
    }

    return 0;
  }

  private function parseCharLiteralValue(string $spelling): int
  {
    $s = $spelling;
    if (str_starts_with($s, 'L') || str_starts_with($s, 'u') || str_starts_with($s, 'U')) {
      $s = substr($s, 1);
    }
    if (!str_starts_with($s, "'") || !str_ends_with($s, "'")) {
      return 0;
    }
    $s = substr($s, 1, -1);
    if ($s === '') {
      return 0;
    }
    if (str_starts_with($s, '\\')) {
      return $this->parseEscapeSeq(substr($s, 1));
    }
    return ord($s[0]);
  }

  private function parseEscapeSeq(string $s): int
  {
    if ($s === '') {
      return 0;
    }
    $chars = str_split($s);
    switch ($chars[0]) {
      case 'n':
        return 10;
      case 't':
        return 9;
      case 'r':
        return 13;
      case '0':
      case '1':
      case '2':
      case '3':
      case '4':
      case '5':
      case '6':
      case '7':
        $result = 0;
        $i = 0;
        while ($i < 3 && $i < count($chars)) {
          $d = ord($chars[$i]);
          if ($d < 0x30 || $d > 0x37) {
            break;
          }
          $result = $result * 8 + ($d - 0x30);
          ++$i;
        }
        return $result;
      case 'x':
        $result = 0;
        $i = 1;
        while ($i < count($chars)) {
          $c = $chars[$i];
          if ($c >= '0' && $c <= '9') {
            $val = ord($c) - 0x30;
          } elseif ($c >= 'a' && $c <= 'f') {
            $val = ord($c) - 0x61 + 10;
          } elseif ($c >= 'A' && $c <= 'F') {
            $val = ord($c) - 0x41 + 10;
          } else {
            break;
          }
          $result = $result * 16 + $val;
          ++$i;
        }
        return $result;
      case '\\':
        return 0x5C;
      case "'":
        return 0x27;
      case '"':
        return 0x22;
      case 'a':
        return 7;
      case 'b':
        return 8;
      case 'f':
        return 12;
      case 'v':
        return 11;
      case 'e':
        return 27;
      default:
        return ord($chars[0]);
    }
  }

  private function parseIntLiteral(string $spelling): int
  {
    $s = $spelling;
    while ($s !== '' && in_array($s[strlen($s) - 1], ['u', 'U', 'l', 'L'], true)) {
      $s = substr($s, 0, -1);
    }
    if (str_starts_with($s, '0x') || str_starts_with($s, '0X')) {
      return (int) hexdec(substr($s, 2));
    }
    if (str_starts_with($s, '0b') || str_starts_with($s, '0B')) {
      return (int) bindec(substr($s, 2));
    }
    if (str_starts_with($s, '0') && strlen($s) > 1) {
      return (int) octdec(substr($s, 1));
    }
    return (int) $s;
  }

  /**
   * Lex macro replacement text and strip trailing EOF tokens (456ddfa fix).
   *
   * @return list<Token>
   */
  private function lexTokensWithoutEof(string $value): array
  {
    $tokens = $this->lexTokens($value, -1, 0);
    return array_values(array_filter($tokens, static fn (Token $t): bool => $t->kind !== TokenKind::Eof));
  }

  /**
   * @return list<Token>
   */
  private function lexTokens(string $source, int $fileId, int $startOffset): array
  {
    $lexer = new Lexer($source, $fileId, $startOffset);
    return $lexer->tokenize();
  }
}
