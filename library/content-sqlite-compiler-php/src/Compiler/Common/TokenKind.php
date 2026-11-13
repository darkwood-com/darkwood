<?php

declare(strict_types=1);

namespace App\Compiler\Common;

/** C token kinds. */
enum TokenKind
{
  case Identifier;
  case Keyword;
  case IntegerLiteral;
  case FloatLiteral;
  case CharLiteral;
  case StringLiteral;
  case Punct;
  case Eof;
}
