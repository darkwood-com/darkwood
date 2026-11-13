<?php

declare(strict_types=1);

namespace App\Compiler\Parser;

use App\Compiler\Common\ArrayCType;
use App\Compiler\Common\AssignExpr;
use App\Compiler\Common\AssignOp;
use App\Compiler\Common\BinaryExpr;
use App\Compiler\Common\BinaryOp;
use App\Compiler\Common\BoolCType;
use App\Compiler\Common\BreakStmt;
use App\Compiler\Common\CallExpr;
use App\Compiler\Common\CaseStmt;
use App\Compiler\Common\CastExpr;
use App\Compiler\Common\CharCType;
use App\Compiler\Common\CharLiteral;
use App\Compiler\Common\CompoundLiteralExpr;
use App\Compiler\Common\CompoundStmt;
use App\Compiler\Common\ConditionalExpr;
use App\Compiler\Common\ContinueStmt;
use App\Compiler\Common\CType;
use App\Compiler\Common\Decl;
use App\Compiler\Common\DeclStmt;
use App\Compiler\Common\DefaultStmt;
use App\Compiler\Common\DiagnosticEngine;
use App\Compiler\Common\DoWhileStmt;
use App\Compiler\Common\DoubleCType;
use App\Compiler\Common\EmptyStmt;
use App\Compiler\Common\EnumCase;
use App\Compiler\Common\EnumCType;
use App\Compiler\Common\EnumDecl;
use App\Compiler\Common\EnumType;
use App\Compiler\Common\Expr;
use App\Compiler\Common\ExprStmt;
use App\Compiler\Common\FloatCType;
use App\Compiler\Common\FloatLiteral;
use App\Compiler\Common\ForStmt;
use App\Compiler\Common\FuncDecl;
use App\Compiler\Common\FunctionCType;
use App\Compiler\Common\GotoStmt;
use App\Compiler\Common\Identifier;
use App\Compiler\Common\IfStmt;
use App\Compiler\Common\IncompleteArrayCType;
use App\Compiler\Common\InitListExpr;
use App\Compiler\Common\IntCType;
use App\Compiler\Common\IntegerLiteral;
use App\Compiler\Common\LabelStmt;
use App\Compiler\Common\LongCType;
use App\Compiler\Common\LongDoubleCType;
use App\Compiler\Common\LongLongCType;
use App\Compiler\Common\MemberExpr;
use App\Compiler\Common\Param;
use App\Compiler\Common\PointerCType;
use App\Compiler\Common\QualifiedCType;
use App\Compiler\Common\RecordField;
use App\Compiler\Common\RecordType;
use App\Compiler\Common\ReturnStmt;
use App\Compiler\Common\ShortCType;
use App\Compiler\Common\SizeofExpr;
use App\Compiler\Common\SourceLoc;
use App\Compiler\Common\StaticAssertDecl;
use App\Compiler\Common\Stmt;
use App\Compiler\Common\StorageClass;
use App\Compiler\Common\StringLiteral;
use App\Compiler\Common\StructCType;
use App\Compiler\Common\StructDecl;
use App\Compiler\Common\SubscriptExpr;
use App\Compiler\Common\SwitchStmt;
use App\Compiler\Common\Token;
use App\Compiler\Common\TokenKind;
use App\Compiler\Common\TypedefCType;
use App\Compiler\Common\TypedefDecl;
use App\Compiler\Common\UCharCType;
use App\Compiler\Common\UIntCType;
use App\Compiler\Common\ULongCType;
use App\Compiler\Common\ULongLongCType;
use App\Compiler\Common\UnionCType;
use App\Compiler\Common\UnionDecl;
use App\Compiler\Common\UnaryExpr;
use App\Compiler\Common\UnaryOp;
use App\Compiler\Common\UShortCType;
use App\Compiler\Common\VarDecl;
use App\Compiler\Common\VoidCType;
use App\Compiler\Common\WhileStmt;

/**
 * Recursive descent C99 parser: token stream → AST.
 */
final class Parser
{
    /** @var list<Token> */
    private array $tokens;

    private int $pos = 0;

    private int $tokenCount = 0;

    private DiagnosticEngine $diags;

    /** @var array<string, true> */
    private array $typedefNames = [];

    /** @var array<string, RecordType> */
    private array $completedRecords = [];

    /** @var array<string, CType> */
    private array $typedefTypes = [];

    /** @var list<Param> */
    private array $lastFuncParams = [];

    private bool $lastFuncVariadic = false;

    /**
     * @param list<Token> $tokens
     */
    public function __construct(array $tokens, ?DiagnosticEngine $diags = null)
    {
        // Preprocessor emits a single trailing Eof. Avoid copying the whole stream
        // just to drop it — track a logical length instead.
        $this->tokens = $tokens;
        $n = count($tokens);
        while ($n > 0 && $tokens[$n - 1]->kind === TokenKind::Eof) {
            --$n;
        }
        $this->tokenCount = $n;
        $this->diags = $diags ?? new DiagnosticEngine();
    }

    /**
     * Known typedef names (lexer hack: distinguish typedef names from identifiers).
     *
     * @return array<string, true>
     */
    public function getTypedefNames(): array
    {
        return $this->typedefNames;
    }

    /**
     * Register a typedef name (for lexer/preprocessor coordination).
     */
    public function addTypedefName(string $name, ?CType $type = null): void
    {
        $this->typedefNames[$name] = true;
        if ($type !== null) {
            $this->typedefTypes[$name] = $type;
        }
    }

    /**
     * @return list<Decl>
     */
    public function parse(): array
    {
        $decls = [];
        while (!$this->isAtEnd()) {
            try {
                $decl = $this->parseExternalDecl();
                if ($decl !== null) {
                    $decls[] = $decl;
                }
            } catch (ParseError $error) {
                match ($error->errorKind) {
                    ParseError::KIND_UNEXPECTED => $this->diags->error(
                        'syntax error: ' . $error->detail1,
                        $error->loc,
                    ),
                    ParseError::KIND_EXPECTED => $this->diags->error(
                        'expected ' . $error->detail1 . ", got '" . $error->detail2 . "'",
                        $error->loc,
                    ),
                    ParseError::KIND_UNDECLARED => $this->diags->error(
                        "undeclared identifier '" . $error->detail1 . "'",
                        $error->loc,
                    ),
                    default => $this->diags->error('syntax error', $error->loc),
                };
                $this->synchronize();
            }
        }

        return $decls;
    }

    // MARK: - Token helpers

    private function peek(int $offset = 0): Token
    {
        $idx = $this->pos + $offset;
        if ($idx >= $this->tokenCount) {
            return new Token(
                kind: TokenKind::Eof,
                spelling: '',
                loc: SourceLoc::unknown(),
            );
        }

        return $this->tokens[$idx];
    }

    private function current(): Token
    {
        return $this->peek(0);
    }

    private function next(): Token
    {
        return $this->peek(1);
    }

    private function isAtEnd(): bool
    {
        return $this->pos >= $this->tokenCount;
    }

    private function advance(): Token
    {
        $t = $this->current();
        if ($this->pos < $this->tokenCount) {
            ++$this->pos;
        }

        return $t;
    }

    private function check(TokenKind $kind, ?string $spelling = null): bool
    {
        if ($this->current()->kind !== $kind) {
            return false;
        }
        if ($spelling !== null) {
            return $this->current()->spelling === $spelling;
        }

        return true;
    }

    private function match(TokenKind $kind, ?string $spelling = null): bool
    {
        if ($this->check($kind, $spelling)) {
            $this->advance();

            return true;
        }

        return false;
    }

    /**
     * @throws ParseError
     */
    private function consume(TokenKind $kind, ?string $spelling = null): Token
    {
        if ($this->check($kind, $spelling)) {
            return $this->advance();
        }
        $exp = $spelling ?? $this->kindDescription($kind);
        throw ParseError::expected($exp, $this->current()->spelling, $this->current()->loc);
    }

    private function kindDescription(TokenKind $k): string
    {
        return match ($k) {
            TokenKind::Identifier => 'identifier',
            TokenKind::Keyword => 'keyword',
            TokenKind::IntegerLiteral => 'integer literal',
            TokenKind::FloatLiteral => 'float literal',
            TokenKind::CharLiteral => 'char literal',
            TokenKind::StringLiteral => 'string literal',
            TokenKind::Punct => 'punctuator',
            TokenKind::Eof => 'end of file',
        };
    }

    private function isPunct(string $s): bool
    {
        return $this->current()->kind === TokenKind::Punct && $this->current()->spelling === $s;
    }

    private function isKeyword(string $s): bool
    {
        return $this->current()->kind === TokenKind::Keyword && $this->current()->spelling === $s;
    }

    private function isIdentifier(?string $s = null): bool
    {
        if ($this->current()->kind !== TokenKind::Identifier) {
            return false;
        }
        if ($s !== null) {
            return $this->current()->spelling === $s;
        }

        return true;
    }

    private function isTypedefName(): bool
    {
        return $this->current()->kind === TokenKind::Identifier
            && isset($this->typedefNames[$this->current()->spelling]);
    }

    private function isForwardTypedef(): bool
    {
        return $this->next()->kind === TokenKind::Identifier;
    }

    // MARK: - Error recovery

    private function synchronize(): void
    {
        while (!$this->isAtEnd()) {
            if ($this->isPunct(';')) {
                $this->advance();

                return;
            }
            if ($this->isPunct('}')) {
                $this->advance();

                return;
            }
            if ($this->current()->kind === TokenKind::Keyword) {
                $kw = $this->current()->spelling;
                if (in_array($kw, [
                    'int', 'char', 'short', 'long', 'float', 'double', 'void',
                    'unsigned', 'signed', 'const', 'static', 'extern', 'struct',
                    'union', 'enum', 'typedef', 'inline', 'register', 'volatile',
                    '_Bool', 'auto',
                ], true)) {
                    return;
                }
            }
            $this->advance();
        }
    }

    // MARK: - External declarations

    private function skipAsmAndAttributes(): void
    {
        while ($this->isKeyword('__asm') || $this->isKeyword('__asm__')
            || $this->isKeyword('__attribute__') || $this->isKeyword('__attribute')) {
            $this->advance();
            if ($this->isPunct('(')) {
                $depth = 0;
                while (!$this->isAtEnd()) {
                    if ($this->isPunct('(')) {
                        ++$depth;
                    } elseif ($this->isPunct(')')) {
                        --$depth;
                        if ($depth === 0) {
                            $this->advance();
                            break;
                        }
                    }
                    $this->advance();
                }
            }
        }
    }

    /**
     * @throws ParseError
     */
    private function parseExternalDecl(): ?Decl
    {
        if ($this->isPunct(';')) {
            $this->advance();

            return null;
        }

        if ($this->isKeyword('_Static_assert')) {
            return $this->parseStaticAssert();
        }

        if ($this->isKeyword('typedef')) {
            return $this->parseTypedef();
        }

        [$baseType, $storageClass, $isInline] = $this->parseDeclSpecifiers();

        if ($this->isPunct(';')) {
            $this->advance();
            if ($baseType instanceof StructCType) {
                return new StructDecl(
                    name: $baseType->record->name !== '' ? $baseType->record->name : null,
                    record: $baseType->record,
                    loc: SourceLoc::unknown(),
                );
            }
            if ($baseType instanceof UnionCType) {
                return new UnionDecl(
                    name: $baseType->record->name !== '' ? $baseType->record->name : null,
                    record: $baseType->record,
                    loc: SourceLoc::unknown(),
                );
            }
            if ($baseType instanceof EnumCType) {
                return new EnumDecl(
                    name: $baseType->enumType->name !== '' ? $baseType->enumType->name : null,
                    enumType: $baseType->enumType,
                    loc: SourceLoc::unknown(),
                );
            }

            return null;
        }

        $firstDecl = null;
        do {
            [$name, $type, $loc] = $this->parseDeclarator($baseType);
            $this->skipAsmAndAttributes();

            if ($this->isPunct('{') && $type->isFunction()) {
                $savedParams = $this->lastFuncParams;
                $savedVariadic = $this->lastFuncVariadic;
                $body = $this->parseCompoundStmt();
                $returnType = $type instanceof FunctionCType ? $type->returnType : $type;

                return new FuncDecl(
                    name: $name,
                    returnType: $returnType,
                    params: $savedParams,
                    variadic: $savedVariadic,
                    loc: $loc,
                    body: $body,
                    storageClass: $storageClass,
                    isInline: $isInline,
                );
            }

            $initExpr = null;
            $actualType = $type;
            if ($this->match(TokenKind::Punct, '=')) {
                $initExpr = $this->parseInitializer($type);
                $actualType = $this->inferArrayTypeFromInitializer($type, $initExpr) ?? $type;
            }

            if ($type->isFunction()) {
                $returnType = $type instanceof FunctionCType ? $type->returnType : $type;
                $fd = new FuncDecl(
                    name: $name,
                    returnType: $returnType,
                    params: $this->lastFuncParams,
                    variadic: $this->lastFuncVariadic,
                    loc: $loc,
                    body: null,
                    storageClass: $storageClass,
                    isInline: $isInline,
                );
                if ($firstDecl === null) {
                    $firstDecl = $fd;
                }
            } else {
                $varDecl = new VarDecl(
                    name: $name,
                    type: $actualType,
                    loc: $loc,
                    initializer: $initExpr,
                    storageClass: $storageClass,
                    isGlobal: true,
                );
                if ($firstDecl === null) {
                    $firstDecl = $varDecl;
                }
            }
        } while ($this->match(TokenKind::Punct, ','));

        if (!$this->isPunct(';')) {
            throw ParseError::expected("';'", $this->current()->spelling, $this->current()->loc);
        }
        $this->consume(TokenKind::Punct, ';');

        return $firstDecl;
    }

    private function inferArrayTypeFromInitializer(CType $type, Expr $initExpr): ?CType
    {
        $unqual = $type->unqualified();
        if ($unqual instanceof IncompleteArrayCType) {
            if ($initExpr instanceof InitListExpr) {
                return new ArrayCType($unqual->of, count($initExpr->values));
            }
            if ($unqual->of->isChar() && $initExpr instanceof StringLiteral) {
                return new ArrayCType($unqual->of, strlen($initExpr->value) + 1);
            }
        }

        return null;
    }

    /**
     * @throws ParseError
     */
    private function parseStaticAssert(): StaticAssertDecl
    {
        $loc = $this->advance()->loc;
        $this->consume(TokenKind::Punct, '(');
        $cond = $this->parseAssignmentExpr();
        $msg = null;
        if ($this->match(TokenKind::Punct, ',')) {
            if ($this->current()->kind === TokenKind::StringLiteral) {
                $msg = $this->current()->spelling;
                $this->advance();
            }
        }
        $this->consume(TokenKind::Punct, ')');
        $this->consume(TokenKind::Punct, ';');

        return new StaticAssertDecl(condition: $cond, message: $msg, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseTypedef(): TypedefDecl
    {
        $loc = $this->advance()->loc;
        [$baseType] = $this->parseDeclSpecifiers();
        $firstDecl = null;

        do {
            [$name, $type, $declLoc] = $this->parseDeclarator($baseType);
            $this->typedefNames[$name] = true;
            $this->typedefTypes[$name] = $type;
            $td = new TypedefDecl(name: $name, type: $type, loc: $declLoc);
            if ($firstDecl === null) {
                $firstDecl = $td;
            }
        } while ($this->match(TokenKind::Punct, ','));

        $this->consume(TokenKind::Punct, ';');

        return $firstDecl ?? new TypedefDecl(name: '', type: IntCType::$instance, loc: $loc);
    }

    /**
     * @return array{CType, StorageClass, bool}
     *
     * @throws ParseError
     */
    private function parseDeclSpecifiers(): array
    {
        $storageClass = StorageClass::None;
        $isInline = false;
        $typeSpecifiers = [];
        $structType = null;
        $unionType = null;
        $enumType = null;
        $typedefBase = null;
        $isConst = false;
        $isVolatile = false;
        $isRestrict = false;

        $done = false;
        while (!$done && !$this->isAtEnd()) {
            $t = $this->current();

            if ($t->kind === TokenKind::Keyword) {
                switch ($t->spelling) {
                    case 'static':
                        $storageClass = StorageClass::Static;
                        $this->advance();
                        break;
                    case 'extern':
                        $storageClass = StorageClass::Extern;
                        $this->advance();
                        break;
                    case 'register':
                        $storageClass = StorageClass::Register;
                        $this->advance();
                        break;
                    case 'auto':
                        $storageClass = StorageClass::Auto;
                        $this->advance();
                        break;
                    case 'typedef':
                        $this->advance();
                        break;
                    case 'const':
                    case '__const':
                    case '__const__':
                        $isConst = true;
                        $this->advance();
                        break;
                    case 'volatile':
                    case '__volatile':
                    case '__volatile__':
                        $isVolatile = true;
                        $this->advance();
                        break;
                    case 'restrict':
                    case '__restrict':
                    case '__restrict__':
                        $isRestrict = true;
                        $this->advance();
                        break;
                    case '__inline':
                    case '__inline__':
                    case 'inline':
                        $isInline = true;
                        $this->advance();
                        break;
                    case '__signed':
                    case '__signed__':
                        $typeSpecifiers[] = 'signed';
                        $this->advance();
                        break;
                    case 'void':
                    case 'char':
                    case 'short':
                    case 'int':
                    case 'long':
                    case 'float':
                    case 'double':
                    case 'signed':
                    case 'unsigned':
                    case '_Bool':
                        $typeSpecifiers[] = $t->spelling;
                        $this->advance();
                        break;
                    case 'struct':
                        $structType = $this->parseStructOrUnion(isStruct: true);
                        break;
                    case 'union':
                        $unionType = $this->parseStructOrUnion(isStruct: false);
                        break;
                    case 'enum':
                        $enumType = $this->parseEnumSpec();
                        break;
                    case '__attribute__':
                    case '__attribute':
                        $this->advance();
                        if ($this->isPunct('(')) {
                            $depth = 0;
                            while (!$this->isAtEnd()) {
                                if ($this->isPunct('(')) {
                                    ++$depth;
                                    $this->advance();
                                } elseif ($this->isPunct(')')) {
                                    --$depth;
                                    $this->advance();
                                    if ($depth === 0) {
                                        break;
                                    }
                                } else {
                                    $this->advance();
                                }
                            }
                        }
                        break;
                    case '__asm':
                    case '__asm__':
                        $this->advance();
                        if ($this->isPunct('(')) {
                            $depth = 0;
                            while (!$this->isAtEnd()) {
                                if ($this->isPunct('(')) {
                                    ++$depth;
                                    $this->advance();
                                } elseif ($this->isPunct(')')) {
                                    --$depth;
                                    $this->advance();
                                    if ($depth === 0) {
                                        break;
                                    }
                                } else {
                                    $this->advance();
                                }
                            }
                        }
                        break;
                    case '__extension__':
                    case '__typeof':
                    case '__typeof__':
                        $this->advance();
                        break;
                    default:
                        $done = true;
                        break;
                }
            } elseif ($this->isTypedefName()) {
                $name = $this->current()->spelling;
                $base = $this->typedefTypes[$name] ?? IntCType::$instance;
                if ($base instanceof StructCType && $base->record->fields === []
                    && $base->record->name !== ''
                    && isset($this->completedRecords[$base->record->name])) {
                    $base = new StructCType($this->completedRecords[$base->record->name]);
                } elseif ($base instanceof UnionCType && $base->record->fields === []
                    && $base->record->name !== ''
                    && isset($this->completedRecords[$base->record->name])) {
                    $base = new UnionCType($this->completedRecords[$base->record->name]);
                }
                $typedefBase = new TypedefCType($name, $base);
                $this->advance();
            } elseif ($this->current()->kind === TokenKind::Identifier && $this->isForwardTypedef()) {
                $name = $this->current()->spelling;
                $this->typedefNames[$name] = true;
                $typedefBase = new TypedefCType($name, LongCType::$instance);
                $this->advance();
            } else {
                $done = true;
            }
        }

        if ($structType !== null) {
            $baseType = $structType;
        } elseif ($unionType !== null) {
            $baseType = $unionType;
        } elseif ($enumType !== null) {
            $baseType = $enumType;
        } elseif ($typedefBase !== null) {
            $baseType = $typedefBase;
        } elseif ($typeSpecifiers === []) {
            $baseType = IntCType::$instance;
        } else {
            $baseType = $this->buildTypeFromSpecifiers($typeSpecifiers);
        }

        if ($isConst || $isVolatile || $isRestrict) {
            $baseType = new QualifiedCType($baseType, $isConst, $isVolatile, $isRestrict);
        }

        return [$baseType, $storageClass, $isInline];
    }

    /**
     * @param list<string> $specs
     */
    private function buildTypeFromSpecifiers(array $specs): CType
    {
        $isUnsigned = in_array('unsigned', $specs, true);
        $longCount = count(array_filter($specs, static fn (string $s): bool => $s === 'long'));
        $hasShort = in_array('short', $specs, true);
        $hasFloat = in_array('float', $specs, true);
        $hasDouble = in_array('double', $specs, true);
        $hasChar = in_array('char', $specs, true);
        $hasVoid = in_array('void', $specs, true);
        $hasBool = in_array('_Bool', $specs, true);

        if ($hasVoid) {
            return VoidCType::$instance;
        }
        if ($hasBool) {
            return BoolCType::$instance;
        }
        if ($hasChar) {
            return $isUnsigned ? UCharCType::$instance : CharCType::$instance;
        }
        if ($hasDouble) {
            return $longCount > 0 ? LongDoubleCType::$instance : DoubleCType::$instance;
        }
        if ($hasFloat) {
            return FloatCType::$instance;
        }
        if ($hasShort) {
            return $isUnsigned ? UShortCType::$instance : ShortCType::$instance;
        }
        if ($longCount >= 2) {
            return $isUnsigned ? ULongLongCType::$instance : LongLongCType::$instance;
        }
        if ($longCount === 1) {
            return $isUnsigned ? ULongCType::$instance : LongCType::$instance;
        }

        return $isUnsigned ? UIntCType::$instance : IntCType::$instance;
    }

    /**
     * @throws ParseError
     */
    private function parseStructOrUnion(bool $isStruct): CType
    {
        $this->advance();

        $tag = null;
        if ($this->current()->kind === TokenKind::Identifier) {
            $tag = $this->current()->spelling;
            $this->advance();
        }

        if ($this->isPunct('{')) {
            $this->advance();
            $fields = [];
            $maxAlign = 1;

            if ($isStruct) {
                $bitOffset = 0;
                while (!$this->isPunct('}') && !$this->isAtEnd()) {
                    [$baseType] = $this->parseDeclSpecifiers();
                    do {
                        [$fieldName, $fieldType] = $this->parseDeclarator($baseType);
                        $bitWidth = null;
                        if ($this->match(TokenKind::Punct, ':')) {
                            $widthExpr = $this->parseConditionalExpr();
                            $bitWidth = $this->evalIntConst($widthExpr);
                        }
                        if ($bitWidth !== null) {
                            $typeSize = $fieldType->sizeInBytes() ?? 0;
                            $typeAlign = $fieldType->alignOf() ?? 1;
                            $unitBits = $typeSize * 8;
                            $alignBits = $typeAlign * 8;
                            $maxAlign = max($maxAlign, $typeAlign);
                            $unitStart = intdiv($bitOffset, $alignBits) * $alignBits;
                            $posInUnit = $bitOffset - $unitStart;
                            if ($posInUnit + $bitWidth > $unitBits) {
                                $bitOffset = $unitStart + $unitBits;
                            }
                            $byteOff = intdiv($bitOffset, 8);
                            $bitPosInUnit = $bitOffset - ($byteOff * 8);
                            $fields[] = new RecordField(
                                name: $fieldName,
                                type: $fieldType,
                                bitWidth: $bitWidth,
                                offset: $byteOff,
                                bitOffset: $bitPosInUnit,
                            );
                            $bitOffset += $bitWidth;
                        } else {
                            $byteOff = intdiv($bitOffset + 7, 8);
                            $fieldAlign = $fieldType->alignOf() ?? 1;
                            $fieldSize = $fieldType->sizeInBytes() ?? 0;
                            $maxAlign = max($maxAlign, $fieldAlign);
                            $byteOff = ($byteOff + $fieldAlign - 1) & ~($fieldAlign - 1);
                            $fields[] = new RecordField(
                                name: $fieldName,
                                type: $fieldType,
                                bitWidth: null,
                                offset: $byteOff,
                                bitOffset: 0,
                            );
                            $bitOffset = ($byteOff + $fieldSize) * 8;
                        }
                    } while ($this->match(TokenKind::Punct, ','));
                    $this->consume(TokenKind::Punct, ';');
                }
                $this->consume(TokenKind::Punct, '}');
                $totalBytes = intdiv($bitOffset + 7, 8);
                $totalBytes = ($totalBytes + $maxAlign - 1) & ~($maxAlign - 1);
                $rec = new RecordType(name: $tag ?? '', fields: $fields, size: $totalBytes, alignment: $maxAlign);
                if ($tag !== null) {
                    $this->completedRecords[$tag] = $rec;
                }

                return new StructCType($rec);
            }

            $maxSize = 0;
            while (!$this->isPunct('}') && !$this->isAtEnd()) {
                [$baseType] = $this->parseDeclSpecifiers();
                do {
                    [$fieldName, $fieldType] = $this->parseDeclarator($baseType);
                    $fieldSize = $fieldType->sizeInBytes() ?? 0;
                    $fieldAlign = $fieldType->alignOf() ?? 1;
                    $maxAlign = max($maxAlign, $fieldAlign);
                    $maxSize = max($maxSize, $fieldSize);
                    $fields[] = new RecordField(name: $fieldName, type: $fieldType, offset: 0);
                } while ($this->match(TokenKind::Punct, ','));
                $this->consume(TokenKind::Punct, ';');
            }
            $this->consume(TokenKind::Punct, '}');
            $totalSize = ($maxSize + $maxAlign - 1) & ~($maxAlign - 1);
            $rec = new RecordType(name: $tag ?? '', fields: $fields, size: $totalSize, alignment: $maxAlign);
            if ($tag !== null) {
                $this->completedRecords[$tag] = $rec;
            }

            return new UnionCType($rec);
        }

        if ($tag !== null && isset($this->completedRecords[$tag])) {
            return $isStruct
                ? new StructCType($this->completedRecords[$tag])
                : new UnionCType($this->completedRecords[$tag]);
        }

        $rec = new RecordType(name: $tag ?? '', fields: [], size: null, alignment: null);

        return $isStruct ? new StructCType($rec) : new UnionCType($rec);
    }

    /**
     * @throws ParseError
     */
    private function parseEnumSpec(): CType
    {
        $this->advance();

        $tag = null;
        if ($this->current()->kind === TokenKind::Identifier) {
            $tag = $this->current()->spelling;
            $this->advance();
        }

        if ($this->isPunct('{')) {
            $this->advance();
            $cases = [];
            $nextValue = 0;

            while (!$this->isPunct('}') && !$this->isAtEnd()) {
                if ($this->current()->kind !== TokenKind::Identifier) {
                    if ($this->isPunct('}')) {
                        break;
                    }
                    throw ParseError::expected('enumerator name', $this->current()->spelling, $this->current()->loc);
                }
                $caseName = $this->advance()->spelling;
                $this->skipAsmAndAttributes();
                if ($this->match(TokenKind::Punct, '=')) {
                    $valExpr = $this->parseConditionalExpr();
                    $nextValue = $this->evalIntConst($valExpr);
                }
                $cases[] = new EnumCase(name: $caseName, value: $nextValue);
                ++$nextValue;
                if (!$this->match(TokenKind::Punct, ',')) {
                    break;
                }
            }
            $this->consume(TokenKind::Punct, '}');
            $en = new EnumType(name: $tag ?? '', cases: $cases, underlyingType: IntCType::$instance);

            return new EnumCType($en);
        }

        $en = new EnumType(name: $tag ?? '', cases: [], underlyingType: IntCType::$instance);

        return new EnumCType($en);
    }

    /**
     * @return array{string, CType, SourceLoc}
     *
     * @throws ParseError
     */
    private function parseDeclarator(CType $baseType): array
    {
        $type = $baseType;

        while ($this->isPunct('*')) {
            $this->advance();
            while ($this->isKeyword('const') || $this->isKeyword('volatile') || $this->isKeyword('restrict')
                || $this->isKeyword('__const') || $this->isKeyword('__const__')
                || $this->isKeyword('__volatile') || $this->isKeyword('__volatile__')
                || $this->isKeyword('__restrict') || $this->isKeyword('__restrict__')) {
                $this->advance();
            }
            $type = new PointerCType($type);
        }

        if ($this->isPunct('(')) {
            $savePos = $this->pos;
            $this->advance();
            if ($this->isPunct('*')) {
                $this->advance();
                $pointerDepth = 1;
                while ($this->isPunct('*')) {
                    $this->advance();
                    ++$pointerDepth;
                }
                $hadInnerParen = false;
                if ($this->isPunct('(')) {
                    $this->advance();
                    $hadInnerParen = true;
                    while ($this->isPunct('*')) {
                        $this->advance();
                        ++$pointerDepth;
                    }
                }
                while ($this->isKeyword('const') || $this->isKeyword('volatile')) {
                    $this->advance();
                }
                $baseTypeForFunc = $type;
                $innerType = $type;
                for ($i = 0; $i < $pointerDepth; ++$i) {
                    $innerType = new PointerCType($innerType);
                }
                $name = '';
                $nameLoc = SourceLoc::unknown();
                if ($this->current()->kind === TokenKind::Identifier) {
                    $name = $this->current()->spelling;
                    $nameLoc = $this->advance()->loc;
                } elseif ($this->isPunct(')')) {
                    $nameLoc = $this->current()->loc;
                } else {
                    throw ParseError::expected('identifier', $this->current()->spelling, $this->current()->loc);
                }

                if ($name !== '' && $this->isPunct('(')) {
                    [$funcParams, $funcVariadic, $funcRetType] = $this->parseFunctionParams($innerType);
                    $this->consume(TokenKind::Punct, ')');
                    [$retParams, $retVariadic, $retRetType] = $this->parseFunctionParams($funcRetType);
                    // Outer return-type params (often `(void)`) must not clobber the
                    // real function parameter list used when emitting FuncDecl.
                    $this->lastFuncParams = $funcParams;
                    $this->lastFuncVariadic = $funcVariadic;
                    $funcType = new FunctionCType(
                        params: array_map(static fn (Param $p): CType => $p->type, $funcParams),
                        returnType: new FunctionCType(
                            params: array_map(static fn (Param $p): CType => $p->type, $retParams),
                            returnType: $retRetType,
                            variadic: $retVariadic,
                        ),
                        variadic: $funcVariadic,
                    );

                    return [$name, $funcType, $nameLoc];
                }

                /** @var list<array{count: ?int}> $innerArrayDims */
                $innerArrayDims = [];
                while ($this->isPunct('[')) {
                    $this->advance();
                    if ($this->isPunct(']')) {
                        $this->advance();
                        $innerArrayDims[] = ['count' => null];
                    } else {
                        $sizeExpr = $this->parseAssignmentExpr();
                        $this->consume(TokenKind::Punct, ']');
                        $innerArrayDims[] = ['count' => $this->evalIntConst($sizeExpr)];
                    }
                }
                $this->consume(TokenKind::Punct, ')');
                if ($this->isPunct('(')) {
                    [$params, $variadic, $retType] = $this->parseFunctionParams($baseTypeForFunc);
                    $type = new FunctionCType(
                        params: array_map(static fn (Param $p): CType => $p->type, $params),
                        returnType: $retType,
                        variadic: $variadic,
                    );
                    for ($i = 0; $i < $pointerDepth; ++$i) {
                        $type = new PointerCType($type);
                    }
                } else {
                    $type = $baseTypeForFunc;
                    for ($i = 0; $i < $pointerDepth; ++$i) {
                        $type = new PointerCType($type);
                    }
                }
                foreach ($innerArrayDims as $dim) {
                    $type = $dim['count'] !== null
                        ? new ArrayCType($type, $dim['count'])
                        : new IncompleteArrayCType($type);
                }
                if ($hadInnerParen) {
                    $this->consume(TokenKind::Punct, ')');
                }
                $savedName = $name;
                $savedLoc = $nameLoc;
                while ($this->isPunct('[') || $this->isPunct('(')) {
                    if ($this->isPunct('[')) {
                        $this->advance();
                        if ($this->isPunct(']')) {
                            $this->advance();
                            $type = $this->applyArraySuffix($type, null);
                        } else {
                            $sizeExpr = $this->parseAssignmentExpr();
                            $this->consume(TokenKind::Punct, ']');
                            $type = $this->applyArraySuffix($type, $this->evalIntConst($sizeExpr));
                        }
                    } elseif ($this->isPunct('(')) {
                        [$params2, $variadic2, $retType2] = $this->parseFunctionParams($type);
                        $type = new FunctionCType(
                            params: array_map(static fn (Param $p): CType => $p->type, $params2),
                            returnType: $retType2,
                            variadic: $variadic2,
                        );
                    }
                }

                return [$savedName, $type, $savedLoc];
            }
            $this->pos = $savePos;
        }

        $name = '';
        $loc = SourceLoc::unknown();
        if ($this->current()->kind === TokenKind::Identifier) {
            $name = $this->current()->spelling;
            $loc = $this->advance()->loc;
        }

        while ($this->isPunct('[') || $this->isPunct('(')) {
            if ($this->isPunct('[')) {
                $this->advance();
                if ($this->isPunct(']')) {
                    $this->advance();
                    $type = new IncompleteArrayCType($type);
                } else {
                    $sizeExpr = $this->parseAssignmentExpr();
                    $this->consume(TokenKind::Punct, ']');
                    $type = new ArrayCType($type, $this->evalIntConst($sizeExpr));
                }
            } elseif ($this->isPunct('(')) {
                [$params, $variadic, $retType] = $this->parseFunctionParams($type);
                $type = new FunctionCType(
                    params: array_map(static fn (Param $p): CType => $p->type, $params),
                    returnType: $retType,
                    variadic: $variadic,
                );
            }
        }

        return [$name, $type, $loc];
    }

    private function applyArraySuffix(CType $type, ?int $count): CType
    {
        if ($type instanceof PointerCType) {
            $to = $type->to;
            $array = $count !== null
                ? new ArrayCType($to, $count)
                : new IncompleteArrayCType($to);

            return new PointerCType($array);
        }

        return $count !== null
            ? new ArrayCType($type, $count)
            : new IncompleteArrayCType($type);
    }

    /**
     * @return array{list<Param>, bool, CType}
     *
     * @throws ParseError
     */
    private function parseFunctionParams(CType $returnType): array
    {
        $this->consume(TokenKind::Punct, '(');
        $params = [];
        $variadic = false;

        if ($this->isKeyword('void') && $this->next()->kind === TokenKind::Punct && $this->next()->spelling === ')') {
            $this->advance();
            $this->consume(TokenKind::Punct, ')');
            $this->lastFuncParams = [];
            $this->lastFuncVariadic = false;

            return [[], false, $returnType];
        }

        while (!$this->isPunct(')') && !$this->isAtEnd()) {
            if ($this->isPunct('...')) {
                $this->advance();
                $variadic = true;
                break;
            }

            [$baseType] = $this->parseDeclSpecifiers();
            [$paramName, $paramType, $paramLoc] = $this->parseDeclarator($baseType);
            $actualType = $paramType;
            $unqual = $actualType->unqualified();
            if ($unqual instanceof ArrayCType) {
                $actualType = new PointerCType($unqual->of);
            } elseif ($unqual instanceof IncompleteArrayCType) {
                $actualType = new PointerCType($unqual->of);
            }
            $params[] = new Param(
                name: $paramName !== '' ? $paramName : null,
                type: $actualType,
                loc: $paramLoc,
            );

            if (!$this->match(TokenKind::Punct, ',')) {
                break;
            }
        }
        $this->consume(TokenKind::Punct, ')');
        $this->lastFuncParams = $params;
        $this->lastFuncVariadic = $variadic;

        return [$params, $variadic, $returnType];
    }

    /**
     * @throws ParseError
     */
    private function parseInitializer(CType $type): Expr
    {
        if ($this->isPunct('{')) {
            return $this->parseInitList();
        }

        return $this->parseAssignmentExpr();
    }

    /**
     * @throws ParseError
     */
    private function parseInitList(): InitListExpr
    {
        $loc = $this->current()->loc;
        $this->consume(TokenKind::Punct, '{');
        $values = [];
        /** @var list<null|string|int|list<string|int>> $designators */
        $designators = [];

        while (!$this->isPunct('}') && !$this->isAtEnd()) {
            $designator = null;
            // C99 designator-list: (.ident | [constexpr])+ =
            if ($this->isPunct('[') || $this->isPunct('.')) {
                /** @var list<string|int> $path */
                $path = [];
                while ($this->isPunct('[') || $this->isPunct('.')) {
                    if ($this->isPunct('[')) {
                        $this->advance();
                        $idxExpr = $this->parseAssignmentExpr();
                        $this->consume(TokenKind::Punct, ']');
                        if ($idxExpr instanceof IntegerLiteral) {
                            $path[] = $idxExpr->value;
                        }
                    } else {
                        $this->advance();
                        $path[] = $this->consume(TokenKind::Identifier)->spelling;
                    }
                }
                $this->match(TokenKind::Punct, '=');
                if (count($path) === 1) {
                    $designator = $path[0];
                } elseif (count($path) > 1) {
                    $designator = $path;
                }
            }

            if ($this->isPunct('{')) {
                $values[] = $this->parseInitList();
            } else {
                $values[] = $this->parseAssignmentExpr();
            }
            $designators[] = $designator;

            if (!$this->match(TokenKind::Punct, ',')) {
                break;
            }
        }
        $this->consume(TokenKind::Punct, '}');

        return new InitListExpr(values: $values, loc: $loc, designators: $designators);
    }

    /**
     * @throws ParseError
     */
    private function parseCompoundStmt(): CompoundStmt
    {
        $loc = $this->consume(TokenKind::Punct, '{')->loc;
        $stmts = [];

        while (!$this->isPunct('}') && !$this->isAtEnd()) {
            $stmts[] = $this->parseStmt();
        }
        $this->consume(TokenKind::Punct, '}');

        return new CompoundStmt(statements: $stmts, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseStmt(): Stmt
    {
        if ($this->current()->kind === TokenKind::Identifier
            && $this->next()->kind === TokenKind::Punct
            && $this->next()->spelling === ':') {
            $label = $this->advance()->spelling;
            $this->advance();
            $stmt = $this->parseStmt();

            return new LabelStmt(name: $label, stmt: $stmt, loc: SourceLoc::unknown());
        }

        if ($this->isKeyword('case')) {
            $loc = $this->advance()->loc;
            $val = $this->parseConditionalExpr();
            $this->consume(TokenKind::Punct, ':');
            $stmt = ($this->isPunct('}') || $this->isKeyword('case') || $this->isKeyword('default'))
                ? null
                : $this->parseStmt();

            return new CaseStmt(value: $val, stmt: $stmt, loc: $loc);
        }

        if ($this->isKeyword('default')) {
            $loc = $this->advance()->loc;
            $this->consume(TokenKind::Punct, ':');
            $stmt = ($this->isPunct('}') || $this->isKeyword('case') || $this->isKeyword('default'))
                ? null
                : $this->parseStmt();

            return new DefaultStmt(stmt: $stmt, loc: $loc);
        }

        if ($this->current()->kind === TokenKind::Punct) {
            if ($this->isPunct('{')) {
                return $this->parseCompoundStmt();
            }
            if ($this->isPunct(';')) {
                return new EmptyStmt($this->advance()->loc);
            }
        }

        if ($this->current()->kind === TokenKind::Keyword) {
            if ($this->isKeyword('if')) {
                return $this->parseIfStmt();
            }
            if ($this->isKeyword('while')) {
                return $this->parseWhileStmt();
            }
            if ($this->isKeyword('do')) {
                return $this->parseDoWhileStmt();
            }
            if ($this->isKeyword('for')) {
                return $this->parseForStmt();
            }
            if ($this->isKeyword('switch')) {
                return $this->parseSwitchStmt();
            }
            if ($this->isKeyword('break')) {
                $loc = $this->advance()->loc;
                $this->consume(TokenKind::Punct, ';');

                return new BreakStmt($loc);
            }
            if ($this->isKeyword('continue')) {
                $loc = $this->advance()->loc;
                $this->consume(TokenKind::Punct, ';');

                return new ContinueStmt($loc);
            }
            if ($this->isKeyword('return')) {
                $loc = $this->advance()->loc;
                $val = $this->isPunct(';') ? null : $this->parseExpr();
                $this->consume(TokenKind::Punct, ';');

                return new ReturnStmt(value: $val, loc: $loc);
            }
            if ($this->isKeyword('goto')) {
                $loc = $this->advance()->loc;
                $label = $this->consume(TokenKind::Identifier)->spelling;
                $this->consume(TokenKind::Punct, ';');

                return new GotoStmt(label: $label, loc: $loc);
            }
            if (!$this->isKeyword('sizeof')) {
                return $this->parseDeclStmt();
            }
        }

        if ($this->current()->kind === TokenKind::Identifier && $this->isTypedefName()) {
            return $this->parseDeclStmt();
        }

        $loc = $this->current()->loc;
        $expr = $this->parseExpr();
        $this->consume(TokenKind::Punct, ';');

        return new ExprStmt(expr: $expr, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseDeclStmt(): DeclStmt
    {
        $loc = $this->current()->loc;

        if ($this->isKeyword('_Static_assert')) {
            $d = $this->parseStaticAssert();

            return new DeclStmt(decls: [$d], loc: $loc);
        }

        if ($this->isKeyword('typedef')) {
            $d = $this->parseTypedef();

            return new DeclStmt(decls: [$d], loc: $loc);
        }

        [$baseType, $storageClass] = $this->parseDeclSpecifiers();
        $decls = [];

        do {
            [$name, $type, $dloc] = $this->parseDeclarator($baseType);
            $initExpr = null;
            $actualType = $type;
            if ($this->match(TokenKind::Punct, '=')) {
                $initExpr = $this->parseInitializer($type);
                $actualType = $this->inferArrayTypeFromInitializer($type, $initExpr) ?? $type;
            }
            $decls[] = new VarDecl(
                name: $name,
                type: $actualType,
                loc: $dloc,
                initializer: $initExpr,
                storageClass: $storageClass,
                isGlobal: false,
            );
        } while ($this->match(TokenKind::Punct, ','));

        $this->consume(TokenKind::Punct, ';');

        return new DeclStmt(decls: $decls, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseIfStmt(): IfStmt
    {
        $loc = $this->advance()->loc;
        $this->consume(TokenKind::Punct, '(');
        $cond = $this->parseExpr();
        $this->consume(TokenKind::Punct, ')');
        $thenStmt = $this->parseStmt();
        $elseStmt = null;
        if ($this->isKeyword('else')) {
            $this->advance();
            $elseStmt = $this->parseStmt();
        }

        return new IfStmt(condition: $cond, thenStmt: $thenStmt, elseStmt: $elseStmt, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseWhileStmt(): WhileStmt
    {
        $loc = $this->advance()->loc;
        $this->consume(TokenKind::Punct, '(');
        $cond = $this->parseExpr();
        $this->consume(TokenKind::Punct, ')');
        $body = $this->parseStmt();

        return new WhileStmt(condition: $cond, body: $body, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseDoWhileStmt(): DoWhileStmt
    {
        $loc = $this->advance()->loc;
        $body = $this->parseStmt();
        $this->consume(TokenKind::Keyword, 'while');
        $this->consume(TokenKind::Punct, '(');
        $cond = $this->parseExpr();
        $this->consume(TokenKind::Punct, ')');
        $this->consume(TokenKind::Punct, ';');

        return new DoWhileStmt(body: $body, condition: $cond, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseForStmt(): ForStmt
    {
        $loc = $this->advance()->loc;
        $this->consume(TokenKind::Punct, '(');
        $initStmt = null;
        if (!$this->isPunct(';')) {
            $initStmt = $this->parseStmt();
        } else {
            $this->advance();
        }
        $cond = $this->isPunct(';') ? null : $this->parseExpr();
        $this->consume(TokenKind::Punct, ';');
        $incr = $this->isPunct(')') ? null : $this->parseExpr();
        $this->consume(TokenKind::Punct, ')');
        $body = $this->parseStmt();

        return new ForStmt(initStmt: $initStmt, condition: $cond, increment: $incr, body: $body, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseSwitchStmt(): SwitchStmt
    {
        $loc = $this->advance()->loc;
        $this->consume(TokenKind::Punct, '(');
        $val = $this->parseExpr();
        $this->consume(TokenKind::Punct, ')');
        $cases = [];
        if ($this->isPunct('{')) {
            $this->advance();
            while (!$this->isPunct('}') && !$this->isAtEnd()) {
                $cases[] = $this->parseStmt();
            }
            $this->consume(TokenKind::Punct, '}');
        } else {
            $cases[] = $this->parseStmt();
        }

        return new SwitchStmt(value: $val, cases: $cases, loc: $loc);
    }

    /**
     * @throws ParseError
     */
    private function parseExpr(): Expr
    {
        return $this->parseCommaExpr();
    }

    /**
     * @throws ParseError
     */
    private function parseCommaExpr(): Expr
    {
        $left = $this->parseAssignmentExpr();
        while ($this->isPunct(',')) {
            $loc = $this->advance()->loc;
            $right = $this->parseAssignmentExpr();
            $left = new BinaryExpr(op: BinaryOp::Comma, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseAssignmentExpr(): Expr
    {
        $left = $this->parseConditionalExpr();

        if ($this->isPunct('=') || $this->isPunct('+=') || $this->isPunct('-=') || $this->isPunct('*=')
            || $this->isPunct('/=') || $this->isPunct('%=') || $this->isPunct('<<=') || $this->isPunct('>>=')
            || $this->isPunct('&=') || $this->isPunct('|=') || $this->isPunct('^=')) {
            $opStr = $this->advance()->spelling;
            $op = AssignOp::tryFrom($opStr) ?? AssignOp::Assign;
            $right = $this->parseAssignmentExpr();
            $left = new AssignExpr(op: $op, target: $left, value: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseConditionalExpr(): Expr
    {
        $cond = $this->parseLogicalOrExpr();
        if ($this->isPunct('?')) {
            $loc = $this->advance()->loc;
            $trueE = $this->parseExpr();
            $this->consume(TokenKind::Punct, ':');
            $falseE = $this->parseConditionalExpr();

            return new ConditionalExpr(condition: $cond, trueExpr: $trueE, falseExpr: $falseE, loc: $loc);
        }

        return $cond;
    }

    /**
     * @throws ParseError
     */
    private function parseLogicalOrExpr(): Expr
    {
        $left = $this->parseLogicalAndExpr();
        while ($this->isPunct('||')) {
            $loc = $this->advance()->loc;
            $right = $this->parseLogicalAndExpr();
            $left = new BinaryExpr(op: BinaryOp::LogicOr, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseLogicalAndExpr(): Expr
    {
        $left = $this->parseBitwiseOrExpr();
        while ($this->isPunct('&&')) {
            $loc = $this->advance()->loc;
            $right = $this->parseBitwiseOrExpr();
            $left = new BinaryExpr(op: BinaryOp::LogicAnd, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseBitwiseOrExpr(): Expr
    {
        $left = $this->parseBitwiseXorExpr();
        while ($this->isPunct('|')) {
            $loc = $this->advance()->loc;
            $right = $this->parseBitwiseXorExpr();
            $left = new BinaryExpr(op: BinaryOp::BitOr, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseBitwiseXorExpr(): Expr
    {
        $left = $this->parseBitwiseAndExpr();
        while ($this->isPunct('^')) {
            $loc = $this->advance()->loc;
            $right = $this->parseBitwiseAndExpr();
            $left = new BinaryExpr(op: BinaryOp::BitXor, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseBitwiseAndExpr(): Expr
    {
        $left = $this->parseEqualityExpr();
        while ($this->isPunct('&')) {
            $loc = $this->advance()->loc;
            $right = $this->parseEqualityExpr();
            $left = new BinaryExpr(op: BinaryOp::BitAnd, left: $left, right: $right, loc: $loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseEqualityExpr(): Expr
    {
        $left = $this->parseRelationalExpr();
        while ($this->isPunct('==') || $this->isPunct('!=')) {
            $op = BinaryOp::tryFrom($this->advance()->spelling) ?? BinaryOp::Eq;
            $right = $this->parseRelationalExpr();
            $left = new BinaryExpr(op: $op, left: $left, right: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseRelationalExpr(): Expr
    {
        $left = $this->parseShiftExpr();
        while ($this->isPunct('<') || $this->isPunct('>') || $this->isPunct('<=') || $this->isPunct('>=')) {
            $op = BinaryOp::tryFrom($this->advance()->spelling) ?? BinaryOp::Lt;
            $right = $this->parseShiftExpr();
            $left = new BinaryExpr(op: $op, left: $left, right: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseShiftExpr(): Expr
    {
        $left = $this->parseAddExpr();
        while ($this->isPunct('<<') || $this->isPunct('>>')) {
            $op = BinaryOp::tryFrom($this->advance()->spelling) ?? BinaryOp::Shl;
            $right = $this->parseAddExpr();
            $left = new BinaryExpr(op: $op, left: $left, right: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseAddExpr(): Expr
    {
        $left = $this->parseMulExpr();
        while ($this->isPunct('+') || $this->isPunct('-')) {
            $op = BinaryOp::tryFrom($this->advance()->spelling) ?? BinaryOp::Add;
            $right = $this->parseMulExpr();
            $left = new BinaryExpr(op: $op, left: $left, right: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseMulExpr(): Expr
    {
        $left = $this->parseCastExpr();
        while ($this->isPunct('*') || $this->isPunct('/') || $this->isPunct('%')) {
            $op = BinaryOp::tryFrom($this->advance()->spelling) ?? BinaryOp::Mul;
            $right = $this->parseCastExpr();
            $left = new BinaryExpr(op: $op, left: $left, right: $right, loc: $left->loc);
        }

        return $left;
    }

    /**
     * @throws ParseError
     */
    private function parseCastExpr(): Expr
    {
        if ($this->isPunct('(')) {
            $nextTok = $this->next();
            if (($nextTok->kind === TokenKind::Keyword && $this->isTypeKeyword($nextTok->spelling))
                || ($nextTok->kind === TokenKind::Identifier && isset($this->typedefNames[$nextTok->spelling]))) {
                $this->advance();
                [$baseType] = $this->parseDeclSpecifiers();
                [, $castType] = $this->parseDeclarator($baseType);
                $this->consume(TokenKind::Punct, ')');
                if ($this->isPunct('{')) {
                    $initList = $this->parseInitList();

                    return new CompoundLiteralExpr(type: $castType, initList: $initList, loc: $this->current()->loc);
                }
                $operand = $this->parseCastExpr();

                return new CastExpr(type: $castType, expr: $operand, loc: $this->current()->loc);
            }
        }

        return $this->parseUnaryExpr();
    }

    private function isTypeKeyword(string $s): bool
    {
        return in_array($s, [
            'void', 'char', 'short', 'int', 'long', 'float', 'double',
            'signed', 'unsigned', 'const', 'volatile', 'struct', 'union',
            'enum', '_Bool', 'restrict',
        ], true);
    }

    /**
     * @throws ParseError
     */
    private function parseUnaryExpr(): Expr
    {
        if ($this->isPunct('!') || $this->isPunct('~') || $this->isPunct('-') || $this->isPunct('+')
            || $this->isPunct('*') || $this->isPunct('&') || $this->isPunct('++') || $this->isPunct('--')) {
            $opStr = $this->advance()->spelling;
            $op = match ($opStr) {
                '!' => UnaryOp::Not,
                '~' => UnaryOp::BitNot,
                '-' => UnaryOp::Neg,
                '+' => UnaryOp::Pos,
                '*' => UnaryOp::Dereference,
                '&' => UnaryOp::AddressOf,
                '++' => UnaryOp::PreInc,
                '--' => UnaryOp::PreDec,
                default => UnaryOp::Not,
            };
            $operand = $this->parseCastExpr();

            return new UnaryExpr(op: $op, operand: $operand, loc: SourceLoc::unknown());
        }

        if ($this->isKeyword('sizeof')) {
            $loc = $this->advance()->loc;
            if ($this->isPunct('(')) {
                $nextTok = $this->next();
                if (($nextTok->kind === TokenKind::Keyword && $this->isTypeKeyword($nextTok->spelling))
                    || ($nextTok->kind === TokenKind::Identifier && isset($this->typedefNames[$nextTok->spelling]))) {
                    $this->advance();
                    [$baseType] = $this->parseDeclSpecifiers();
                    [, $typeName] = $this->parseDeclarator($baseType);
                    $this->consume(TokenKind::Punct, ')');

                    return new SizeofExpr(expr: null, typeName: $typeName, loc: $loc);
                }
            }
            $e = $this->parseUnaryExpr();

            return new SizeofExpr(expr: $e, typeName: null, loc: $loc);
        }

        return $this->parsePostfixExpr();
    }

    /**
     * @throws ParseError
     */
    private function parsePostfixExpr(): Expr
    {
        $expr = $this->parsePrimaryExpr();

        while (true) {
            if ($this->isPunct('[')) {
                $loc = $this->advance()->loc;
                $index = $this->parseExpr();
                $this->consume(TokenKind::Punct, ']');
                $expr = new SubscriptExpr(base: $expr, index: $index, loc: $loc);
            } elseif ($this->isPunct('(')) {
                $loc = $this->advance()->loc;
                $args = [];
                if (!$this->isPunct(')')) {
                    $args[] = $this->parseAssignmentExpr();
                    while ($this->match(TokenKind::Punct, ',')) {
                        $args[] = $this->parseAssignmentExpr();
                    }
                }
                $this->consume(TokenKind::Punct, ')');
                $expr = new CallExpr(function: $expr, arguments: $args, loc: $loc);
            } elseif ($this->isPunct('.')) {
                $loc = $this->advance()->loc;
                $member = $this->consume(TokenKind::Identifier)->spelling;
                $expr = new MemberExpr(base: $expr, memberName: $member, isArrow: false, loc: $loc);
            } elseif ($this->isPunct('->')) {
                $loc = $this->advance()->loc;
                $member = $this->consume(TokenKind::Identifier)->spelling;
                $expr = new MemberExpr(base: $expr, memberName: $member, isArrow: true, loc: $loc);
            } elseif ($this->isPunct('++') || $this->isPunct('--')) {
                $opStr = $this->advance()->spelling;
                $op = $opStr === '++' ? UnaryOp::PostInc : UnaryOp::PostDec;
                $expr = new UnaryExpr(op: $op, operand: $expr, loc: SourceLoc::unknown());
            } else {
                break;
            }
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function parsePrimaryExpr(): Expr
    {
        $token = $this->current();

        switch ($token->kind) {
            case TokenKind::IntegerLiteral:
                $this->advance();
                [$val, $isUnsigned, $type] = $this->parseIntLiteral($token->spelling);

                return new IntegerLiteral(value: $val, loc: $token->loc, isUnsigned: $isUnsigned, type: $type);

            case TokenKind::FloatLiteral:
                $this->advance();
                $val = $this->parseDoubleLiteral($token->spelling);
                $type = (str_ends_with($token->spelling, 'f') || str_ends_with($token->spelling, 'F'))
                    ? FloatCType::$instance
                    : DoubleCType::$instance;

                return new FloatLiteral(value: $val, loc: $token->loc, type: $type);

            case TokenKind::CharLiteral:
                $this->advance();
                $val = $this->parseCharLiteralValue($token->spelling);

                return new CharLiteral(value: $val, loc: $token->loc);

            case TokenKind::StringLiteral:
                $str = $this->parseStringLiteralValue($token->spelling);
                $this->advance();
                while ($this->current()->kind === TokenKind::StringLiteral) {
                    $str .= $this->parseStringLiteralValue($this->current()->spelling);
                    $this->advance();
                }
                $type = new ArrayCType(CharCType::$instance, strlen($str) + 1);

                return new StringLiteral(value: $str, type: $type, loc: $token->loc);

            case TokenKind::Identifier:
                $this->advance();

                return new Identifier(name: $token->spelling, loc: $token->loc);

            case TokenKind::Punct:
                if ($this->isPunct('(')) {
                    $this->advance();
                    $e = $this->parseExpr();
                    $this->consume(TokenKind::Punct, ')');

                    return $e;
                }
                break;

            default:
                break;
        }

        throw ParseError::unexpectedToken(
            "unexpected {$token->kind->name} '{$token->spelling}' in expression",
            $token->loc,
        );
    }

    /**
     * @return array{int, bool, CType}
     */
    private function parseIntLiteral(string $spelling): array
    {
        $s = $spelling;
        $isUnsigned = false;
        $isLong = false;
        $isLongLong = false;

        while ($s !== '' && (
            str_ends_with($s, 'u') || str_ends_with($s, 'U')
            || str_ends_with($s, 'l') || str_ends_with($s, 'L')
        )) {
            if (str_ends_with($s, 'u') || str_ends_with($s, 'U')) {
                $isUnsigned = true;
            }
            if (str_ends_with($s, 'l') || str_ends_with($s, 'L')) {
                if ($isLong) {
                    $isLongLong = true;
                    $isLong = false;
                } else {
                    $isLong = true;
                }
            }
            $s = substr($s, 0, -1);
        }

        if (str_starts_with($s, '0x') || str_starts_with($s, '0X')) {
            $value = self::parseHexInt(substr($s, 2));
        } elseif (str_starts_with($s, '0b') || str_starts_with($s, '0B')) {
            $value = (int) bindec(substr($s, 2));
        } elseif (str_starts_with($s, '0') && strlen($s) > 1) {
            $value = (int) octdec(substr($s, 1));
        } else {
            $value = (int) $s;
        }

        if ($isLongLong) {
            $type = $isUnsigned ? ULongLongCType::$instance : LongLongCType::$instance;
        } elseif ($isLong) {
            $type = $isUnsigned ? ULongCType::$instance : LongCType::$instance;
        } else {
            $type = $isUnsigned ? UIntCType::$instance : IntCType::$instance;
        }

        return [$value, $isUnsigned, $type];
    }

    private function parseDoubleLiteral(string $spelling): float
    {
        $s = $spelling;
        if ($s !== '' && in_array($s[strlen($s) - 1], ['f', 'F', 'l', 'L'], true)) {
            $s = substr($s, 0, -1);
        }

        return (float) $s;
    }

    private function parseCharLiteralValue(string $spelling): int
    {
        $s = $spelling;
        if ($s !== '' && in_array($s[0], ['L', 'u', 'U'], true)) {
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
            return $this->parseEscape(substr($s, 1));
        }

        return ord($s[0]);
    }

    private function parseStringLiteralValue(string $spelling): string
    {
        $s = $spelling;
        if ($s !== '' && in_array($s[0], ['L', 'u', 'U'], true)) {
            $s = substr($s, 1);
        }
        if (!str_starts_with($s, '"') || !str_ends_with($s, '"')) {
            return $s;
        }
        $s = substr($s, 1, -1);
        $result = '';
        $len = strlen($s);
        $i = 0;
        while ($i < $len) {
            if ($s[$i] === '\\' && $i + 1 < $len) {
                $next = $s[$i + 1];
                switch ($next) {
                    case 'n':
                        $result .= "\n";
                        break;
                    case 't':
                        $result .= "\t";
                        break;
                    case 'r':
                        $result .= "\r";
                        break;
                    case '\\':
                        $result .= '\\';
                        break;
                    case "'":
                        $result .= "'";
                        break;
                    case '"':
                        $result .= '"';
                        break;
                    case 'x':
                        $hexStart = $i + 2;
                        $hexStr = '';
                        $j = $hexStart;
                        while ($j < $len && ctype_xdigit($s[$j]) && strlen($hexStr) < 2) {
                            $hexStr .= $s[$j];
                            ++$j;
                        }
                        if ($hexStr !== '') {
                            $val = (int) hexdec($hexStr);
                            if ($val < 128) {
                                $result .= chr($val);
                            } else {
                                $result .= sprintf('\\%03o', $val);
                            }
                        }
                        $i = $j;
                        continue 2;
                    default:
                        if ($next >= '0' && $next <= '7') {
                            $octStr = '';
                            $j = $i + 1;
                            while ($j < $len && $s[$j] >= '0' && $s[$j] <= '7' && strlen($octStr) < 3) {
                                $octStr .= $s[$j];
                                ++$j;
                            }
                            if ($octStr !== '') {
                                $val = (int) octdec($octStr);
                                if ($val < 128) {
                                    $result .= chr($val);
                                } else {
                                    $result .= sprintf('\\%03o', $val);
                                }
                            }
                            $i = $j;
                            continue 2;
                        }
                        $result .= $next;
                        break;
                }
                $i += 2;
            } else {
                $result .= $s[$i];
                ++$i;
            }
        }

        return $result;
    }

    private function parseEscape(string $s): int
    {
        if ($s === '') {
            return 0;
        }
        $first = $s[0];
        return match ($first) {
            'n' => 0x0A,
            't' => 0x09,
            'r' => 0x0D,
            '0' => 0x00,
            '\\' => 0x5C,
            "'" => 0x27,
            '"' => 0x22,
            'x' => (int) hexdec(substr($s, 1)),
            default => ctype_digit($first) ? (int) octdec($s) : ord($first),
        };
    }

    private function evalIntConst(Expr $expr): int
    {
        if ($expr instanceof IntegerLiteral) {
            return $expr->value;
        }
        if ($expr instanceof CharLiteral) {
            return $expr->value;
        }
        if ($expr instanceof BinaryExpr) {
            $l = $this->evalIntConst($expr->left);
            $r = $this->evalIntConst($expr->right);

            return match ($expr->op) {
                BinaryOp::Add => $l + $r,
                BinaryOp::Sub => $l - $r,
                BinaryOp::Mul => $l * $r,
                BinaryOp::Div => $r !== 0 ? intdiv($l, $r) : 0,
                BinaryOp::Mod => $r !== 0 ? $l % $r : 0,
                BinaryOp::Shl => $l << $r,
                BinaryOp::Shr => $l >> $r,
                BinaryOp::BitAnd => $l & $r,
                BinaryOp::BitOr => $l | $r,
                BinaryOp::BitXor => $l ^ $r,
                BinaryOp::LogicAnd => ($l !== 0 && $r !== 0) ? 1 : 0,
                BinaryOp::LogicOr => ($l !== 0 || $r !== 0) ? 1 : 0,
                BinaryOp::Eq => $l === $r ? 1 : 0,
                BinaryOp::Ne => $l !== $r ? 1 : 0,
                BinaryOp::Lt => $l < $r ? 1 : 0,
                BinaryOp::Le => $l <= $r ? 1 : 0,
                BinaryOp::Gt => $l > $r ? 1 : 0,
                BinaryOp::Ge => $l >= $r ? 1 : 0,
                BinaryOp::Comma => $r,
            };
        }
        if ($expr instanceof UnaryExpr) {
            $v = $this->evalIntConst($expr->operand);

            return match ($expr->op) {
                UnaryOp::Neg => -$v,
                UnaryOp::Pos => $v,
                UnaryOp::Not => $v === 0 ? 1 : 0,
                UnaryOp::BitNot => ~$v,
                default => 0,
            };
        }
        if ($expr instanceof ConditionalExpr) {
            return $this->evalIntConst($expr->condition) !== 0
                ? $this->evalIntConst($expr->trueExpr)
                : $this->evalIntConst($expr->falseExpr);
        }
        if ($expr instanceof CastExpr) {
            return $this->evalIntConst($expr->expr);
        }
        if ($expr instanceof SizeofExpr) {
            if ($expr->typeName !== null) {
                return $expr->typeName->sizeInBytes() ?? 0;
            }

            return $this->evalExprType($expr->expr ?? new IntegerLiteral(0, SourceLoc::unknown()))->sizeInBytes() ?? 0;
        }

        return 0;
    }

    private function evalExprType(Expr $expr): CType
    {
        if ($expr instanceof IntegerLiteral) {
            return $expr->type;
        }
        if ($expr instanceof CharLiteral) {
            return IntCType::$instance;
        }
        if ($expr instanceof StringLiteral) {
            return $expr->type;
        }
        if ($expr instanceof FloatLiteral) {
            return $expr->type;
        }

        return IntCType::$instance;
    }

    /**
     * Parse hex string to signed 64-bit int without float overflow warnings.
     */
    private static function parseHexInt(string $hex): int
    {
        $hex = strtolower($hex);
        if ($hex === '') {
            return 0;
        }

        if (strlen($hex) <= 15) {
            return (int) hexdec($hex);
        }

        $hex = substr($hex, -16);
        $hex = str_pad($hex, 16, '0', STR_PAD_LEFT);
        $hi = (int) hexdec(substr($hex, 0, 8));
        $lo = (int) hexdec(substr($hex, 8, 8));

        /** @var int $signed */
        $signed = unpack('q', pack('V', $lo) . pack('V', $hi))[1];

        return $signed;
    }
}
