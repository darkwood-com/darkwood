<?php

declare(strict_types=1);

namespace App\Compiler\Preproc;

use App\Compiler\Common\DiagnosticEngine;
use App\Compiler\Common\SourceLoc;
use App\Compiler\Common\SourceManager;
use App\Compiler\Common\Token;
use App\Compiler\Common\TokenKind;

/** Represents a preprocessor macro definition. */
final readonly class Macro
{
  /**
   * @param list<string> $params
   * @param list<Token> $body
   */
  public function __construct(
    public string $name,
    public bool $isFunctionLike,
    public bool $isVariadic,
    public array $params,
    public array $body,
    public SourceLoc $loc,
  ) {
  }
}

/** Manages a collection of defined macros. */
final class MacroTable
{
  /** @var array<string, Macro> */
  private array $macros = [];

  /** @var list<string> Names currently being expanded (blue-painting / hide sets). */
  private array $expansionStack = [];

  public function define(Macro $macro): void
  {
    $this->macros[$macro->name] = $macro;
  }

  public function undef(string $name): void
  {
    unset($this->macros[$name]);
  }

  public function isDefined(string $name): bool
  {
    return isset($this->macros[$name]);
  }

  public function lookup(string $name): ?Macro
  {
    return $this->macros[$name] ?? null;
  }

  public function pushExpansion(string $name): void
  {
    $this->expansionStack[] = $name;
  }

  public function popExpansion(): void
  {
    array_pop($this->expansionStack);
  }

  public function isExpanding(string $name): bool
  {
    return in_array($name, $this->expansionStack, true);
  }

  public function clear(): void
  {
    $this->macros = [];
    $this->expansionStack = [];
  }
}

/**
 * Expands macros in a token stream following C99 rules (rescanning + blue-painting).
 * Uses a mutable index-based approach for correct rescanning.
 */
final class MacroExpander
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
  ];

  public function __construct(
    private readonly MacroTable $table,
    private readonly DiagnosticEngine $diags,
    private readonly ?SourceManager $sm = null,
  ) {
  }

  /**
   * Expand all macros in the given token list, returning the fully expanded token list.
   * Uses hide sets for blue-painting.
   *
   * @param list<Token> $tokens
   * @return list<Token>
   */
  public function expand(array $tokens): array
  {
    $output = [];
    /** @var list<Token> */
    $pending = [];
    $input = $tokens;
    $inputPos = 0;
    $inputCount = count($tokens);
    /** @var list<Token> */
    $pendingReversed = [];

    $nextToken = function () use (&$pendingReversed, &$pending, &$input, &$inputPos, $inputCount): ?Token {
      if ($pendingReversed !== []) {
        return array_pop($pendingReversed);
      }
      if ($pending !== []) {
        $pendingReversed = array_reverse($pending);
        $pending = [];

        return array_pop($pendingReversed);
      }
      if ($inputPos < $inputCount) {
        return $input[$inputPos++];
      }

      return null;
    };

    $peekToken = function () use (&$pendingReversed, &$pending, &$input, &$inputPos, $inputCount): ?Token {
      for ($i = count($pendingReversed) - 1; $i >= 0; --$i) {
        $t = $pendingReversed[$i];
        if ($t->kind !== TokenKind::Eof) {
          return $t;
        }
      }
      foreach ($pending as $t) {
        if ($t->kind !== TokenKind::Eof) {
          return $t;
        }
      }
      for ($i = $inputPos; $i < $inputCount; ++$i) {
        $t = $input[$i];
        if ($t->kind !== TokenKind::Eof) {
          return $t;
        }
      }

      return null;
    };

    /**
     * @return list<list<Token>>|null
     */
    $parseFunctionCallArgs = function () use ($nextToken): ?array {
      $first = $nextToken();
      if ($first === null || $first->kind !== TokenKind::Punct || $first->spelling !== '(') {
        return null;
      }

      $args = [];
      $current = [];
      $depth = 0;

      while (($t = $nextToken()) !== null) {
        if ($t->kind === TokenKind::Eof) {
          continue;
        }
        if ($t->kind === TokenKind::Punct) {
          if ($t->spelling === '(') {
            ++$depth;
            $current[] = $t;
          } elseif ($t->spelling === ')') {
            if ($depth === 0) {
              if ($current !== [] || $args !== []) {
                $args[] = $current;
              }
              return $args;
            }
            --$depth;
            $current[] = $t;
          } elseif ($t->spelling === ',' && $depth === 0) {
            $args[] = $current;
            $current = [];
          } else {
            $current[] = $t;
          }
        } else {
          $current[] = $t;
        }
      }

      return null;
    };

    while (($token = $nextToken()) !== null) {
      if ($token->kind !== TokenKind::Identifier) {
        $output[] = $token;
        continue;
      }

      if ($token->spelling === '__LINE__') {
        $line = 0;
        if ($this->sm !== null) {
          $line = $this->sm->lineCol($token->loc)['line'];
        }
        $output[] = new Token(
          kind: TokenKind::IntegerLiteral,
          spelling: (string) $line,
          loc: $token->loc,
        );
        continue;
      }

      if ($token->spelling === '__FILE__') {
        $fname = '';
        if ($this->sm !== null && $token->loc->fileId >= 0) {
          $fname = $this->sm->name($token->loc->fileId);
        }
        $output[] = new Token(
          kind: TokenKind::StringLiteral,
          spelling: '"' . $fname . '"',
          loc: $token->loc,
        );
        continue;
      }

      if ($token->hideSet !== [] && in_array($token->spelling, $token->hideSet, true)) {
        $output[] = $token;
        continue;
      }

      $macro = $this->table->lookup($token->spelling);
      if ($macro === null) {
        $output[] = $token;
        continue;
      }

      if ($macro->isFunctionLike) {
        $peek = $peekToken();
        if ($peek === null || $peek->kind !== TokenKind::Punct || $peek->spelling !== '(') {
          $output[] = $token;
          continue;
        }

        $args = $parseFunctionCallArgs();
        if ($args === null) {
          $output[] = $token;
          continue;
        }

        $newHideSet = $this->unionHideSet($token->hideSet, [$macro->name]);
        $expanded = $this->expandFunctionLike($macro, $args, $token->loc, $newHideSet);
        for ($i = count($expanded) - 1; $i >= 0; --$i) {
          $pendingReversed[] = $expanded[$i];
        }
      } else {
        $newHideSet = $this->unionHideSet($token->hideSet, [$macro->name]);
        $expanded = $this->expandObjectLike($macro, $token->loc, $newHideSet);
        for ($i = count($expanded) - 1; $i >= 0; --$i) {
          $pendingReversed[] = $expanded[$i];
        }
      }
    }

    return $output;
  }

  /**
   * @param list<list<Token>> $args
   * @param list<string> $hideSet
   * @return list<Token>
   */
  private function expandObjectLike(Macro $macro, SourceLoc $invocationLoc, array $hideSet): array
  {
    return $this->substitute($macro->body, null, null, $macro, $invocationLoc, $hideSet);
  }

  /**
   * @param list<list<Token>> $args
   * @param list<string> $hideSet
   * @return list<Token>
   */
  private function expandFunctionLike(Macro $macro, array $args, SourceLoc $invocationLoc, array $hideSet): array
  {
    if (!$macro->isVariadic && count($args) !== count($macro->params)) {
      if (!(count($args) === 1 && $args[0] === [] && count($macro->params) === 0)) {
        $this->diags->error(
          "macro '{$macro->name}' requires " . count($macro->params) . ' arguments, but ' . count($args) . ' given',
          $invocationLoc,
        );
      }
    }

    /** @var array<string, list<Token>> $argMap */
    $argMap = [];
    foreach ($macro->params as $idx => $param) {
      $argMap[$param] = $args[$idx] ?? [];
    }

    if ($macro->isVariadic) {
      $vaArgs = [];
      for ($idx = count($macro->params); $idx < count($args); ++$idx) {
        if ($vaArgs !== []) {
          $vaArgs[] = new Token(kind: TokenKind::Punct, spelling: ',', loc: $invocationLoc);
        }
        array_push($vaArgs, ...$args[$idx]);
      }
      $argMap['__VA_ARGS__'] = $vaArgs;
    }

    return $this->substitute($macro->body, $macro->params, $argMap, $macro, $invocationLoc, $hideSet);
  }

  /**
   * @param list<Token> $body
   * @param list<string>|null $params
   * @param array<string, list<Token>>|null $args
   * @param list<string> $hideSet
   * @return list<Token>
   */
  private function substitute(
    array $body,
    ?array $params,
    ?array $args,
    Macro $macro,
    SourceLoc $invocationLoc,
    array $hideSet,
  ): array {
    $result = [];
    $i = 0;
    $count = count($body);

    while ($i < $count) {
      $token = $body[$i];

      // # operator (stringize) — only in function-like macros
      if ($token->kind === TokenKind::Punct && $token->spelling === '#' && $macro->isFunctionLike) {
        if ($i + 1 < $count) {
          $nextToken = $body[$i + 1];
          if ($nextToken->kind === TokenKind::Identifier && $args !== null && array_key_exists($nextToken->spelling, $args)) {
            $stringized = $this->stringize($args[$nextToken->spelling] ?? []);
            $result[] = new Token(
              kind: TokenKind::StringLiteral,
              spelling: $stringized,
              loc: $invocationLoc,
              hideSet: $hideSet,
            );
            $i += 2;
            continue;
          }
          if ($nextToken->kind === TokenKind::Identifier && $nextToken->spelling === '__VA_ARGS__' && $macro->isVariadic) {
            $stringized = $this->stringize($args['__VA_ARGS__'] ?? []);
            $result[] = new Token(
              kind: TokenKind::StringLiteral,
              spelling: $stringized,
              loc: $invocationLoc,
              hideSet: $hideSet,
            );
            $i += 2;
            continue;
          }
        }
      }

      // ## operator (token paste)
      if ($token->kind === TokenKind::Punct && $token->spelling === '##') {
        if ($i + 1 < $count && $result !== []) {
          $rightToken = $body[$i + 1];
          $left = array_pop($result);
          $pasted = $this->pasteTokens($left, $rightToken, $args, $params, $hideSet);
          $result[] = $pasted;
          $i += 2;
          continue;
        }
      }

      // Check if the NEXT token is ## — if so, don't expand the current parameter (use raw arg)
      if ($token->kind === TokenKind::Identifier && $params !== null && in_array($token->spelling, $params, true)) {
        if ($i + 1 < $count && $body[$i + 1]->kind === TokenKind::Punct && $body[$i + 1]->spelling === '##') {
          $argTokens = $args[$token->spelling] ?? [];
          if ($argTokens === []) {
            ++$i;
            continue;
          }
          foreach ($argTokens as $t) {
            $result[] = $t->withHideSet($this->unionHideSet($t->hideSet, $hideSet));
          }
          ++$i;
          continue;
        }
      }

      // Parameter substitution (expand the argument before substitution)
      if ($token->kind === TokenKind::Identifier && $params !== null && in_array($token->spelling, $params, true)) {
        $argTokens = $args[$token->spelling] ?? [];
        $expanded = $this->expand($argTokens);
        foreach ($expanded as $t) {
          $result[] = $t->withHideSet($this->unionHideSet($t->hideSet, $hideSet));
        }
        ++$i;
        continue;
      }

      // __VA_ARGS__ substitution
      if ($token->kind === TokenKind::Identifier && $token->spelling === '__VA_ARGS__' && $macro->isVariadic) {
        $argTokens = $args['__VA_ARGS__'] ?? [];
        $expanded = $this->expand($argTokens);
        foreach ($expanded as $t) {
          $result[] = $t->withHideSet($this->unionHideSet($t->hideSet, $hideSet));
        }
        ++$i;
        continue;
      }

      $result[] = $token->withHideSet($this->unionHideSet($token->hideSet, $hideSet));
      ++$i;
    }

    return $result;
  }

  /**
   * @param list<Token> $tokens
   */
  private function stringize(array $tokens): string
  {
    $s = '"';
    foreach ($tokens as $i => $token) {
      if ($i > 0) {
        $s .= ' ';
      }
      if ($token->kind === TokenKind::StringLiteral || $token->kind === TokenKind::CharLiteral) {
        $s .= str_replace(['\\', '"'], ['\\\\', '\\"'], $token->spelling);
      } else {
        $s .= $token->spelling;
      }
    }
    $s .= '"';

    return $s;
  }

  /**
   * @param array<string, list<Token>>|null $args
   * @param list<string>|null $params
   * @param list<string> $hideSet
   */
  private function pasteTokens(
    Token $left,
    Token $right,
    ?array $args,
    ?array $params,
    array $hideSet,
  ): Token {
    $rightSpelling = $right->spelling;

    if ($right->kind === TokenKind::Identifier && $params !== null && in_array($right->spelling, $params, true)) {
      $argTokens = $args[$right->spelling] ?? [];
      if ($argTokens === []) {
        return $left->withHideSet($this->unionHideSet($left->hideSet, $hideSet));
      }
      $rightSpelling = $argTokens[0]->spelling;
    }

    $pasted = $left->spelling . $rightSpelling;

    if (in_array($pasted, self::C_KEYWORDS, true)) {
      $kind = TokenKind::Keyword;
    } elseif ($left->kind === TokenKind::IntegerLiteral && $right->kind === TokenKind::IntegerLiteral) {
      $kind = TokenKind::IntegerLiteral;
    } elseif ($left->kind === TokenKind::FloatLiteral || $right->kind === TokenKind::FloatLiteral) {
      $kind = TokenKind::FloatLiteral;
    } elseif ($left->kind === TokenKind::Punct && $right->kind === TokenKind::Punct) {
      $kind = TokenKind::Punct;
    } else {
      $kind = TokenKind::Identifier;
    }

    return new Token(
      kind: $kind,
      spelling: $pasted,
      loc: $left->loc,
      hideSet: $this->unionHideSet($left->hideSet, $hideSet),
    );
  }

  /**
   * @param list<string> $a
   * @param list<string> $b
   * @return list<string>
   */
  private function unionHideSet(array $a, array $b): array
  {
    if ($a === []) {
      return $b;
    }
    if ($b === []) {
      return $a;
    }

    return array_values(array_unique([...$a, ...$b]));
  }
}
