<?php

declare(strict_types=1);

namespace App\Compiler\CodeGen;

use App\Compiler\Common\AssignExpr;
use App\Compiler\Common\ArrayCType;
use App\Compiler\Common\AssignOp;
use App\Compiler\Common\BinaryExpr;
use App\Compiler\Common\BinaryOp;
use App\Compiler\Common\BoolCType;
use App\Compiler\Common\BoolLiteral;
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
use App\Compiler\Common\Decl;
use App\Compiler\Common\DeclStmt;
use App\Compiler\Common\DefaultStmt;
use App\Compiler\Common\DoWhileStmt;
use App\Compiler\Common\DoubleCType;
use App\Compiler\Common\EmptyStmt;
use App\Compiler\Common\EnumCType;
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
use App\Compiler\Common\PointerCType;
use App\Compiler\Common\QualifiedCType;
use App\Compiler\Common\RecordType;
use App\Compiler\Common\ReturnStmt;
use App\Compiler\Common\SCharCType;
use App\Compiler\Common\ShortCType;
use App\Compiler\Common\SizeofExpr;
use App\Compiler\Common\SourceLoc;
use App\Compiler\Common\StorageClass;
use App\Compiler\Common\StringLiteral;
use App\Compiler\Common\StructCType;
use App\Compiler\Common\StructDecl;
use App\Compiler\Common\SubscriptExpr;
use App\Compiler\Common\SwitchStmt;
use App\Compiler\Common\TypedefCType;
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
use App\Compiler\Common\Stmt;
use App\Compiler\Common\CType;

/**
 * ARM64 code generator: emits assembly from the typed AST.
 */
final class Codegen
{
    private array $lines = [];
    private int $framePlaceholderIndex = -1;

    /** @var list<array{label: string, value: string}> */
    private array $stringLiterals = [];

    private int $stringLabelCounter = 0;

    /** @var array<string, true> */
    private array $globalLabels = [];

    /** @var array<string, true> */
    private array $externGlobals = [];

    private int $localOffset = 0;

    /** @var array<string, int> */
    private array $localVarOffsets = [];

    private int $frameSize = 0;
    private int $labelCounter = 0;
    private RegAlloc $regAlloc;
    private string $currentFuncName = '';

    /** @var array<string, CType> */
    private array $localVarTypes = [];

    /** @var array<string, CType> */
    private array $globalVarTypes = [];

    /** @var array<string, RecordType> */
    private array $knownRecords = [];

    /** @var array<string, true> */
    private array $functionNames = [];

    /** @var array<string, true> */
    private array $definedFunctions = [];

    /** @var array<string, CType> */
    private array $functionReturnTypes = [];

    /** FP offset of saved indirect-return pointer (x8), or null. */
    private ?int $indirectReturnOffset = null;

    /** When set, next call returning a large aggregate writes through this FP offset. */
    private ?int $forcedSretOffset = null;

    /** @var array<string, true> */
    private array $variadicFunctions = [];

    /** @var array<string, int> */
    private array $functionParamCounts = [];

    /** @var array<string, string> */
    private array $staticLocalGlobals = [];

    /**
     * @var list<array{name: string, type: CType, init: Expr}>
     */
    private array $staticLocalInits = [];

    /** @var list<string> */
    private array $breakLabels = [];

    /** @var list<string> */
    private array $continueLabels = [];

    /** @var array<string, string> */
    private array $gotoLabels = [];

    private int $vaSaveAreaOffset = 0;

    /** @var array<string, int> */
    private array $enumConstants = [];

    /**
     * @var list<array{label: string, type: CType, init: Expr}>
     */
    private array $compoundLiterals = [];

    private int $compoundLiteralCounter = 0;

    /**
     * @param array<string, int> $enumConstants
     */
    public function __construct(array $enumConstants = [])
    {
        $this->enumConstants = $enumConstants;
        $this->regAlloc = new RegAlloc();
    }

    /**
     * @param list<Decl> $decls
     */
    public function generate(array $decls): string
    {
        $this->lines = [];
        foreach ($decls as $d) {
            if ($d instanceof VarDecl) {
                $vd = $d;
                $this->globalLabels[$vd->name] = true;
                $this->globalVarTypes[$vd->name] = $vd->type;
                if ($vd->storageClass === StorageClass::Extern && $vd->initializer === null) {
                    $this->externGlobals[$vd->name] = true;
                } else {
                    unset($this->externGlobals[$vd->name]);
                }
            }
            if ($d instanceof FuncDecl) {
                $fd = $d;
                $this->functionNames[$fd->name] = true;
                $this->functionReturnTypes[$fd->name] = $fd->returnType;
                if ($fd->body !== null) {
                    $this->definedFunctions[$fd->name] = true;
                }
                if ($fd->variadic) {
                    $this->variadicFunctions[$fd->name] = true;
                }
                $this->functionParamCounts[$fd->name] = count($fd->params);
            }
        }
        foreach ($decls as $d) {
            if ($d instanceof StructDecl) {
                $sd = $d;
                if ($sd->record->size !== null) {
                    $this->knownRecords[$sd->name ?? ''] = $sd->record;
                }
            }
            if ($d instanceof UnionDecl) {
                $ud = $d;
                if ($ud->record->size !== null) {
                    $this->knownRecords[$ud->name ?? ''] = $ud->record;
                }
            }
            if ($d instanceof VarDecl) {
                $vd = $d;
                $this->collectRecords($vd->type);
            }
        }
        $this->emitDataSection($decls);
        $this->emitLine('.text');
        foreach ($decls as $decl) {
            if ($decl instanceof FuncDecl) {
                $fd = $decl;
                if ($fd->body !== null) {
                    $this->emitFunction($fd);
                }
            }
        }
        $this->emitStaticLocalData();
        $this->emitCompoundLiterals();
        $this->emitStringLiterals();

        $assembly = implode("\n", $this->lines)."\n";
        $this->lines = [];

        return $assembly;
    }

    /**
     * @param list<Decl> $decls
     */
    private function emitDataSection(array $decls): void
    {
        $hasGlobals = false;
        $emittedLabels = [];
        $definedGlobals = [];
        $nonExternDefs = [];
        foreach ($decls as $d) {
            if ($d instanceof VarDecl) {
                $vd = $d;
                if ($vd->isGlobal) {
                    if ($vd->initializer !== null) {
                        $definedGlobals[$vd->name] = true;
                    }
                    if ($vd->storageClass !== StorageClass::Extern) {
                        $nonExternDefs[$vd->name] = true;
                    }
                }
            }
        }
        foreach ($decls as $d) {
            if (!($d instanceof VarDecl)) {
                continue;
            }
            $vd = $d;
            if (!$vd->isGlobal) {
                continue;
            }
            if ($vd->storageClass === StorageClass::Extern && !isset($nonExternDefs[$vd->name])) {
                continue;
            }
            if (isset($emittedLabels[$vd->name])) {
                continue;
            }
            if ($vd->initializer === null && isset($definedGlobals[$vd->name])) {
                continue;
            }
            if ($vd->storageClass === StorageClass::Extern && isset($nonExternDefs[$vd->name])) {
                continue;
            }
            $emittedLabels[$vd->name] = true;
            if (!$hasGlobals) {
                $hasGlobals = true;
            }
            $size = $vd->type->sizeInBytes() ?? 0;
            if ($vd->initializer !== null) {
                $this->emitLine('.section __DATA,__data');
                $this->emitLine('.globl _' . $vd->name);
                $this->emitLine('.p2align 3');
                $this->emitLine('_' . $vd->name . ':');
                $this->emitInitializer($vd->initializer, $size, $vd->type);
            } elseif ($vd->storageClass === StorageClass::Extern) {
            } else {
                $this->emitLine('.section __DATA,__bss');
                $this->emitLine('.globl _' . $vd->name);
                $this->emitLine('.p2align 3');
                $this->emitLine('_' . $vd->name . ':');
                $bssSize = $vd->type->isScalar() ? max($size, 8) : max($size, 1);
                $this->emitLine('.zero ' . $bssSize);
            }
        }
    }

    private function emitInitializer(Expr $expr, int $size, ?CType $type = null): void
    {
        if ($expr instanceof IntegerLiteral) {
            $l = $expr;
            if ($type !== null) {
                $t = $type->unqualified();
                if ($t instanceof BoolCType || $t instanceof CharCType || $t instanceof SCharCType || $t instanceof UCharCType) {
                    $this->emitLine('.byte ' . ($l->value & 0xFF));
                } elseif ($t instanceof ShortCType || $t instanceof UShortCType) {
                    $this->emitLine('.short ' . ($l->value & 0xFFFF));
                } elseif ($t instanceof IntCType || $t instanceof UIntCType) {
                    $this->emitLine('.long ' . ($l->value & 0xFFFFFFFF));
                } elseif ($t instanceof LongCType || $t instanceof ULongCType
                    || $t instanceof LongLongCType || $t instanceof ULongLongCType) {
                    $this->emitLine('.quad ' . $l->value);
                } elseif ($t instanceof PointerCType || $t instanceof FunctionCType) {
                    $this->emitLine('.quad ' . $l->value);
                } elseif ($t instanceof QualifiedCType) {
                    $this->emitInitializer($expr, $size, $t->base);
                } elseif ($t instanceof TypedefCType) {
                    $this->emitInitializer($expr, $size, $t->base);
                } else {
                    $this->emitLine('.quad ' . $l->value);
                }
            } else {
                $this->emitLine('.quad ' . $l->value);
            }
            return;
        }

        if ($expr instanceof FloatLiteral) {
            $f = $expr;
            if ($type !== null && $type->unqualified() instanceof FloatCType) {
                $bits = unpack('V', pack('g', $f->value))[1];
                $this->emitLine('.long ' . $bits);
            } else {
                $bits = unpack('P', pack('E', $f->value))[1];
                $this->emitLine('.quad ' . $bits);
            }
            return;
        }

        if ($expr instanceof CompoundLiteralExpr) {
            $this->emitInitializer($expr->initList, $size, $type ?? $expr->type);

            return;
        }

        if ($expr instanceof InitListExpr) {
            $il = $this->normalizeInitListPaths($expr);
            if ($type !== null) {
                $t = $type->unqualified();
                if ($t instanceof StructCType) {
                    $rec = $t->record;
                    $fields = $rec->fields;
                    /** @var array<int, array{value: Expr, type: CType}> $byOffset */
                    $byOffset = [];
                    /** @var array<int, Expr> $byField */
                    $byField = [];
                    $next = 0;
                    foreach ($il->values as $i => $v) {
                        $des = $il->designators[$i] ?? null;
                        if (is_string($des)) {
                            $found = $this->findMemberField($t, $des);
                            if ($found === null) {
                                continue;
                            }
                            $byOffset[$found['offset']] = ['value' => $v, 'type' => $found['type']];

                            continue;
                        }
                        $fi = $next;
                        if ($fi < count($fields)) {
                            $byField[$fi] = $v;
                            $next = $fi + 1;
                        }
                    }
                    $currentOffset = 0;
                    ksort($byOffset);
                    foreach ($byOffset as $offset => $entry) {
                        if ($offset > $currentOffset) {
                            $this->emitLine('.zero ' . ($offset - $currentOffset));
                        }
                        $this->emitInitializer($entry['value'], $entry['type']->sizeInBytes() ?? 8, $entry['type']);
                        $currentOffset = $offset + ($entry['type']->sizeInBytes() ?? 8);
                    }
                    ksort($byField);
                    foreach ($byField as $fi => $v) {
                        $fieldOffset = $fields[$fi]->offset;
                        if ($fieldOffset > $currentOffset) {
                            $this->emitLine('.zero ' . ($fieldOffset - $currentOffset));
                        }
                        $this->emitInitializer($v, $fields[$fi]->type->sizeInBytes() ?? 8, $fields[$fi]->type);
                        $currentOffset = $fieldOffset + ($fields[$fi]->type->sizeInBytes() ?? 8);
                    }
                    $totalSize = $rec->size ?? $currentOffset;
                    if ($totalSize > $currentOffset) {
                        $this->emitLine('.zero ' . ($totalSize - $currentOffset));
                    }
                } elseif ($t instanceof UnionCType) {
                    $rec = $t->record;
                    $fields = $rec->fields;
                    $fi = 0;
                    $v = $il->values[0] ?? null;
                    if ($v !== null && $fields !== []) {
                        $des = $il->designators[0] ?? null;
                        if (is_string($des)) {
                            foreach ($fields as $j => $f) {
                                if ($f->name === $des) {
                                    $fi = $j;
                                    break;
                                }
                            }
                        }
                        $field = $fields[$fi];
                        $fieldSize = $field->type->sizeInBytes() ?? 8;
                        $this->emitInitializer($v, $fieldSize, $field->type);
                        $totalSize = $rec->size ?? $fieldSize;
                        if ($totalSize > $fieldSize) {
                            $this->emitLine('.zero ' . ($totalSize - $fieldSize));
                        }
                    } else {
                        $this->emitLine('.zero ' . ($rec->size ?? $size));
                    }
                } elseif ($t instanceof ArrayCType) {
                    $elemType = $t->of;
                    $elemSize = $elemType->sizeInBytes() ?? 8;
                    $count = $t->count;
                    /** @var array<int, Expr> $byIndex */
                    $byIndex = [];
                    $next = 0;
                    foreach ($il->values as $i => $v) {
                        $des = $il->designators[$i] ?? null;
                        $idx = is_int($des) ? $des : $next;
                        $byIndex[$idx] = $v;
                        $next = $idx + 1;
                    }
                    $emittedThrough = -1;
                    ksort($byIndex);
                    foreach ($byIndex as $idx => $v) {
                        if ($idx > $emittedThrough + 1) {
                            $this->emitLine('.zero ' . (($idx - $emittedThrough - 1) * $elemSize));
                        }
                        $this->emitInitializer($v, $elemSize, $elemType);
                        $emittedThrough = $idx;
                    }
                    if ($emittedThrough + 1 < $count) {
                        $this->emitLine('.zero ' . (($count - $emittedThrough - 1) * $elemSize));
                    }
                } elseif ($t instanceof IncompleteArrayCType) {
                    $elemType = $t->of;
                    foreach ($il->values as $v) {
                        $this->emitInitializer($v, $elemType->sizeInBytes() ?? 8, $elemType);
                    }
                } else {
                    foreach ($il->values as $v) {
                        $this->emitInitializer($v, 8);
                    }
                }
            } else {
                foreach ($il->values as $v) {
                    $this->emitInitializer($v, 8);
                }
            }
            return;
        }

        if ($expr instanceof StringLiteral) {
            $sl = $expr;
            if ($type !== null) {
                $tu = $type->unqualified();
                if ($tu instanceof ArrayCType && $tu->of->isChar()) {
                    $bytes = $sl->value;
                    $count = $tu->count;
                    if (strlen($bytes) <= $count) {
                        $this->emitLine('.asciz "' . $this->escapeStringLiteral($bytes) . '"');
                        $emitted = strlen($bytes) + 1;
                        if ($count > $emitted) {
                            $this->emitLine('.zero ' . ($count - $emitted));
                        }
                    } else {
                        $this->emitLine('.ascii "' . $this->escapeStringLiteral(substr($bytes, 0, $count)) . '"');
                    }
                } else {
                    $label = $this->addStringLiteral($sl->value);
                    $this->emitLine('.quad ' . $label);
                }
            } else {
                $label = $this->addStringLiteral($sl->value);
                $this->emitLine('.quad ' . $label);
            }
            return;
        }

        if ($expr instanceof UnaryExpr && $expr->op === UnaryOp::AddressOf) {
            $op = $expr->operand;
            if ($op instanceof StringLiteral) {
                $label = $this->addStringLiteral($op->value);
                $this->emitLine('.quad ' . $label);
            } elseif ($op instanceof IntegerLiteral && $op->value === 0) {
                $this->emitLine('.quad 0');
            } elseif ($op instanceof Identifier) {
                if (isset($this->globalLabels[$op->name])) {
                    $this->emitLine('.quad _' . $op->name);
                } elseif (isset($this->staticLocalGlobals[$op->name])) {
                    $this->emitLine('.quad ' . $this->staticLocalGlobals[$op->name]);
                } elseif (isset($this->functionNames[$op->name])) {
                    $this->emitLine('.quad _' . $op->name);
                } else {
                    $this->emitLine('.quad _' . $op->name);
                }
            } elseif ($op instanceof CompoundLiteralExpr) {
                $label = 'L_COMPLIT_' . $this->compoundLiteralCounter;
                $this->compoundLiteralCounter++;
                $this->compoundLiterals[] = ['label' => $label, 'type' => $op->type, 'init' => $op->initList];
                $this->emitLine('.quad ' . $label);
            } elseif ($op instanceof SubscriptExpr) {
                $resolved = $this->resolveAddressOfSubscript($op);
                if ($resolved !== null) {
                    $this->emitSymbolOffset($resolved[0], $resolved[1]);
                } else {
                    $this->emitLine('.quad 0');
                }
            } elseif ($op instanceof MemberExpr) {
                $resolved = $this->resolveAddressOfMember($op);
                if ($resolved !== null) {
                    $this->emitSymbolOffset($resolved[0], $resolved[1]);
                } else {
                    $this->emitLine('.quad 0');
                }
            } elseif ($op instanceof BinaryExpr && ($op->op === BinaryOp::Add || $op->op === BinaryOp::Sub)) {
                $resolved = $this->resolvePointerArith($op);
                if ($resolved !== null) {
                    $this->emitSymbolOffset($resolved[0], $resolved[1]);
                } else {
                    $this->emitLine('.quad 0');
                }
            } else {
                $this->emitLine('.quad 0');
            }
            return;
        }

        if ($expr instanceof CastExpr) {
            $this->emitInitializer($expr->expr, $size, $type);
            return;
        }

        if ($expr instanceof Identifier) {
            $id = $expr;
            if (isset($this->enumConstants[$id->name])) {
                $this->emitInitializer(
                    new IntegerLiteral($this->enumConstants[$id->name], $id->loc, false, IntCType::$instance),
                    $size,
                    $type
                );

                return;
            }
            if (isset($this->functionNames[$id->name])) {
                $this->emitLine('.quad _' . $id->name);
            } elseif (isset($this->globalLabels[$id->name])) {
                $this->emitLine('.quad _' . $id->name);
            } elseif (isset($this->staticLocalGlobals[$id->name])) {
                $this->emitLine('.quad ' . $this->staticLocalGlobals[$id->name]);
            } else {
                $this->emitLine('.quad _' . $id->name);
            }
            return;
        }

        $resolved = $this->resolveSymbolAndOffset($expr);
        if ($resolved !== null) {
            [$sym, $offset] = $resolved;
            if ($offset === 0) {
                $this->emitLine('.quad ' . $sym);
            } elseif ($offset > 0) {
                $this->emitLine('.quad ' . $sym . '+' . $offset);
            } else {
                $this->emitLine('.quad ' . $sym . '-' . (-$offset));
            }
            return;
        }

        $val = $this->evalConstExpr($expr);
        if ($val !== null) {
            $this->emitInitializer(
                new IntegerLiteral((int) $val, SourceLoc::unknown(), false, $type ?? IntCType::$instance),
                $size,
                $type
            );
            return;
        }

        $this->emitLine('.zero ' . max($size, 8));
    }

    private function emitSymbolOffset(string $sym, int $offset): void
    {
        if ($offset === 0) {
            $this->emitLine('.quad ' . $sym);
        } elseif ($offset > 0) {
            $this->emitLine('.quad ' . $sym . '+' . $offset);
        } else {
            $this->emitLine('.quad ' . $sym . '-' . (-$offset));
        }
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private function resolveSymbolAndOffset(Expr $expr): ?array
    {
        if ($expr instanceof Identifier) {
            $id = $expr;
            if (isset($this->globalLabels[$id->name])) {
                return ['_' . $id->name, 0];
            }
            if (isset($this->staticLocalGlobals[$id->name])) {
                return [$this->staticLocalGlobals[$id->name], 0];
            }
            if (isset($this->functionNames[$id->name])) {
                return ['_' . $id->name, 0];
            }
            return ['_' . $id->name, 0];
        }

        if ($expr instanceof SubscriptExpr) {
            $sub = $expr;
            $base = $this->resolveSymbolAndOffset($sub->base);
            $idx = $this->evalConstExpr($sub->index);
            if ($base !== null && $idx !== null) {
                $baseType = $this->exprType($sub->base)->unqualified();
                $elemSize = 1;
                if ($baseType instanceof PointerCType) {
                    $elemSize = $baseType->to->unqualified()->sizeInBytes() ?? 1;
                } elseif ($baseType instanceof ArrayCType) {
                    $elemSize = $baseType->of->sizeInBytes() ?? 1;
                }
                return [$base[0], $base[1] + $idx * $elemSize];
            }
            return null;
        }

        if ($expr instanceof MemberExpr) {
            $m = $expr;
            $base = $this->resolveSymbolAndOffset($m->base);
            if ($base !== null) {
                $baseType = $this->exprType($m->base)->unqualified();
                if ($baseType instanceof StructCType) {
                    foreach ($baseType->record->fields as $field) {
                        if ($field->name === $m->memberName) {
                            return [$base[0], $base[1] + $field->offset];
                        }
                    }
                }
            }
            return null;
        }

        if ($expr instanceof CastExpr) {
            return $this->resolveSymbolAndOffset($expr->expr);
        }

        if ($expr instanceof BinaryExpr && ($expr->op === BinaryOp::Add || $expr->op === BinaryOp::Sub)) {
            $b = $expr;
            $left = $this->resolveSymbolAndOffset($b->left);
            $rightVal = $this->evalConstExpr($b->right);
            if ($left !== null && $rightVal !== null) {
                $leftType = $this->exprType($b->left)->unqualified();
                $pointeeSize = 1;
                if ($leftType instanceof PointerCType) {
                    $pointeeSize = $leftType->to->unqualified()->sizeInBytes() ?? 1;
                }
                $delta = $rightVal * $pointeeSize;
                return [$left[0], $left[1] + ($b->op === BinaryOp::Add ? $delta : -$delta)];
            }
            $right = $this->resolveSymbolAndOffset($b->right);
            $leftVal = $this->evalConstExpr($b->left);
            if ($right !== null && $leftVal !== null) {
                $rightType = $this->exprType($b->right)->unqualified();
                $pointeeSize = 1;
                if ($rightType instanceof PointerCType) {
                    $pointeeSize = $rightType->to->unqualified()->sizeInBytes() ?? 1;
                }
                $delta = $leftVal * $pointeeSize;
                return [$right[0], $right[1] + ($b->op === BinaryOp::Add ? $delta : -$delta)];
            }
            return null;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private function resolveAddressOfSubscript(SubscriptExpr $sub): ?array
    {
        return $this->resolveSymbolAndOffset($sub);
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private function resolveAddressOfMember(MemberExpr $m): ?array
    {
        return $this->resolveSymbolAndOffset($m);
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private function resolvePointerArith(BinaryExpr $b): ?array
    {
        return $this->resolveSymbolAndOffset($b);
    }

    private function evalConstExpr(Expr $expr): ?int
    {
        if ($expr instanceof IntegerLiteral) {
            return $expr->value;
        }
        if ($expr instanceof CharLiteral) {
            return $expr->value;
        }
        if ($expr instanceof Identifier) {
            return $this->enumConstants[$expr->name] ?? null;
        }
        if ($expr instanceof BinaryExpr) {
            $b = $expr;
            $lhs = $this->evalConstExpr($b->left);
            $rhs = $this->evalConstExpr($b->right);
            if ($lhs === null || $rhs === null) {
                return null;
            }
            return match ($b->op) {
                BinaryOp::Add => $lhs + $rhs,
                BinaryOp::Sub => $lhs - $rhs,
                BinaryOp::Mul => $lhs * $rhs,
                BinaryOp::Div => $rhs !== 0 ? intdiv($lhs, $rhs) : null,
                BinaryOp::Mod => $rhs !== 0 ? $lhs % $rhs : null,
                BinaryOp::Shl => $lhs << $rhs,
                BinaryOp::Shr => $lhs >> $rhs,
                BinaryOp::BitAnd => $lhs & $rhs,
                BinaryOp::BitOr => $lhs | $rhs,
                BinaryOp::BitXor => $lhs ^ $rhs,
                BinaryOp::Lt => $lhs < $rhs ? 1 : 0,
                BinaryOp::Gt => $lhs > $rhs ? 1 : 0,
                BinaryOp::Le => $lhs <= $rhs ? 1 : 0,
                BinaryOp::Ge => $lhs >= $rhs ? 1 : 0,
                BinaryOp::Eq => $lhs === $rhs ? 1 : 0,
                BinaryOp::Ne => $lhs !== $rhs ? 1 : 0,
                BinaryOp::LogicAnd => ($lhs !== 0 && $rhs !== 0) ? 1 : 0,
                BinaryOp::LogicOr => ($lhs !== 0 || $rhs !== 0) ? 1 : 0,
                default => null,
            };
        }
        if ($expr instanceof UnaryExpr) {
            $u = $expr;
            $val = $this->evalConstExpr($u->operand);
            if ($val === null) {
                return null;
            }
            return match ($u->op) {
                UnaryOp::Neg => -$val,
                UnaryOp::BitNot => ~$val,
                UnaryOp::Not => $val === 0 ? 1 : 0,
                default => $val,
            };
        }
        if ($expr instanceof CastExpr) {
            return $this->evalConstExpr($expr->expr);
        }
        if ($expr instanceof ConditionalExpr) {
            $c = $expr;
            $cond = $this->evalConstExpr($c->condition);
            if ($cond === null) {
                return null;
            }
            return $cond !== 0 ? $this->evalConstExpr($c->trueExpr) : $this->evalConstExpr($c->falseExpr);
        }
        if ($expr instanceof SizeofExpr) {
            $s = $expr;
            if ($s->typeName !== null) {
                return $s->typeName->sizeInBytes() ?? 0;
            }
            if ($s->expr !== null) {
                return $this->exprType($s->expr)->sizeInBytes() ?? 0;
            }
            return 0;
        }
        return null;
    }

    private function emitStaticLocalData(): void
    {
        if ($this->staticLocalInits === []) {
            return;
        }
        $emitted = [];
        $this->emitLine('.section __DATA,__data');
        foreach ($this->staticLocalInits as $item) {
            if (isset($emitted[$item['name']])) {
                continue;
            }
            $emitted[$item['name']] = true;
            $size = $item['type']->sizeInBytes() ?? 8;
            $this->emitLine('.globl ' . $item['name']);
            $this->emitLine('.p2align 3');
            $this->emitLine($item['name'] . ':');
            $this->emitInitializer($item['init'], $size, $item['type']);
        }
    }

    private function emitCompoundLiterals(): void
    {
        if ($this->compoundLiterals === []) {
            return;
        }
        $this->emitLine('.section __DATA,__data');
        foreach ($this->compoundLiterals as $cl) {
            $size = $cl['type']->sizeInBytes() ?? 0;
            $this->emitLine('.p2align 3');
            $this->emitLine($cl['label'] . ':');
            $this->emitInitializer($cl['init'], $size, $cl['type']);
        }
    }

    private function emitStringLiterals(): void
    {
        if ($this->stringLiterals === []) {
            return;
        }
        $this->emitLine('.section __TEXT,__cstring');
        foreach ($this->stringLiterals as $sl) {
            $this->emitLine($sl['label'] . ':');
            $this->emitLine('.asciz "' . $this->escapeString($sl['value']) . '"');
        }
    }

    private function escapeString(string $s): string
    {
        $result = '';
        $bytes = array_values(unpack('C*', $s));
        $count = count($bytes);
        $idx = 0;
        while ($idx < $count) {
            $byte = $bytes[$idx];
            if ($byte === 0x5C && $idx + 3 < $count
                && $bytes[$idx + 1] >= 0x30 && $bytes[$idx + 1] <= 0x37
                && $bytes[$idx + 2] >= 0x30 && $bytes[$idx + 2] <= 0x37
                && $bytes[$idx + 3] >= 0x30 && $bytes[$idx + 3] <= 0x37) {
                $result .= '\\' . chr($bytes[$idx + 1]) . chr($bytes[$idx + 2]) . chr($bytes[$idx + 3]);
                $idx += 4;
                continue;
            }
            $result .= match ($byte) {
                0x0A => '\\n',
                0x09 => '\\t',
                0x0D => '\\r',
                0x5C => '\\\\',
                0x22 => '\\"',
                0x00 => '\\0',
                default => ($byte >= 0x20 && $byte <= 0x7E) ? chr($byte) : sprintf('\\%03o', $byte),
            };
            $idx++;
        }
        return $result;
    }

    private function addStringLiteral(string $s): string
    {
        $label = 'L_STR_' . $this->stringLabelCounter;
        $this->stringLabelCounter++;
        $this->stringLiterals[] = ['label' => $label, 'value' => $s];
        return $label;
    }

    private function escapeStringLiteral(string $s): string
    {
        $result = '';
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($s[$i]);
            $result .= match (true) {
                $c === 0x22 => '\\"',
                $c === 0x5C => '\\\\',
                $c === 0x0A => '\\n',
                $c === 0x09 => '\\t',
                $c === 0x0D => '\\r',
                $c === 0x00 => '\\0',
                $c >= 32 && $c < 127 => $s[$i],
                default => sprintf('\\%03o', $c),
            };
        }
        return $result;
    }


    private function emitFunction(FuncDecl $fd): void
    {
        $this->currentFuncName = $fd->name;
        $this->localOffset = 0;
        $this->localVarOffsets = [];
        $this->gotoLabels = [];
        $this->frameSize = 0;
        $this->labelCounter = 0;
        $this->indirectReturnOffset = null;
        $this->forcedSretOffset = null;

        $this->emitLine('');
        $this->emitLine('.globl _' . $fd->name);
        $this->emitLine('.p2align 2');
        $this->emitLine('_' . $fd->name . ':');

        $this->emitLine('stp x29, x30, [sp, #-16]!');
        $this->emitLine('mov x29, sp');

        if ($fd->variadic) {
            $this->vaSaveAreaOffset = 16;
        } else {
            $this->vaSaveAreaOffset = 0;
        }

        $this->emitLine('sub sp, sp, #0  ; FRAME_SIZE_PLACEHOLDER');
        $this->framePlaceholderIndex = count($this->lines) - 1;

        $retUnqual = $fd->returnType->unqualified();
        $retSize = $retUnqual->sizeInBytes() ?? 0;
        if (($retUnqual instanceof StructCType || $retUnqual instanceof UnionCType) && $retSize > 16) {
            $this->ensureLocalSpace(8);
            $this->indirectReturnOffset = -$this->localOffset;
            $this->emitStoreFP('x8', $this->indirectReturnOffset);
        }

        $regIndex = 0;
        foreach ($fd->params as $i => $param) {
            $pt = $param->type->unqualified();
            $paramSize = $pt->sizeInBytes() ?? 8;
            if (($pt instanceof StructCType || $pt instanceof UnionCType) && $paramSize > 8 && $paramSize <= 16) {
                $regWidth = 2;
            } else {
                $regWidth = 1;
            }

            $paramName = $param->name ?? '_param_' . $i;

            if (($pt instanceof StructCType || $pt instanceof UnionCType) && $paramSize > 16) {
                // Large aggregates are passed by invisible reference (pointer in one GPR).
                if ($regIndex < 8) {
                    $this->ensureLocalSpace($paramSize);
                    $offset = -$this->localOffset;
                    $this->localVarOffsets[$paramName] = $offset;
                    if ($offset >= -4095 && $offset <= 4095) {
                        $this->emitLine('add x10, x29, #' . $offset);
                    } else {
                        $this->emitLoadImm('x17', $offset);
                        $this->emitLine('add x10, x29, x17');
                    }
                    $this->emitLine('mov x9, ' . ARG_REGS[$regIndex]->x());
                    $this->emitMemCopy(Arm64Reg::X9, Arm64Reg::X10, $paramSize);
                    $this->localVarTypes[$paramName] = $param->type;
                    $regIndex += 1;
                }
                continue;
            }

            if ($regIndex < 8) {
                $this->ensureLocalSpace($regWidth * 8);
                $offset = -$this->localOffset;
                $this->localVarOffsets[$paramName] = $offset;
                $isInt = $pt->isInteger() || $pt->isPointer() || $pt->isFunction();
                if ($isInt) {
                    $this->emitStoreFP(ARG_REGS[$regIndex]->x(), $offset);
                } elseif ($pt instanceof StructCType || $pt instanceof UnionCType) {
                    // Apple ARM64: aggregates ≤8B in one GPR; 9–16B in two consecutive GPRs.
                    $this->emitStoreFP(ARG_REGS[$regIndex]->x(), $offset);
                    if ($regWidth === 2 && $regIndex + 1 < 8) {
                        $this->emitStoreFP(ARG_REGS[$regIndex + 1]->x(), $offset + 8);
                    }
                }
                $this->localVarTypes[$paramName] = $param->type;
                $regIndex += $regWidth;
            } else {
                $stackSrcOffset = 16 + ($regIndex - 8) * 8;
                $this->ensureLocalSpace($regWidth * 8);
                $localOff = -$this->localOffset;
                $this->localVarOffsets[$paramName] = $localOff;
                $this->emitLoadFP('x9', $stackSrcOffset);
                $this->emitStoreFP('x9', $localOff);
                if ($regWidth === 2) {
                    $this->emitLoadFP('x9', $stackSrcOffset + 8);
                    $this->emitStoreFP('x9', $localOff + 8);
                }
                $this->localVarTypes[$paramName] = $param->type;
                $regIndex += $regWidth;
            }
        }

        if ($fd->body !== null) {
            $this->emitCompoundStmt($fd->body);
        }

        $this->emitLine('mov w0, #0  ; default return');
        $this->emitEpilogue();

        $alignedFrameSize = ($this->frameSize + 15) & ~15;
        if ($alignedFrameSize === 0) {
            $alignedFrameSize = 16;
        }
        if ($alignedFrameSize <= 4095) {
            $replacement = ['sub sp, sp, #'.$alignedFrameSize];
        } else {
            $lo = $alignedFrameSize & 0xFFFF;
            $hi = ($alignedFrameSize >> 16) & 0xFFFF;
            $replacement = [
                "mov x16, #{$lo}",
                "movk x16, #{$hi}, lsl #16",
                'sub sp, sp, x16',
            ];
        }
        array_splice($this->lines, $this->framePlaceholderIndex, 1, $replacement);
    }

    private function emitEpilogue(): void
    {
        $this->emitLine('mov sp, x29');
        $this->emitLine('ldp x29, x30, [sp], #16');
        $this->emitLine('ret');
    }

    /**
     * Apple ARM64: aggregates ≤16 bytes are returned in x0 (and x1 if >8).
     */
    private function emitSmallAggregateReturn(Expr $value, CType $type, int $size): void
    {
        $slot = ($size + 15) & ~15;
        if ($slot < 16) {
            $slot = 16;
        }
        $this->emitLine('sub sp, sp, #'.$slot);
        $addrReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
        $this->emitLine('mov '.$addrReg->x().', sp');

        $init = $value;
        if ($value instanceof CompoundLiteralExpr) {
            $init = $value->initList;
        }

        if ($init instanceof InitListExpr) {
            $this->emitLocalInit($addrReg, $init, $type);
        } elseif ($value instanceof CallExpr) {
            $this->emitExpr($value);
            $this->emitLine('str x0, ['.$addrReg->x().']');
            if ($size > 8) {
                $this->emitLine('str x1, ['.$addrReg->x().', #8]');
            }
        } elseif ($value instanceof Identifier && isset($this->localVarOffsets[$value->name])) {
            $src = $this->regAlloc->alloc() ?? Arm64Reg::X10;
            $offset = $this->localVarOffsets[$value->name];
            if ($offset >= -256 && $offset <= 255) {
                $this->emitLine('add '.$src->x().', x29, #'.$offset);
            } else {
                $this->emitLoadImm('x16', $offset);
                $this->emitLine('add '.$src->x().', x29, x16');
            }
            $this->emitLine('ldr x16, ['.$src->x().']');
            $this->emitLine('str x16, ['.$addrReg->x().']');
            if ($size > 8) {
                $this->emitLine('ldr x16, ['.$src->x().', #8]');
                $this->emitLine('str x16, ['.$addrReg->x().', #8]');
            }
            $this->regAlloc->free($src);
        } else {
            // Fallback: evaluate as scalar into first word (legacy path).
            $reg = $this->emitExpr($value);
            $this->emitStoreToAddr($addrReg, $reg, IntCType::$instance);
            $this->regAlloc->free($reg);
        }

        $this->regAlloc->free($addrReg);
        $this->emitLine('ldr x0, [sp]');
        if ($size > 8) {
            $this->emitLine('ldr x1, [sp, #8]');
        }
        $this->emitLine('add sp, sp, #'.$slot);
    }

    /**
     * Apple ARM64: aggregates >16 bytes are returned via the pointer in x8.
     */
    private function emitLargeAggregateReturn(Expr $value, CType $type, int $size): void
    {
        $srcReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
        if ($value instanceof Identifier && isset($this->localVarOffsets[$value->name])) {
            $offset = $this->localVarOffsets[$value->name];
            if ($offset >= -4095 && $offset <= 4095) {
                $this->emitLine('add ' . $srcReg->x() . ', x29, #' . $offset);
            } else {
                $this->emitLoadImm('x17', $offset);
                $this->emitLine('add ' . $srcReg->x() . ', x29, x17');
            }
        } elseif ($value instanceof CompoundLiteralExpr || $value instanceof InitListExpr) {
            $this->ensureLocalSpace($size);
            $tempOff = -$this->localOffset;
            if ($tempOff >= -4095 && $tempOff <= 4095) {
                $this->emitLine('add ' . $srcReg->x() . ', x29, #' . $tempOff);
            } else {
                $this->emitLoadImm('x17', $tempOff);
                $this->emitLine('add ' . $srcReg->x() . ', x29, x17');
            }
            $init = $value instanceof CompoundLiteralExpr ? $value->initList : $value;
            $this->emitLocalInit($srcReg, $init, $type);
        } else {
            $this->regAlloc->free($srcReg);
            $srcReg = $this->emitAddr($value);
        }

        $dstReg = $this->regAlloc->alloc() ?? Arm64Reg::X10;
        $this->emitLoadFP($dstReg->x(), $this->indirectReturnOffset ?? 0);
        $this->emitMemCopy($srcReg, $dstReg, $size);
        $this->regAlloc->free($srcReg);
        $this->regAlloc->free($dstReg);
    }

    private function ensureLocalSpace(int $size): void
    {
        $aligned = ($size + 7) & ~7;
        $this->localOffset += $aligned;
        if ($this->localOffset > $this->frameSize) {
            $this->frameSize = $this->localOffset;
        }
    }

    private function allocLocal(string $name, CType $type): void
    {
        $size = $type->sizeInBytes() ?? 8;
        $this->ensureLocalSpace($size);
        $offset = -$this->localOffset;
        $this->localVarOffsets[$name] = $offset;
        $this->localVarTypes[$name] = $type;
    }

    private function emitCompoundStmt(CompoundStmt $cs): void
    {
        foreach ($cs->statements as $stmt) {
            $this->emitStmt($stmt);
        }
    }


    private function emitStmt(Stmt $stmt): void
    {
        if ($stmt instanceof ExprStmt) {
            $es = $stmt;
            if ($es->expr !== null) {
                $this->emitExpr($es->expr);
                $this->regAlloc->reset();
            }
            return;
        }

        if ($stmt instanceof CompoundStmt) {
            $this->emitCompoundStmt($stmt);
            return;
        }

        if ($stmt instanceof DeclStmt) {
            $ds = $stmt;
            foreach ($ds->decls as $d) {
                if ($d instanceof VarDecl) {
                    $vd = $d;
                    if ($vd->storageClass === StorageClass::Static) {
                        $globalName = '_' . $this->currentFuncName . '__' . $vd->name;
                        $isFirstTime = !in_array($globalName, $this->staticLocalGlobals, true);
                        $this->staticLocalGlobals[$vd->name] = $globalName;
                        if ($isFirstTime) {
                            $this->globalLabels[$globalName] = true;
                            if ($vd->initializer !== null) {
                                $this->staticLocalInits[] = ['name' => $globalName, 'type' => $vd->type, 'init' => $vd->initializer];
                            } else {
                                $this->staticLocalInits[] = [
                                    'name' => $globalName,
                                    'type' => $vd->type,
                                    'init' => new IntegerLiteral(0, SourceLoc::unknown()),
                                ];
                            }
                            $this->localVarTypes[$vd->name] = $vd->type;
                        }
                    } else {
                        $this->allocLocal($vd->name, $vd->type);
                        if ($vd->initializer !== null) {
                            if ($vd->initializer instanceof StringLiteral) {
                                $sl = $vd->initializer;
                                $tu = $vd->type->unqualified();
                                if ($tu instanceof ArrayCType && $tu->of->isChar()) {
                                    $addrReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                                    $offset = $this->localVarOffsets[$vd->name] ?? null;
                                    if ($offset !== null) {
                                        if ($offset >= -256 && $offset <= 255) {
                                            $this->emitLine('add ' . $addrReg->x() . ', x29, #' . $offset);
                                        } else {
                                            $this->emitLoadImm('x16', $offset);
                                            $this->emitLine('add ' . $addrReg->x() . ', x29, x16');
                                        }
                                        $label = $this->addStringLiteral($sl->value);
                                        $this->emitCopyStringToAddr($addrReg, $sl, $tu);
                                    }
                                    $this->regAlloc->free($addrReg);
                                } else {
                                    $reg = $this->emitExpr($vd->initializer);
                                    $this->storeLocal($vd->name, $reg, $vd->type);
                                }
                            } elseif ($vd->initializer instanceof CompoundLiteralExpr
                                && $vd->type->unqualified()->isPointer()) {
                                $addrReg = $this->emitAddr($vd->initializer);
                                $this->storeLocal($vd->name, $addrReg, $vd->type);
                                $this->regAlloc->free($addrReg);
                            } elseif ($vd->initializer instanceof CastExpr
                                && $vd->initializer->expr instanceof CompoundLiteralExpr
                                && $vd->type->unqualified()->isPointer()) {
                                $addrReg = $this->emitAddr($vd->initializer->expr);
                                $this->storeLocal($vd->name, $addrReg, $vd->type);
                                $this->regAlloc->free($addrReg);
                            } elseif ($vd->initializer instanceof InitListExpr || $vd->initializer instanceof CompoundLiteralExpr) {
                                $addrReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                                $offset = $this->localVarOffsets[$vd->name] ?? null;
                                if ($offset !== null) {
                                    if ($offset >= -256 && $offset <= 255) {
                                        $this->emitLine('add ' . $addrReg->x() . ', x29, #' . $offset);
                                    } else {
                                        $this->emitLoadImm('x16', $offset);
                                        $this->emitLine('add ' . $addrReg->x() . ', x29, x16');
                                    }
                                    $initType = $vd->type;
                                    $init = $vd->initializer;
                                    if ($vd->initializer instanceof CompoundLiteralExpr) {
                                        $initType = $vd->initializer->type;
                                        $init = $vd->initializer->initList;
                                    }
                                    $this->emitLocalInit($addrReg, $init, $initType);
                                }
                                $this->regAlloc->free($addrReg);
                            } else {
                                $tu = $vd->type->unqualified();
                                $aggSize = $tu->sizeInBytes() ?? 0;
                                if (($tu instanceof StructCType || $tu instanceof UnionCType) && $aggSize > 8) {
                                    if ($vd->initializer instanceof CallExpr && $aggSize <= 16) {
                                        // Small aggregate return lives in x0(/x1), not memory.
                                        $reg = $this->emitExpr($vd->initializer);
                                        $this->storeLocal($vd->name, $reg, $vd->type);
                                    } elseif ($vd->initializer instanceof CallExpr && $aggSize > 16) {
                                        // Large aggregate: callee writes through x8 into our local.
                                        $this->forcedSretOffset = $this->localVarOffsets[$vd->name] ?? null;
                                        $this->emitExpr($vd->initializer);
                                        $this->forcedSretOffset = null;
                                    } else {
                                        $srcReg = $this->emitAddr($vd->initializer);
                                        $dstReg = $this->regAlloc->alloc() ?? Arm64Reg::X10;
                                        $offset = $this->localVarOffsets[$vd->name] ?? null;
                                        if ($offset !== null) {
                                            if ($offset >= -4095 && $offset <= 4095) {
                                                $this->emitLine('add ' . $dstReg->x() . ', x29, #' . $offset);
                                            } else {
                                                $this->emitLoadImm('x17', $offset);
                                                $this->emitLine('add ' . $dstReg->x() . ', x29, x17');
                                            }
                                            $this->emitMemCopy($srcReg, $dstReg, $aggSize);
                                        }
                                        $this->regAlloc->free($srcReg);
                                        $this->regAlloc->free($dstReg);
                                    }
                                } else {
                                    $reg = $this->emitExpr($vd->initializer);
                                    $this->storeLocal($vd->name, $reg, $vd->type);
                                }
                            }
                        }
                    }
                }
            }
            $this->regAlloc->reset();
            return;
        }

        if ($stmt instanceof IfStmt) {
            $this->emitIfStmt($stmt);
            return;
        }
        if ($stmt instanceof WhileStmt) {
            $this->emitWhileStmt($stmt);
            return;
        }
        if ($stmt instanceof DoWhileStmt) {
            $this->emitDoWhileStmt($stmt);
            return;
        }
        if ($stmt instanceof ForStmt) {
            $this->emitForStmt($stmt);
            return;
        }
        if ($stmt instanceof ReturnStmt) {
            $rs = $stmt;
            if ($rs->value !== null) {
                $retType = $this->functionReturnTypes[$this->currentFuncName] ?? null;
                $retUnqual = $retType?->unqualified();
                if (($retUnqual instanceof StructCType || $retUnqual instanceof UnionCType)
                    && ($size = $retUnqual->sizeInBytes() ?? 0) > 0
                    && $size <= 16) {
                    $this->emitSmallAggregateReturn($rs->value, $retUnqual, $size);
                } elseif (($retUnqual instanceof StructCType || $retUnqual instanceof UnionCType)
                    && ($size = $retUnqual->sizeInBytes() ?? 0) > 16
                    && $this->indirectReturnOffset !== null) {
                    $this->emitLargeAggregateReturn($rs->value, $retUnqual, $size);
                } else {
                    $reg = $this->emitExpr($rs->value);
                    if ($reg !== Arm64Reg::X0) {
                        $this->emitLine('mov x0, ' . $reg->x());
                    }
                }
            } else {
                $this->emitLine('mov w0, #0');
            }
            $this->emitEpilogue();
            return;
        }
        if ($stmt instanceof BreakStmt) {
            if ($this->breakLabels !== []) {
                $this->emitLine('b ' . $this->breakLabels[array_key_last($this->breakLabels)]);
            }
            return;
        }
        if ($stmt instanceof ContinueStmt) {
            if ($this->continueLabels !== []) {
                $this->emitLine('b ' . $this->continueLabels[array_key_last($this->continueLabels)]);
            }
            return;
        }
        if ($stmt instanceof SwitchStmt) {
            $this->emitSwitchStmt($stmt);
            return;
        }
        if ($stmt instanceof CaseStmt || $stmt instanceof DefaultStmt) {
            return;
        }
        if ($stmt instanceof GotoStmt) {
            $g = $stmt;
            if (isset($this->gotoLabels[$g->label])) {
                $asmLabel = $this->gotoLabels[$g->label];
            } else {
                $asmLabel = $this->newLabel();
                $this->gotoLabels[$g->label] = $asmLabel;
            }
            $this->emitLine('b ' . $asmLabel);
            return;
        }
        if ($stmt instanceof LabelStmt) {
            $l = $stmt;
            if (isset($this->gotoLabels[$l->name])) {
                $asmLabel = $this->gotoLabels[$l->name];
            } else {
                $asmLabel = $this->newLabel();
                $this->gotoLabels[$l->name] = $asmLabel;
            }
            $this->emitLine($asmLabel . ':');
            $this->emitStmt($l->stmt);
            return;
        }
        if ($stmt instanceof EmptyStmt) {
            return;
        }
    }

    private function emitIfStmt(IfStmt $is): void
    {
        $condReg = $this->emitExpr($is->condition);
        $this->regAlloc->reset();
        $elseLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $this->emitLine('cbz ' . $condReg->x() . ', ' . $elseLabel);
        $this->emitStmt($is->thenStmt);
        $this->regAlloc->reset();
        $this->emitLine('b ' . $endLabel);
        $this->emitLine($elseLabel . ':');
        if ($is->elseStmt !== null) {
            $this->emitStmt($is->elseStmt);
            $this->regAlloc->reset();
        }
        $this->emitLine($endLabel . ':');
    }

    private function emitWhileStmt(WhileStmt $ws): void
    {
        $startLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $this->emitLine($startLabel . ':');
        $this->regAlloc->reset();
        $condReg = $this->emitExpr($ws->condition);
        $this->emitLine('cbz ' . $condReg->x() . ', ' . $endLabel);
        $this->regAlloc->reset();
        $this->breakLabels[] = $endLabel;
        $this->continueLabels[] = $startLabel;
        $this->emitStmt($ws->body);
        array_pop($this->breakLabels);
        array_pop($this->continueLabels);
        $this->regAlloc->reset();
        $this->emitLine('b ' . $startLabel);
        $this->emitLine($endLabel . ':');
    }

    private function emitDoWhileStmt(DoWhileStmt $dws): void
    {
        $startLabel = $this->newLabel();
        $continueLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $this->emitLine($startLabel . ':');
        $this->breakLabels[] = $endLabel;
        $this->continueLabels[] = $continueLabel;
        $this->emitStmt($dws->body);
        array_pop($this->breakLabels);
        array_pop($this->continueLabels);
        $this->emitLine($continueLabel . ':');
        $this->regAlloc->reset();
        $condReg = $this->emitExpr($dws->condition);
        $this->emitLine('cbnz ' . $condReg->x() . ', ' . $startLabel);
        $this->emitLine($endLabel . ':');
    }

    private function emitForStmt(ForStmt $fs): void
    {
        if ($fs->initStmt !== null) {
            $this->emitStmt($fs->initStmt);
        }
        $this->regAlloc->reset();
        $startLabel = $this->newLabel();
        $continueLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $this->emitLine($startLabel . ':');
        if ($fs->condition !== null) {
            $condReg = $this->emitExpr($fs->condition);
            $this->emitLine('cbz ' . $condReg->x() . ', ' . $endLabel);
        }
        $this->regAlloc->reset();
        $this->breakLabels[] = $endLabel;
        $this->continueLabels[] = $continueLabel;
        $this->emitStmt($fs->body);
        array_pop($this->breakLabels);
        array_pop($this->continueLabels);
        $this->emitLine($continueLabel . ':');
        $this->regAlloc->reset();
        if ($fs->increment !== null) {
            $this->emitExpr($fs->increment);
        }
        $this->regAlloc->reset();
        $this->emitLine('b ' . $startLabel);
        $this->emitLine($endLabel . ':');
    }

    private function emitSwitchStmt(SwitchStmt $ss): void
    {
        $endLabel = $this->newLabel();
        $valueReg = $this->emitExpr($ss->value);
        $this->regAlloc->reset();

        $this->ensureLocalSpace(8);
        $switchOffset = -$this->localOffset;
        $this->emitStoreFP($valueReg->x(), $switchOffset);
        $this->regAlloc->reset();

        $caseLabelMap = [];
        $defaultLabel = null;

        $emitCaseComparison = function (Expr $caseVal) use ($switchOffset): string {
            $label = $this->newLabel();
            $val = $this->emitExpr($caseVal);
            $switchTemp = $this->regAlloc->alloc() ?? Arm64Reg::X10;
            $this->emitLoadFP($switchTemp->x(), $switchOffset);
            $this->emitLine('cmp ' . $switchTemp->x() . ', ' . $val->x());
            $this->emitLine('b.eq ' . $label);
            $this->regAlloc->free($switchTemp);
            $this->regAlloc->free($val);
            $this->regAlloc->reset();
            return $label;
        };

        foreach ($ss->cases as $idx => $stmt) {
            if ($stmt instanceof CaseStmt) {
                $cs = $stmt;
                $caseLabelMap[(string) $idx] = $emitCaseComparison($cs->value);
                if ($cs->stmt instanceof CompoundStmt) {
                    $comp = $cs->stmt;
                    foreach ($comp->statements as $innerIdx => $innerStmt) {
                        if ($innerStmt instanceof CaseStmt) {
                            $innerCs = $innerStmt;
                            $caseLabelMap[$idx . '.' . $innerIdx] = $emitCaseComparison($innerCs->value);
                        } elseif ($innerStmt instanceof DefaultStmt) {
                            $defaultLabel = $this->newLabel();
                        }
                    }
                }
            } elseif ($stmt instanceof CompoundStmt) {
                $comp = $stmt;
                foreach ($comp->statements as $innerIdx => $innerStmt) {
                    if ($innerStmt instanceof CaseStmt) {
                        $innerCs = $innerStmt;
                        $caseLabelMap[$idx . '.' . $innerIdx] = $emitCaseComparison($innerCs->value);
                    } elseif ($innerStmt instanceof DefaultStmt) {
                        $defaultLabel = $this->newLabel();
                    }
                }
            } elseif ($stmt instanceof DefaultStmt) {
                $defaultLabel = $this->newLabel();
            }
        }

        if ($defaultLabel !== null) {
            $this->emitLine('b ' . $defaultLabel);
        } else {
            $this->emitLine('b ' . $endLabel);
        }

        $this->breakLabels[] = $endLabel;

        $emitCompoundWithCases = function (CompoundStmt $comp, string $keyPrefix) use (&$caseLabelMap, &$defaultLabel): void {
            foreach ($comp->statements as $innerIdx => $innerStmt) {
                $key = $keyPrefix . '.' . $innerIdx;
                if (isset($caseLabelMap[$key])) {
                    $this->emitLine($caseLabelMap[$key] . ':');
                } elseif ($innerStmt instanceof DefaultStmt && $defaultLabel !== null) {
                    $this->emitLine($defaultLabel . ':');
                }
                if ($innerStmt instanceof CaseStmt) {
                    $cs = $innerStmt;
                    if ($cs->stmt !== null) {
                        $this->emitStmt($cs->stmt);
                    }
                } elseif ($innerStmt instanceof DefaultStmt) {
                    $ds = $innerStmt;
                    if ($ds->stmt !== null) {
                        $this->emitStmt($ds->stmt);
                    }
                } else {
                    $this->emitStmt($innerStmt);
                }
            }
        };

        foreach ($ss->cases as $idx => $stmt) {
            $key = (string) $idx;
            if (isset($caseLabelMap[$key])) {
                $this->emitLine($caseLabelMap[$key] . ':');
            } elseif ($stmt instanceof DefaultStmt && $defaultLabel !== null) {
                $this->emitLine($defaultLabel . ':');
            }

            if ($stmt instanceof CaseStmt) {
                $cs = $stmt;
                if ($cs->stmt !== null) {
                    if ($cs->stmt instanceof CompoundStmt) {
                        $emitCompoundWithCases($cs->stmt, (string) $idx);
                    } else {
                        $this->emitStmt($cs->stmt);
                    }
                }
            } elseif ($stmt instanceof DefaultStmt) {
                $ds = $stmt;
                if ($ds->stmt !== null) {
                    $this->emitStmt($ds->stmt);
                }
            } elseif ($stmt instanceof CompoundStmt) {
                $emitCompoundWithCases($stmt, (string) $idx);
            } else {
                $this->emitStmt($stmt);
            }
        }

        array_pop($this->breakLabels);
        $this->emitLine($endLabel . ':');
    }

    private function emitExpr(Expr $expr): Arm64Reg
    {
        if ($expr instanceof IntegerLiteral) {
            $l = $expr;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLoadImm($reg->x(), $l->value);
            return $reg;
        }
        if ($expr instanceof CharLiteral) {
            $cl = $expr;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('mov ' . $reg->x() . ', #' . $cl->value);
            return $reg;
        }
        if ($expr instanceof BoolLiteral) {
            $bl = $expr;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('mov ' . $reg->x() . ', #' . ($bl->value ? 1 : 0));
            return $reg;
        }
        if ($expr instanceof FloatLiteral) {
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('mov ' . $reg->x() . ', #0  ; float not supported yet');
            return $reg;
        }
        if ($expr instanceof StringLiteral) {
            $sl = $expr;
            $label = $this->addStringLiteral($sl->value);
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('adrp ' . $reg->x() . ', ' . $label . '@PAGE');
            $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', ' . $label . '@PAGEOFF');
            return $reg;
        }
        if ($expr instanceof Identifier) {
            $id = $expr;
            if (isset($this->enumConstants[$id->name])) {
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLoadImm($reg->x(), $this->enumConstants[$id->name]);
                return $reg;
            }
            if (isset($this->localVarOffsets[$id->name])) {
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $offset = $this->localVarOffsets[$id->name];
                $t = $this->localVarTypes[$id->name] ?? null;
                if ($t !== null && $t->unqualified() instanceof ArrayCType) {
                    if ($offset >= -256 && $offset <= 255) {
                        $this->emitLine('add ' . $reg->x() . ', x29, #' . $offset);
                    } else {
                        $this->emitLoadImm('x16', $offset);
                        $this->emitLine('add ' . $reg->x() . ', x29, x16');
                    }
                    return $reg;
                }
                if ($t !== null) {
                    if ($offset >= -256 && $offset <= 255) {
                        $this->emitLine('add ' . $reg->x() . ', x29, #' . $offset);
                    } else {
                        $this->emitLoadImm('x17', $offset);
                        $this->emitLine('add ' . $reg->x() . ', x29, x17');
                    }
                    $this->emitLoad($reg, $t);
                } else {
                    $this->emitLoadFP($reg->x(), $offset);
                }
                return $reg;
            }
            if (isset($this->staticLocalGlobals[$id->name])) {
                $globalName = $this->staticLocalGlobals[$id->name];
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLine('adrp ' . $reg->x() . ', ' . $globalName . '@PAGE');
                $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', ' . $globalName . '@PAGEOFF');
                $t = $this->localVarTypes[$id->name] ?? null;
                if ($t !== null && $t->unqualified() instanceof ArrayCType) {
                    return $reg;
                }
                $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ']');
                return $reg;
            }
            if (isset($this->globalLabels[$id->name])) {
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                if (isset($this->externGlobals[$id->name])) {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@GOTPAGE');
                    $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ', _' . $id->name . '@GOTPAGEOFF]');
                    $gt = $this->globalVarTypes[$id->name] ?? null;
                    if ($gt !== null && $gt->unqualified() instanceof ArrayCType) {
                        return $reg;
                    }
                    $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ']');
                } else {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@PAGE');
                    $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', _' . $id->name . '@PAGEOFF');
                    $gt = $this->globalVarTypes[$id->name] ?? null;
                    if ($gt !== null && $gt->unqualified() instanceof ArrayCType) {
                        return $reg;
                    }
                    $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ']');
                }
                return $reg;
            }
            if (isset($this->functionNames[$id->name])) {
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                if (isset($this->definedFunctions[$id->name])) {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@PAGE');
                    $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', _' . $id->name . '@PAGEOFF');
                } else {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@GOTPAGE');
                    $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ', _' . $id->name . '@GOTPAGEOFF]');
                }
                return $reg;
            }
            if ($id->name === '__builtin_va_list' || $id->name === '__va_list_tag') {
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLine('mov ' . $reg->x() . ', #0');
                return $reg;
            }
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@GOTPAGE');
            $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ', _' . $id->name . '@GOTPAGEOFF]');
            return $reg;
        }
        if ($expr instanceof BinaryExpr) {
            return $this->emitBinaryExpr($expr);
        }
        if ($expr instanceof UnaryExpr) {
            return $this->emitUnaryExpr($expr);
        }
        if ($expr instanceof AssignExpr) {
            return $this->emitAssignExpr($expr);
        }
        if ($expr instanceof CallExpr) {
            return $this->emitCallExpr($expr);
        }
        if ($expr instanceof SubscriptExpr) {
            return $this->emitSubscriptExpr($expr);
        }
        if ($expr instanceof MemberExpr) {
            return $this->emitMemberExpr($expr);
        }
        if ($expr instanceof ConditionalExpr) {
            return $this->emitConditionalExpr($expr);
        }
        if ($expr instanceof CastExpr) {
            $reg = $this->emitExpr($expr->expr);
            $to = $expr->type->unqualified();
            if ($to instanceof BoolCType) {
                $this->emitLine('cmp ' . $reg->x() . ', #0');
                $this->emitLine('cset ' . $reg->x() . ', ne');
            }

            return $reg;
        }
        if ($expr instanceof SizeofExpr) {
            $s = $expr;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            if ($s->typeName !== null) {
                $t = $s->typeName->unqualified();
                if ($t instanceof StructCType && $t->record->fields === [] && isset($this->knownRecords[$t->record->name])) {
                    $t = new StructCType($this->knownRecords[$t->record->name]);
                }
                if ($t instanceof UnionCType && $t->record->fields === [] && isset($this->knownRecords[$t->record->name])) {
                    $t = new UnionCType($this->knownRecords[$t->record->name]);
                }
                $size = $t->sizeInBytes() ?? 0;
            } elseif ($s->expr !== null) {
                $size = $this->exprType($s->expr)->sizeInBytes() ?? 4;
            } else {
                $size = 0;
            }
            $this->emitLine('mov ' . $reg->x() . ', #' . $size);
            return $reg;
        }
        if ($expr instanceof CompoundLiteralExpr) {
            $tu = $expr->type->unqualified();
            if ($tu instanceof ArrayCType || $tu instanceof IncompleteArrayCType) {
                return $this->emitAddr($expr);
            }

            return $this->emitExpr($expr->initList);
        }
        if ($expr instanceof InitListExpr) {
            $il = $expr;
            if ($il->values !== []) {
                return $this->emitExpr($il->values[0]);
            }
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('mov ' . $reg->x() . ', #0');
            return $reg;
        }

        $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
        $this->emitLine('mov ' . $reg->x() . ', #0');
        return $reg;
    }

    private function emitBinaryExpr(BinaryExpr $b): Arm64Reg
    {
        if ($b->op === BinaryOp::LogicAnd) {
            $leftReg = $this->emitExpr($b->left);
            $falseLabel = $this->newLabel();
            $endLabel = $this->newLabel();
            $this->emitLine('cbz ' . $leftReg->x() . ', ' . $falseLabel);
            $rightReg = $this->emitExpr($b->right);
            $this->emitLine('cbz ' . $rightReg->x() . ', ' . $falseLabel);
            $this->emitLine('mov ' . $leftReg->x() . ', #1');
            $this->emitLine('b ' . $endLabel);
            $this->emitLine($falseLabel . ':');
            $this->emitLine('mov ' . $leftReg->x() . ', #0');
            $this->regAlloc->free($rightReg);
            $this->emitLine($endLabel . ':');
            return $leftReg;
        }

        if ($b->op === BinaryOp::LogicOr) {
            $leftReg = $this->emitExpr($b->left);
            $trueLabel = $this->newLabel();
            $endLabel = $this->newLabel();
            $this->emitLine('cbnz ' . $leftReg->x() . ', ' . $trueLabel);
            $rightReg = $this->emitExpr($b->right);
            $this->emitLine('cbnz ' . $rightReg->x() . ', ' . $trueLabel);
            $this->emitLine('mov ' . $leftReg->x() . ', #0');
            $this->emitLine('b ' . $endLabel);
            $this->emitLine($trueLabel . ':');
            $this->emitLine('mov ' . $leftReg->x() . ', #1');
            $this->regAlloc->free($rightReg);
            $this->emitLine($endLabel . ':');
            return $leftReg;
        }

        if ($b->op === BinaryOp::Comma) {
            $leftReg = $this->emitExpr($b->left);
            $this->regAlloc->free($leftReg);
            return $this->emitExpr($b->right);
        }

        $leftReg = $this->emitExpr($b->left);
        $rightReg = $this->emitExpr($b->right);

        $leftType = $this->exprType($b->left)->unqualified();
        $rightType = $this->exprType($b->right)->unqualified();
        $is32BitSigned = match (true) {
            $leftType instanceof IntCType,
            $leftType instanceof ShortCType,
            $leftType instanceof SCharCType,
            $leftType instanceof CharCType,
            $leftType instanceof EnumCType => true,
            $leftType instanceof UIntCType,
            $leftType instanceof UShortCType,
            $leftType instanceof UCharCType => false,
            default => false,
        };
        $unsignedArith = ($leftType->isInteger() && !$leftType->isSigned())
            || ($rightType->isInteger() && !$rightType->isSigned());

        $leftIsPtrLike = $leftType->isPointer() || $leftType->isArray();
        $rightIsPtrLike = $rightType->isPointer() || $rightType->isArray();
        $isPtrArith = ($leftIsPtrLike || $rightIsPtrLike)
            && !($leftIsPtrLike && $rightIsPtrLike);
        if ($isPtrArith && ($b->op === BinaryOp::Add || $b->op === BinaryOp::Sub)) {
            $ptrType = $leftIsPtrLike ? $leftType : $rightType;
            $pointeeSize = 4;
            if ($ptrType instanceof PointerCType) {
                $t = $ptrType->to->unqualified();
                $pointeeSize = $t->isPointer() ? 8 : ($t->sizeInBytes() ?? 4);
            } elseif ($ptrType instanceof ArrayCType || $ptrType instanceof IncompleteArrayCType) {
                $t = $ptrType->of->unqualified();
                $pointeeSize = $t->isPointer() ? 8 : ($t->sizeInBytes() ?? 4);
            }
            $intReg = $leftIsPtrLike ? $rightReg : $leftReg;
            $intType = $leftIsPtrLike ? $rightType : $leftType;
            if ($intType->isSigned32Bit()) {
                $this->emitLine('sxtw ' . $intReg->x() . ', ' . $intReg->w());
            }
            if ($pointeeSize > 1) {
                if ($pointeeSize === 2) {
                    $this->emitLine('lsl ' . $intReg->x() . ', ' . $intReg->x() . ', #1');
                } elseif ($pointeeSize === 4) {
                    $this->emitLine('lsl ' . $intReg->x() . ', ' . $intReg->x() . ', #2');
                } elseif ($pointeeSize === 8) {
                    $this->emitLine('lsl ' . $intReg->x() . ', ' . $intReg->x() . ', #3');
                } else {
                    $this->emitLine('mov x16, #' . $pointeeSize);
                    $this->emitLine('mul ' . $intReg->x() . ', ' . $intReg->x() . ', x16');
                }
            }
        }

        match ($b->op) {
            BinaryOp::Add => $this->emitLine('add ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::Sub => (function () use ($leftReg, $rightReg, $leftType, $rightType): void {
                if ($leftType->isPointer() && $rightType->isPointer()) {
                    $this->emitLine('sub ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x());
                    $pointeeSize = 1;
                    if ($leftType instanceof PointerCType) {
                        $t = $leftType->to->unqualified();
                        $pointeeSize = $t->isPointer() ? 8 : ($t->sizeInBytes() ?? 4);
                    }
                    if ($pointeeSize > 1) {
                        if ($pointeeSize === 2) {
                            $this->emitLine('asr ' . $leftReg->x() . ', ' . $leftReg->x() . ', #1');
                        } elseif ($pointeeSize === 4) {
                            $this->emitLine('asr ' . $leftReg->x() . ', ' . $leftReg->x() . ', #2');
                        } elseif ($pointeeSize === 8) {
                            $this->emitLine('asr ' . $leftReg->x() . ', ' . $leftReg->x() . ', #3');
                        } else {
                            $this->emitLine('mov x16, #' . $pointeeSize);
                            $this->emitLine('sdiv ' . $leftReg->x() . ', ' . $leftReg->x() . ', x16');
                        }
                    }
                } else {
                    $this->emitLine('sub ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x());
                }
            })(),
            BinaryOp::Mul => $this->emitLine('mul ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::Div => (function () use ($leftReg, $rightReg, $unsignedArith, $is32BitSigned): void {
                if (!$unsignedArith && $is32BitSigned) {
                    $this->emitLine('sxtw ' . $leftReg->x() . ', ' . $leftReg->w());
                    $this->emitLine('sxtw ' . $rightReg->x() . ', ' . $rightReg->w());
                }
                $op = $unsignedArith ? 'udiv' : 'sdiv';
                $this->emitLine($op . ' ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x());
            })(),
            BinaryOp::Mod => (function () use ($leftReg, $rightReg, $unsignedArith, $is32BitSigned): void {
                if (!$unsignedArith && $is32BitSigned) {
                    $this->emitLine('sxtw ' . $leftReg->x() . ', ' . $leftReg->w());
                    $this->emitLine('sxtw ' . $rightReg->x() . ', ' . $rightReg->w());
                }
                $op = $unsignedArith ? 'udiv' : 'sdiv';
                $this->emitLine($op . ' x16, ' . $leftReg->x() . ', ' . $rightReg->x());
                $this->emitLine('msub ' . $leftReg->x() . ', x16, ' . $rightReg->x() . ', ' . $leftReg->x());
            })(),
            BinaryOp::Shl => $this->emitLine('lsl ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::Shr => (function () use ($leftReg, $rightReg, $unsignedArith, $is32BitSigned): void {
                if (!$unsignedArith && $is32BitSigned) {
                    $this->emitLine('sxtw ' . $leftReg->x() . ', ' . $leftReg->w());
                }
                $op = $unsignedArith ? 'lsr' : 'asr';
                $this->emitLine($op . ' ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x());
            })(),
            BinaryOp::BitAnd => $this->emitLine('and ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::BitOr => $this->emitLine('orr ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::BitXor => $this->emitLine('eor ' . $leftReg->x() . ', ' . $leftReg->x() . ', ' . $rightReg->x()),
            BinaryOp::Eq, BinaryOp::Ne, BinaryOp::Lt, BinaryOp::Le, BinaryOp::Gt, BinaryOp::Ge => (function () use ($b, $leftReg, $rightReg, $leftType, $rightType, $is32BitSigned): void {
                if ($is32BitSigned) {
                    $this->emitLine('sxtw ' . $leftReg->x() . ', ' . $leftReg->w());
                    $this->emitLine('sxtw ' . $rightReg->x() . ', ' . $rightReg->w());
                }
                $this->emitLine('cmp ' . $leftReg->x() . ', ' . $rightReg->x());
                $unsigned = ($leftType->isInteger() && !$leftType->isSigned())
                    || ($rightType->isInteger() && !$rightType->isSigned())
                    || $leftType->isPointer()
                    || $rightType->isPointer();
                $cond = match ($b->op) {
                    BinaryOp::Eq => 'eq',
                    BinaryOp::Ne => 'ne',
                    BinaryOp::Lt => $unsigned ? 'lo' : 'lt',
                    BinaryOp::Le => $unsigned ? 'ls' : 'le',
                    BinaryOp::Gt => $unsigned ? 'hi' : 'gt',
                    BinaryOp::Ge => $unsigned ? 'hs' : 'ge',
                    default => 'eq',
                };
                $this->emitLine('cset ' . $leftReg->x() . ', ' . $cond);
            })(),
            default => null,
        };

        $this->regAlloc->free($rightReg);
        return $leftReg;
    }

    private function emitUnaryExpr(UnaryExpr $u): Arm64Reg
    {
        if ($u->op === UnaryOp::AddressOf) {
            return $this->emitAddr($u->operand);
        }

        if ($u->op === UnaryOp::Dereference) {
            $operandType = $this->exprType($u->operand)->unqualified();
            if ($operandType->isArray()) {
                $addrReg = $this->emitAddr($u->operand);
            } else {
                $addrReg = $this->emitExpr($u->operand);
            }
            $pointedType = IntCType::$instance;
            if ($operandType instanceof PointerCType) {
                $pointedType = $operandType->to;
            } elseif ($operandType instanceof ArrayCType) {
                $pointedType = $operandType->of;
            }
            if ($pointedType->unqualified() instanceof ArrayCType) {
                return $addrReg;
            }
            $this->emitLoad($addrReg, $pointedType);
            return $addrReg;
        }

        if (in_array($u->op, [UnaryOp::PreInc, UnaryOp::PreDec, UnaryOp::PostInc, UnaryOp::PostDec], true)) {
            $addrReg = $this->emitAddr($u->operand);
            $valReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $operandType = $this->exprType($u->operand)->unqualified();
            $incSize = 1;
            if ($operandType->isPointer() && $operandType instanceof PointerCType) {
                $incSize = $operandType->to->sizeInBytes() ?? 1;
            }
            $this->emitLine('mov ' . $valReg->x() . ', ' . $addrReg->x());
            $this->emitLoad($valReg, $operandType);

            if ($u->op === UnaryOp::PostInc || $u->op === UnaryOp::PostDec) {
                $origReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLine('mov ' . $origReg->x() . ', ' . $valReg->x());
                if ($u->op === UnaryOp::PostInc) {
                    $this->emitLine('add ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $incSize);
                } else {
                    $this->emitLine('sub ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $incSize);
                }
                $this->emitStoreToAddr($addrReg, $valReg, $operandType);
                $this->regAlloc->free($valReg);
                $this->regAlloc->free($addrReg);
                return $origReg;
            }

            if ($u->op === UnaryOp::PreInc) {
                $this->emitLine('add ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $incSize);
            } else {
                $this->emitLine('sub ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $incSize);
            }
            $this->emitStoreToAddr($addrReg, $valReg, $operandType);
            $this->regAlloc->free($addrReg);
            return $valReg;
        }

        $operandReg = $this->emitExpr($u->operand);
        match ($u->op) {
            UnaryOp::Neg => $this->emitLine('neg ' . $operandReg->x() . ', ' . $operandReg->x()),
            UnaryOp::Pos => null,
            UnaryOp::Not => (function () use ($operandReg): void {
                $this->emitLine('cmp ' . $operandReg->x() . ', #0');
                $this->emitLine('cset ' . $operandReg->x() . ', eq');
            })(),
            UnaryOp::BitNot => (function () use ($operandReg, $u): void {
                $t = $this->exprType($u->operand)->unqualified();
                $wide = $t instanceof LongCType || $t instanceof ULongCType
                    || $t instanceof LongLongCType || $t instanceof ULongLongCType
                    || $t instanceof PointerCType;
                if ($wide) {
                    $this->emitLine('mvn ' . $operandReg->x() . ', ' . $operandReg->x());
                } else {
                    // 32-bit (post–integer-promotion) invert; keeps high bits clear.
                    $this->emitLine('mvn ' . $operandReg->w() . ', ' . $operandReg->w());
                }
            })(),
            default => null,
        };
        return $operandReg;
    }

    private function emitAssignExpr(AssignExpr $a): Arm64Reg
    {
        if ($a->op !== AssignOp::Assign) {
            $currentReg = $this->emitExpr($a->target);
            $rhsReg = $this->emitExpr($a->value);

            $binaryOp = match ($a->op) {
                AssignOp::AddAssign => BinaryOp::Add,
                AssignOp::SubAssign => BinaryOp::Sub,
                AssignOp::MulAssign => BinaryOp::Mul,
                AssignOp::DivAssign => BinaryOp::Div,
                AssignOp::ModAssign => BinaryOp::Mod,
                AssignOp::ShlAssign => BinaryOp::Shl,
                AssignOp::ShrAssign => BinaryOp::Shr,
                AssignOp::AndAssign => BinaryOp::BitAnd,
                AssignOp::OrAssign => BinaryOp::BitOr,
                AssignOp::XorAssign => BinaryOp::BitXor,
                default => BinaryOp::Add,
            };

            $targetType = $this->exprType($a->target)->unqualified();
            $valueType = $this->exprType($a->value)->unqualified();
            $unsignedArith = ($targetType->isInteger() && !$targetType->isSigned())
                || ($valueType->isInteger() && !$valueType->isSigned());
            $is32BitSigned = match (true) {
                $targetType instanceof IntCType,
                $targetType instanceof ShortCType,
                $targetType instanceof SCharCType,
                $targetType instanceof CharCType,
                $targetType instanceof EnumCType => true,
                default => false,
            };
            $isPtrArithCompound = ($targetType->isPointer() || $valueType->isPointer())
                && !($targetType->isPointer() && $valueType->isPointer());
            if ($isPtrArithCompound && ($binaryOp === BinaryOp::Add || $binaryOp === BinaryOp::Sub)) {
                $ptrType = $targetType->isPointer() ? $targetType : $valueType;
                $pointeeSize = 4;
                if ($ptrType instanceof PointerCType) {
                    $t = $ptrType->to->unqualified();
                    $pointeeSize = $t->isPointer() ? 8 : ($t->sizeInBytes() ?? 4);
                }
                if ($valueType->isSigned32Bit()) {
                    $this->emitLine('sxtw ' . $rhsReg->x() . ', ' . $rhsReg->w());
                }
                if ($pointeeSize > 1) {
                    if ($pointeeSize === 2) {
                        $this->emitLine('lsl ' . $rhsReg->x() . ', ' . $rhsReg->x() . ', #1');
                    } elseif ($pointeeSize === 4) {
                        $this->emitLine('lsl ' . $rhsReg->x() . ', ' . $rhsReg->x() . ', #2');
                    } elseif ($pointeeSize === 8) {
                        $this->emitLine('lsl ' . $rhsReg->x() . ', ' . $rhsReg->x() . ', #3');
                    } else {
                        $this->emitLine('mov x16, #' . $pointeeSize);
                        $this->emitLine('mul ' . $rhsReg->x() . ', ' . $rhsReg->x() . ', x16');
                    }
                }
            }

            match ($binaryOp) {
                BinaryOp::Add => $this->emitLine('add ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::Sub => $this->emitLine('sub ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::Mul => $this->emitLine('mul ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::Div => (function () use ($currentReg, $rhsReg, $unsignedArith, $is32BitSigned): void {
                    if (!$unsignedArith && $is32BitSigned) {
                        $this->emitLine('sxtw ' . $currentReg->x() . ', ' . $currentReg->w());
                        $this->emitLine('sxtw ' . $rhsReg->x() . ', ' . $rhsReg->w());
                    }
                    $op = $unsignedArith ? 'udiv' : 'sdiv';
                    $this->emitLine($op . ' ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x());
                })(),
                BinaryOp::Mod => (function () use ($currentReg, $rhsReg, $unsignedArith, $is32BitSigned): void {
                    if (!$unsignedArith && $is32BitSigned) {
                        $this->emitLine('sxtw ' . $currentReg->x() . ', ' . $currentReg->w());
                        $this->emitLine('sxtw ' . $rhsReg->x() . ', ' . $rhsReg->w());
                    }
                    $temp = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                    $op = $unsignedArith ? 'udiv' : 'sdiv';
                    $this->emitLine($op . ' ' . $temp->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x());
                    $this->emitLine('msub ' . $currentReg->x() . ', ' . $temp->x() . ', ' . $rhsReg->x() . ', ' . $currentReg->x());
                    $this->regAlloc->free($temp);
                })(),
                BinaryOp::Shl => $this->emitLine('lsl ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::Shr => (function () use ($currentReg, $rhsReg, $unsignedArith, $is32BitSigned): void {
                    if (!$unsignedArith && $is32BitSigned) {
                        $this->emitLine('sxtw ' . $currentReg->x() . ', ' . $currentReg->w());
                    }
                    $op = $unsignedArith ? 'lsr' : 'asr';
                    $this->emitLine($op . ' ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x());
                })(),
                BinaryOp::BitAnd => $this->emitLine('and ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::BitOr => $this->emitLine('orr ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                BinaryOp::BitXor => $this->emitLine('eor ' . $currentReg->x() . ', ' . $currentReg->x() . ', ' . $rhsReg->x()),
                default => null,
            };
            $this->regAlloc->free($rhsReg);
            $this->storeExprResult($a->target, $currentReg);
            return $currentReg;
        }

        $targetType = $this->exprType($a->target)->unqualified();
        if ($targetType instanceof StructCType || $targetType instanceof UnionCType) {
            $size = $targetType->sizeInBytes();
            if ($size !== null && $size > 0) {
                $srcReg = $this->emitAddr($a->value);
                $dstReg = $this->emitAddr($a->target);
                $this->emitMemCopy($srcReg, $dstReg, $size);
                $this->regAlloc->free($srcReg);
                $this->regAlloc->free($dstReg);
                return Arm64Reg::X9;
            }
        }
        $valueReg = $this->emitExpr($a->value);
        $this->storeExprResult($a->target, $valueReg);
        return $valueReg;
    }

    private function emitMemCopy(Arm64Reg $srcReg, Arm64Reg $dstReg, int $size): void
    {
        $remaining = $size;
        $offset = 0;
        while ($remaining >= 8) {
            $this->emitLine('ldr x16, [' . $srcReg->x() . ', #' . $offset . ']');
            $this->emitLine('str x16, [' . $dstReg->x() . ', #' . $offset . ']');
            $offset += 8;
            $remaining -= 8;
        }
        if ($remaining >= 4) {
            $this->emitLine('ldr w16, [' . $srcReg->x() . ', #' . $offset . ']');
            $this->emitLine('str w16, [' . $dstReg->x() . ', #' . $offset . ']');
            $offset += 4;
            $remaining -= 4;
        }
        if ($remaining >= 2) {
            $this->emitLine('ldrh w16, [' . $srcReg->x() . ', #' . $offset . ']');
            $this->emitLine('strh w16, [' . $dstReg->x() . ', #' . $offset . ']');
            $offset += 2;
            $remaining -= 2;
        }
        if ($remaining >= 1) {
            $this->emitLine('ldrb w16, [' . $srcReg->x() . ', #' . $offset . ']');
            $this->emitLine('strb w16, [' . $dstReg->x() . ', #' . $offset . ']');
        }
    }

    private function storeExprResult(Expr $target, Arm64Reg $reg): void
    {
        if ($target instanceof MemberExpr) {
            $bf = $this->bitfieldInfo($this->exprType($target->base), $target->memberName);
            if ($bf !== null) {
                $this->emitLine('str ' . $reg->x() . ', [sp, #-16]!');
                $addrReg = $this->emitAddr($target);
                $unitReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X17 : Arm64Reg::X16;
                $valReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X16 : Arm64Reg::X17;
                match ($bf['unitSize']) {
                    1 => $this->emitLine('ldrb ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    2 => $this->emitLine('ldrh ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    4 => $this->emitLine('ldr ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    8 => $this->emitLine('ldr ' . $unitReg->x() . ', [' . $addrReg->x() . ']'),
                    default => $this->emitLine('ldr ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                };
                $bitWidth = $bf['bitWidth'];
                $bitOffset = $bf['bitOffset'];
                $bitfieldMask = (int) ((1 << $bitWidth) - 1) << $bitOffset;
                $clearMask = ~$bitfieldMask;
                $valueMask = $bitfieldMask;
                $this->emitLine('ldr ' . $valReg->x() . ', [sp]');
                if ($bitOffset > 0) {
                    $this->emitLine('lsl ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $bitOffset);
                }
                if ($valueMask <= 255) {
                    $this->emitLine('and ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $valueMask);
                } else {
                    $this->emitLine('mov ' . $unitReg->x() . ', #' . ($valueMask & 0xffff));
                    if ($valueMask > 0xffff) {
                        $this->emitLine('movk ' . $unitReg->x() . ', #' . (($valueMask >> 16) & 0xffff) . ', lsl #16');
                    }
                    if ($valueMask > 0xffffff) {
                        $this->emitLine('movk ' . $unitReg->x() . ', #' . (($valueMask >> 32) & 0xffff) . ', lsl #32');
                    }
                    if ($valueMask > 0xffffffffffff) {
                        $this->emitLine('movk ' . $unitReg->x() . ', #' . (($valueMask >> 48) & 0xffff) . ', lsl #48');
                    }
                    $this->emitLine('and ' . $valReg->x() . ', ' . $valReg->x() . ', ' . $unitReg->x());
                }
                if ($clearMask <= 255) {
                    $this->emitLine('and ' . $unitReg->x() . ', ' . $unitReg->x() . ', #' . $clearMask);
                } else {
                    $this->emitLine('mov ' . $valReg->x() . ', #' . ($clearMask & 0xffff));
                    if ($clearMask > 0xffff) {
                        $this->emitLine('movk ' . $valReg->x() . ', #' . (($clearMask >> 16) & 0xffff) . ', lsl #16');
                    }
                    if ($clearMask > 0xffffff) {
                        $this->emitLine('movk ' . $valReg->x() . ', #' . (($clearMask >> 32) & 0xffff) . ', lsl #32');
                    }
                    if ($clearMask > 0xffffffffffff) {
                        $this->emitLine('movk ' . $valReg->x() . ', #' . (($clearMask >> 48) & 0xffff) . ', lsl #48');
                    }
                    $this->emitLine('and ' . $unitReg->x() . ', ' . $unitReg->x() . ', ' . $valReg->x());
                    $this->emitLine('ldr ' . $valReg->x() . ', [sp]');
                    if ($bitOffset > 0) {
                        $this->emitLine('lsl ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $bitOffset);
                    }
                    if ($valueMask <= 255) {
                        $this->emitLine('and ' . $valReg->x() . ', ' . $valReg->x() . ', #' . $valueMask);
                    }
                }
                $this->emitLine('orr ' . $unitReg->x() . ', ' . $unitReg->x() . ', ' . $valReg->x());
                match ($bf['unitSize']) {
                    1 => $this->emitLine('strb ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    2 => $this->emitLine('strh ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    4 => $this->emitLine('str ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                    8 => $this->emitLine('str ' . $unitReg->x() . ', [' . $addrReg->x() . ']'),
                    default => $this->emitLine('str ' . $unitReg->w() . ', [' . $addrReg->x() . ']'),
                };
                $this->emitLine('add sp, sp, #16');
                $this->regAlloc->free($addrReg);
                return;
            }
        }

        $this->emitLine('str ' . $reg->x() . ', [sp, #-16]!');
        $addrReg = $this->emitAddr($target);
        $targetType = $this->exprType($target)->unqualified();
        if ($targetType instanceof StructCType) {
            $size = $targetType->sizeInBytes();
            if ($size !== null && $size > 8) {
                $srcReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X17 : Arm64Reg::X16;
                $tmpReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X16 : Arm64Reg::X17;
                $this->emitLine('ldr ' . $srcReg->x() . ', [sp, #0]');
                $remaining = $size;
                $offset = 0;
                while ($remaining >= 8) {
                    $this->emitLine('ldr ' . $tmpReg->x() . ', [' . $srcReg->x() . ', #' . $offset . ']');
                    $this->emitLine('str ' . $tmpReg->x() . ', [' . $addrReg->x() . ', #' . $offset . ']');
                    $offset += 8;
                    $remaining -= 8;
                }
                if ($remaining >= 4) {
                    $this->emitLine('ldr ' . $tmpReg->w() . ', [' . $srcReg->x() . ', #' . $offset . ']');
                    $this->emitLine('str ' . $tmpReg->w() . ', [' . $addrReg->x() . ', #' . $offset . ']');
                    $offset += 4;
                    $remaining -= 4;
                }
                if ($remaining >= 2) {
                    $this->emitLine('ldrh ' . $tmpReg->w() . ', [' . $srcReg->x() . ', #' . $offset . ']');
                    $this->emitLine('strh ' . $tmpReg->w() . ', [' . $addrReg->x() . ', #' . $offset . ']');
                    $offset += 2;
                    $remaining -= 2;
                }
                if ($remaining >= 1) {
                    $this->emitLine('ldrb ' . $tmpReg->w() . ', [' . $srcReg->x() . ', #' . $offset . ']');
                    $this->emitLine('strb ' . $tmpReg->w() . ', [' . $addrReg->x() . ', #' . $offset . ']');
                }
            } else {
                $valReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X17 : Arm64Reg::X16;
                $this->emitLine('ldr ' . $valReg->x() . ', [sp, #0]');
                $this->emitStoreToAddr($addrReg, $valReg, $targetType);
            }
        } else {
            $valReg = ($addrReg === Arm64Reg::X16) ? Arm64Reg::X17 : Arm64Reg::X16;
            $this->emitLine('ldr ' . $valReg->x() . ', [sp, #0]');
            $this->emitStoreToAddr($addrReg, $valReg, $targetType);
        }
        $this->emitLine('add sp, sp, #16');
        $this->regAlloc->free($addrReg);
    }

    private function emitCallExpr(CallExpr $c): Arm64Reg
    {
        if ($c->function instanceof Identifier && $c->function->name === '__builtin_va_start') {
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            if ($this->vaSaveAreaOffset >= -256 && $this->vaSaveAreaOffset <= 255) {
                $this->emitLine('add ' . $reg->x() . ', x29, #' . $this->vaSaveAreaOffset);
            } else {
                $this->emitLoadImm('x16', $this->vaSaveAreaOffset);
                $this->emitLine('add ' . $reg->x() . ', x29, x16');
            }
            return $reg;
        }

        $funcName = '';
        if ($c->function instanceof Identifier) {
            $id = $c->function;
            if (!isset($this->localVarOffsets[$id->name]) && !isset($this->globalLabels[$id->name])) {
                $funcName = $id->name;
            }
        }

        $variadicNamedParams = [
            'printf' => 1,
            'fprintf' => 2,
            'sprintf' => 2,
            'snprintf' => 3,
            'scanf' => 1,
            'fscanf' => 2,
            'sscanf' => 2,
            'vprintf' => 1,
            'vfprintf' => 2,
            'vsprintf' => 2,
            'vsnprintf' => 3,
        ];
        $namedParamCount = $variadicNamedParams[$funcName] ?? null;

        if ($namedParamCount !== null && count($c->arguments) > $namedParamCount) {
            $namedCount = $namedParamCount;
            /** @var list<Arm64Reg> */
            $namedArgRegs = [];
            for ($i = 0; $i < min($namedCount, count($c->arguments)); $i++) {
                $argReg = $this->emitExpr($c->arguments[$i]);
                $this->emitLine('str ' . $argReg->x() . ', [sp, #-16]!');
                $namedArgRegs[] = $argReg;
                $this->regAlloc->free($argReg);
            }

            /** @var list<Arm64Reg> */
            $variadicArgRegs = [];
            for ($i = $namedCount; $i < count($c->arguments); $i++) {
                $argReg = $this->emitExpr($c->arguments[$i]);
                $this->emitLine('str ' . $argReg->x() . ', [sp, #-16]!');
                $variadicArgRegs[] = $argReg;
                $this->regAlloc->free($argReg);
            }

            $inUse = array_values(array_filter(SCRATCH_REGS, fn (Arm64Reg $reg) => !in_array($reg, $this->regAlloc->available, true)));
            $numVariadicArgs = count($c->arguments) - $namedCount;
            $variadicSize = ($numVariadicArgs * 8 + 15) & ~15;
            $spillCount = count($inUse) + (count($inUse) % 2);
            $spillSize = $spillCount * 8;
            $namedTempSize = $namedCount * 16;
            $variadicTempSize = $numVariadicArgs * 16;
            $totalSize = ($variadicSize + $spillSize + $namedTempSize + $variadicTempSize + 15) & ~15;

            if ($totalSize > 0) {
                $this->emitLine('sub sp, sp, #' . $totalSize);
            }
            for ($i = 0; $i < $numVariadicArgs; $i++) {
                $tempOffset = $totalSize + ($numVariadicArgs - 1 - $i) * 16;
                $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                $this->emitLine('str x9, [sp, #' . ($i * 8) . ']');
            }
            foreach ($inUse as $idx => $reg) {
                $this->emitLine('str ' . $reg->x() . ', [sp, #' . ($variadicSize + $idx * 8) . ']');
            }
            for ($i = 0; $i < min($namedCount, count($c->arguments)); $i++) {
                $tempOffset = $totalSize + $variadicTempSize + ($namedCount - 1 - $i) * 16;
                $this->emitLine('ldr ' . ARG_REGS[$i]->x() . ', [sp, #' . $tempOffset . ']');
            }

            if ($funcName !== '') {
                $this->emitLine('bl _' . $funcName);
            }

            foreach ($inUse as $idx => $reg) {
                $this->emitLine('ldr ' . $reg->x() . ', [sp, #' . ($variadicSize + $idx * 8) . ']');
            }
            if ($totalSize > 0) {
                $this->emitLine('add sp, sp, #' . $totalSize);
            }
            for ($i = 0; $i < min($namedCount, count($c->arguments)); $i++) {
                $this->emitLine('add sp, sp, #16');
            }
        } else {
            $isInternalVariadic = isset($this->variadicFunctions[$funcName]) && isset($this->definedFunctions[$funcName]);
            $namedParamCountInternal = $isInternalVariadic ? ($this->functionParamCounts[$funcName] ?? 0) : 0;

            $funcPtrReg = null;
            if ($funcName === '') {
                $fpReg = $this->emitExpr($c->function);
                $this->emitLine('mov x16, ' . $fpReg->x());
                $this->regAlloc->free($fpReg);
                $this->emitLine('str x16, [sp, #-16]!');
                $funcPtrReg = Arm64Reg::X16;
            }

            /** @var list<Arm64Reg> */
            $evaluatedArgs = [];
            /** @var array<int, true> */
            $wideArgs = [];
            foreach ($c->arguments as $i => $arg) {
                $argType = $this->exprType($arg)->unqualified();
                $argSize = $argType->sizeInBytes() ?? 8;
                if (($argType instanceof StructCType || $argType instanceof UnionCType) && $argSize > 16) {
                    // Pass by invisible reference: copy to a temp and pass its address.
                    $srcReg = $this->emitAddr($arg);
                    $this->ensureLocalSpace($argSize);
                    $tempOff = -$this->localOffset;
                    $tmpAddr = $this->regAlloc->alloc() ?? Arm64Reg::X10;
                    if ($tempOff >= -4095 && $tempOff <= 4095) {
                        $this->emitLine('add ' . $tmpAddr->x() . ', x29, #' . $tempOff);
                    } else {
                        $this->emitLoadImm('x17', $tempOff);
                        $this->emitLine('add ' . $tmpAddr->x() . ', x29, x17');
                    }
                    $this->emitMemCopy($srcReg, $tmpAddr, $argSize);
                    $this->regAlloc->free($srcReg);
                    $this->emitLine('str ' . $tmpAddr->x() . ', [sp, #-16]!');
                    $evaluatedArgs[] = $tmpAddr;
                    $this->regAlloc->free($tmpAddr);
                } elseif (($argType instanceof StructCType || $argType instanceof UnionCType) && $argSize > 0 && $argSize <= 16) {
                    $addrReg = $this->emitAddr($arg);
                    if ($argSize > 8) {
                        $this->emitLine('str ' . $addrReg->x() . ', [sp, #-16]!');
                        $this->emitLine('str ' . $addrReg->x() . ', [sp, #-16]!');
                        $this->emitLine('ldr x16, [' . $addrReg->x() . ']');
                        $this->emitLine('str x16, [sp, #16]');
                        $this->emitLine('ldr x16, [' . $addrReg->x() . ', #8]');
                        $this->emitLine('str x16, [sp, #0]');
                        $wideArgs[$i] = true;
                    } else {
                        $this->emitLine('ldr ' . $addrReg->x() . ', [' . $addrReg->x() . ']');
                        $this->emitLine('str ' . $addrReg->x() . ', [sp, #-16]!');
                    }
                    $evaluatedArgs[] = $addrReg;
                    $this->regAlloc->free($addrReg);
                } else {
                    $argReg = $this->emitExpr($arg);
                    $this->emitLine('str ' . $argReg->x() . ', [sp, #-16]!');
                    $evaluatedArgs[] = $argReg;
                    $this->regAlloc->free($argReg);
                }
            }

            $totalRegSlots = 0;
            for ($i = 0; $i < count($evaluatedArgs); $i++) {
                $totalRegSlots += isset($wideArgs[$i]) ? 2 : 1;
            }
            if ($isInternalVariadic && count($evaluatedArgs) > $namedParamCountInternal) {
                $numStackArgs = count($evaluatedArgs) - $namedParamCountInternal;
            } else {
                $numStackArgs = max($totalRegSlots - 8, 0);
            }
            $stackArgSize = 0;
            if ($numStackArgs > 0) {
                $stackArgSize = ($numStackArgs * 8 + 15) & ~15;
            }

            $namedCount = min(count($evaluatedArgs), 8);
            if ($isInternalVariadic) {
                $namedCount = min($namedParamCountInternal, count($evaluatedArgs));
            }

            $inUse = array_values(array_filter(SCRATCH_REGS, fn (Arm64Reg $reg) => !in_array($reg, $this->regAlloc->available, true)));
            $paddedCount = count($inUse) + (count($inUse) % 2);
            $scratchSaveSize = $paddedCount * 8;

            if ($stackArgSize > 0) {
                $this->emitLine('sub sp, sp, #' . $stackArgSize);
            }
            if ($scratchSaveSize > 0) {
                $this->emitLine('sub sp, sp, #' . $scratchSaveSize);
                foreach ($inUse as $idx => $reg) {
                    $this->emitLine('str ' . $reg->x() . ', [sp, #' . ($idx * 8) . ']');
                }
            }

            $tempBase = $scratchSaveSize + $stackArgSize;
            $numArgs = count($evaluatedArgs);
            /** @var list<int> */
            $argSlotOffsets = [];
            $cumulative = 0;
            for ($i = $numArgs - 1; $i >= 0; $i--) {
                $slotSize = isset($wideArgs[$i]) ? 32 : 16;
                array_unshift($argSlotOffsets, $cumulative);
                $cumulative += $slotSize;
            }

            $regIdx = 0;
            for ($i = 0; $i < $numArgs; $i++) {
                $tempOffset = $tempBase + $argSlotOffsets[$i];
                $isWide = isset($wideArgs[$i]);
                $regsNeeded = $isWide ? 2 : 1;
                $isVariadicArg = $isInternalVariadic && $i >= $namedParamCountInternal;
                if ($isVariadicArg) {
                    $stackOffset = ($i - $namedParamCountInternal) * 8;
                    if ($isWide) {
                        $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                        $this->emitLine('str x9, [sp, #' . $stackOffset . ']');
                        $this->emitLine('ldr x9, [sp, #' . ($tempOffset + 16) . ']');
                        $this->emitLine('str x9, [sp, #' . ($stackOffset + 8) . ']');
                    } else {
                        $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                        $this->emitLine('str x9, [sp, #' . $stackOffset . ']');
                    }
                    $this->regAlloc->free($evaluatedArgs[$i]);
                    $regIdx += $regsNeeded;
                    continue;
                }

                if ($regIdx < 8) {
                    if ($isWide) {
                        if ($regIdx + 1 < 8) {
                            $this->emitLine('ldr ' . ARG_REGS[$regIdx + 1]->x() . ', [sp, #' . $tempOffset . ']');
                            $this->emitLine('ldr ' . ARG_REGS[$regIdx]->x() . ', [sp, #' . ($tempOffset + 16) . ']');
                        } else {
                            $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                            $this->emitLine('str x9, [sp, #0]');
                            $this->emitLine('ldr ' . ARG_REGS[$regIdx]->x() . ', [sp, #' . ($tempOffset + 16) . ']');
                        }
                    } else {
                        $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                        $this->emitLine('mov ' . ARG_REGS[$regIdx]->x() . ', x9');
                    }
                    $this->regAlloc->free($evaluatedArgs[$i]);
                } else {
                    $stackOffset = ($regIdx - 8) * 8;
                    if ($isWide) {
                        $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                        $this->emitLine('str x9, [sp, #' . $stackOffset . ']');
                        $this->emitLine('ldr x9, [sp, #' . ($tempOffset + 16) . ']');
                        $this->emitLine('str x9, [sp, #' . ($stackOffset + 8) . ']');
                    } else {
                        $this->emitLine('ldr x9, [sp, #' . $tempOffset . ']');
                        $this->emitLine('str x9, [sp, #' . $stackOffset . ']');
                    }
                    $this->regAlloc->free($evaluatedArgs[$i]);
                }
                $regIdx += $regsNeeded;
            }

            $totalRegArgs = $isInternalVariadic ? min($namedParamCountInternal, 8) : min($regIdx, 8);
            for ($i = 0; $i < $totalRegArgs; $i++) {
                $this->regAlloc->alloc();
            }

            $variadicStackArgSize = 0;
            $tempStackSize = $cumulative;

            $sretOff = $this->forcedSretOffset;
            if ($sretOff === null && $funcName !== '' && isset($this->functionReturnTypes[$funcName])) {
                $rt = $this->functionReturnTypes[$funcName]->unqualified();
                $rsz = $rt->sizeInBytes() ?? 0;
                if (($rt instanceof StructCType || $rt instanceof UnionCType) && $rsz > 16) {
                    $this->ensureLocalSpace($rsz);
                    $sretOff = -$this->localOffset;
                }
            }
            if ($sretOff !== null) {
                if ($sretOff >= -4095 && $sretOff <= 4095) {
                    $this->emitLine('add x8, x29, #' . $sretOff);
                } else {
                    $this->emitLoadImm('x8', $sretOff);
                    $this->emitLine('add x8, x29, x8');
                }
            }

            if ($funcPtrReg !== null) {
                $fpOffset = $variadicStackArgSize + $stackArgSize + $scratchSaveSize + $tempStackSize;
                $this->emitLine('ldr ' . $funcPtrReg->x() . ', [sp, #' . $fpOffset . ']');
                $this->emitLine('blr ' . $funcPtrReg->x());
            } elseif ($funcName !== '') {
                $this->emitLine('bl _' . $funcName);
            }

            if ($variadicStackArgSize > 0) {
                $this->emitLine('add sp, sp, #' . $variadicStackArgSize);
            }
            if ($stackArgSize > 0) {
                $this->emitLine('add sp, sp, #' . $stackArgSize);
            }
            if ($scratchSaveSize > 0) {
                foreach ($inUse as $idx => $reg) {
                    $this->emitLine('ldr ' . $reg->x() . ', [sp, #' . ($idx * 8) . ']');
                }
                $this->emitLine('add sp, sp, #' . $scratchSaveSize);
            }
            if ($tempStackSize > 0) {
                $this->emitLine('add sp, sp, #' . $tempStackSize);
            }
            if ($funcPtrReg !== null) {
                $this->emitLine('add sp, sp, #16');
            }
        }

        $resultReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
        $retType = null;
        if ($funcName !== '' && isset($this->functionReturnTypes[$funcName])) {
            $retType = $this->functionReturnTypes[$funcName]->unqualified();
        }
        $retSize = $retType?->sizeInBytes() ?? 0;
        if (($retType instanceof StructCType || $retType instanceof UnionCType) && $retSize > 0 && $retSize <= 16) {
            // Keep ABI return registers x0(/x1) intact for the caller to spill.
            if ($resultReg !== Arm64Reg::X0) {
                $this->regAlloc->free($resultReg);
            }

            return Arm64Reg::X0;
        }
        if ($resultReg !== Arm64Reg::X0) {
            $this->emitLine('mov ' . $resultReg->x() . ', x0');
        }

        return $resultReg;
    }

    private function exprType(Expr $expr): CType
    {
        if ($expr instanceof IntegerLiteral) {
            return $expr->type;
        }
        if ($expr instanceof CharLiteral) {
            return IntCType::$instance;
        }
        if ($expr instanceof FloatLiteral) {
            return $expr->type;
        }
        if ($expr instanceof StringLiteral) {
            return $expr->type;
        }
        if ($expr instanceof BoolLiteral) {
            return BoolCType::$instance;
        }
        if ($expr instanceof Identifier) {
            if (isset($this->localVarTypes[$expr->name])) {
                return $this->localVarTypes[$expr->name];
            }
            if (isset($this->globalVarTypes[$expr->name])) {
                return $this->globalVarTypes[$expr->name];
            }

            return IntCType::$instance;
        }
        if ($expr instanceof BinaryExpr) {
            $lt = $this->exprType($expr->left);
            $rt = $this->exprType($expr->right);
            $lu = $lt->unqualified();
            $ru = $rt->unqualified();
            if ($expr->op === BinaryOp::Add || $expr->op === BinaryOp::Sub) {
                if (($lt->isPointer() || $lt->isArray()) && $rt->isInteger()) {
                    if ($lu instanceof ArrayCType || $lu instanceof IncompleteArrayCType) {
                        return new PointerCType($lu->of);
                    }

                    return $lt;
                }
                if ($lt->isInteger() && ($rt->isPointer() || $rt->isArray())) {
                    if ($ru instanceof ArrayCType || $ru instanceof IncompleteArrayCType) {
                        return new PointerCType($ru->of);
                    }

                    return $rt;
                }
            }
            if ($lu->isArithmetic() && $ru->isArithmetic()) {
                $rank = function (CType $t): int {
                    if ($t instanceof LongLongCType || $t instanceof ULongLongCType) {
                        return 4;
                    }
                    if ($t instanceof LongCType || $t instanceof ULongCType) {
                        return 3;
                    }
                    if ($t instanceof IntCType || $t instanceof UIntCType) {
                        return 2;
                    }
                    if ($t instanceof ShortCType || $t instanceof UShortCType) {
                        return 1;
                    }

                    return 0;
                };
                if ($rank($lu) >= 4 || $rank($ru) >= 4) {
                    return $rank($lu) >= $rank($ru) ? $lu : $ru;
                }
                if ($rank($lu) >= 3 || $rank($ru) >= 3) {
                    return $rank($lu) >= $rank($ru) ? $lu : $ru;
                }
            }

            return $lu->isArithmetic() ? $lt : $rt;
        }
        if ($expr instanceof UnaryExpr) {
            if ($expr->op === UnaryOp::Dereference) {
                $t = $this->exprType($expr->operand);
                $tu = $t->unqualified();
                if ($tu instanceof PointerCType) {
                    return $tu->to;
                }
                if ($tu instanceof ArrayCType) {
                    return $tu->of;
                }

                return IntCType::$instance;
            }
            if ($expr->op === UnaryOp::AddressOf) {
                return new PointerCType($this->exprType($expr->operand));
            }

            return $this->exprType($expr->operand);
        }
        if ($expr instanceof AssignExpr) {
            return $this->exprType($expr->target);
        }
        if ($expr instanceof CallExpr) {
            if ($expr->function instanceof Identifier) {
                $id = $expr->function;
                if (isset($this->functionReturnTypes[$id->name])) {
                    return $this->functionReturnTypes[$id->name];
                }
                if (isset($this->globalVarTypes[$id->name])) {
                    $t = $this->globalVarTypes[$id->name]->unqualified();
                    if ($t instanceof FunctionCType) {
                        return $t->returnType;
                    }
                }
            }

            return IntCType::$instance;
        }
        if ($expr instanceof SubscriptExpr) {
            $bt = $this->exprType($expr->base);
            $tu = $bt->unqualified();
            if ($tu instanceof PointerCType) {
                return $tu->to;
            }
            if ($tu instanceof ArrayCType) {
                return $tu->of;
            }

            return IntCType::$instance;
        }
        if ($expr instanceof MemberExpr) {
            $m = $expr;
            $bt = $this->exprType($m->base);
            $recordType = $bt->unqualified();
            if ($m->isArrow && $bt->unqualified() instanceof PointerCType) {
                /** @var PointerCType $ptr */
                $ptr = $bt->unqualified();
                $recordType = $ptr->to;
            }
            $ru = $recordType->unqualified();
            if ($ru instanceof StructCType && $ru->record->fields === [] && isset($this->knownRecords[$ru->record->name])) {
                $recordType = new StructCType($this->knownRecords[$ru->record->name]);
            }
            if ($ru instanceof UnionCType && $ru->record->fields === [] && isset($this->knownRecords[$ru->record->name])) {
                $recordType = new UnionCType($this->knownRecords[$ru->record->name]);
            }
            $rq = $recordType->unqualified();
            $found = $this->findMemberField($rq, $m->memberName);
            if ($found !== null) {
                return $found['type'];
            }

            return IntCType::$instance;
        }
        if ($expr instanceof CastExpr) {
            return $expr->type;
        }
        if ($expr instanceof ConditionalExpr) {
            return IntCType::$instance;
        }
        if ($expr instanceof SizeofExpr) {
            return ULongCType::$instance;
        }
        if ($expr instanceof CompoundLiteralExpr) {
            return $expr->type;
        }
        if ($expr instanceof InitListExpr) {
            return IntCType::$instance;
        }

        return IntCType::$instance;
    }

    private function collectRecords(CType $type): void
    {
        $t = $type->unqualified();
        if ($t instanceof PointerCType) {
            $t = $t->to->unqualified();
        }
        if ($t instanceof StructCType && $t->record->size !== null) {
            $this->knownRecords[$t->record->name] = $t->record;
        }
        if ($t instanceof UnionCType && $t->record->size !== null) {
            $this->knownRecords[$t->record->name] = $t->record;
        }
    }

    private function memberOffset(CType $baseType, string $memberName): int
    {
        return $this->findMemberField($baseType, $memberName)['offset'] ?? 0;
    }

    /**
     * Resolve a named member, including C11 anonymous struct/union members.
     *
     * @return array{offset: int, type: CType}|null
     */
    private function findMemberField(CType $baseType, string $memberName): ?array
    {
        $t = $baseType->unqualified();
        if ($t instanceof PointerCType) {
            $t = $t->to->unqualified();
        }
        if ($t instanceof StructCType) {
            if ($t->record->fields === [] && isset($this->knownRecords[$t->record->name])) {
                $t = new StructCType($this->knownRecords[$t->record->name]);
            }
        }
        if ($t instanceof UnionCType) {
            if ($t->record->fields === [] && isset($this->knownRecords[$t->record->name])) {
                $t = new UnionCType($this->knownRecords[$t->record->name]);
            }
        }
        if (!($t instanceof StructCType || $t instanceof UnionCType)) {
            return null;
        }
        foreach ($t->record->fields as $field) {
            if ($field->name === $memberName) {
                return ['offset' => $field->offset, 'type' => $field->type];
            }
            if ($field->name === null || $field->name === '') {
                $ft = $field->type->unqualified();
                if ($ft instanceof StructCType || $ft instanceof UnionCType) {
                    $inner = $this->findMemberField($ft, $memberName);
                    if ($inner !== null) {
                        return [
                            'offset' => $field->offset + $inner['offset'],
                            'type' => $inner['type'],
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array{bitWidth: int, bitOffset: int, unitSize: int}|null
     */
    private function bitfieldInfo(CType $baseType, string $memberName): ?array
    {
        $t = $baseType->unqualified();
        if ($t instanceof PointerCType) {
            $t = $t->to->unqualified();
        }
        if ($t instanceof StructCType) {
            if ($t->record->fields === [] && isset($this->knownRecords[$t->record->name])) {
                $t = new StructCType($this->knownRecords[$t->record->name]);
            }
        }
        if ($t instanceof StructCType) {
            foreach ($t->record->fields as $field) {
                if ($field->name === $memberName) {
                    if ($field->bitWidth === null) {
                        return null;
                    }
                    $unitSize = $field->type->unqualified()->sizeInBytes() ?? 4;

                    return ['bitWidth' => $field->bitWidth, 'bitOffset' => $field->bitOffset, 'unitSize' => $unitSize];
                }
            }
        }

        return null;
    }

    private function emitAddr(Expr $expr): Arm64Reg
    {
        if ($expr instanceof Identifier) {
            $id = $expr;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            if (isset($this->localVarOffsets[$id->name])) {
                $offset = $this->localVarOffsets[$id->name];
                if ($offset >= -4095 && $offset <= 4095) {
                    $this->emitLine('add ' . $reg->x() . ', x29, #' . $offset);
                } else {
                    $this->emitLoadImm('x17', $offset);
                    $this->emitLine('add ' . $reg->x() . ', x29, x17');
                }
            } elseif (isset($this->staticLocalGlobals[$id->name])) {
                $globalName = $this->staticLocalGlobals[$id->name];
                $this->emitLine('adrp ' . $reg->x() . ', ' . $globalName . '@PAGE');
                $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', ' . $globalName . '@PAGEOFF');
            } elseif (isset($this->globalLabels[$id->name])) {
                if (isset($this->externGlobals[$id->name])) {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@GOTPAGE');
                    $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ', _' . $id->name . '@GOTPAGEOFF]');
                } else {
                    $this->emitLine('adrp ' . $reg->x() . ', _' . $id->name . '@PAGE');
                    $this->emitLine('add ' . $reg->x() . ', ' . $reg->x() . ', _' . $id->name . '@PAGEOFF');
                }
            }
            return $reg;
        }

        if ($expr instanceof SubscriptExpr) {
            $s = $expr;
            $baseTypeFull = $this->exprType($s->base)->unqualified();
            if ($baseTypeFull->isArray()) {
                $baseReg = $this->emitAddr($s->base);
            } else {
                $baseReg = $this->emitExpr($s->base);
            }
            $indexReg = $this->emitExpr($s->index);
            $indexType = $this->exprType($s->index)->unqualified();
            if ($indexType->isSigned32Bit()) {
                $this->emitLine('sxtw ' . $indexReg->x() . ', ' . $indexReg->w());
            }
            $elemType = IntCType::$instance;
            if ($baseTypeFull instanceof ArrayCType) {
                $elemType = $baseTypeFull->of;
            } elseif ($baseTypeFull instanceof IncompleteArrayCType) {
                $elemType = $baseTypeFull->of;
            } elseif ($baseTypeFull instanceof PointerCType) {
                $elemType = $baseTypeFull->to;
            }
            $elemSize = $elemType->unqualified()->isPointer() ? 8 : ($elemType->sizeInBytes() ?? 4);
            if ($elemSize === 1) {
                $this->emitLine('add ' . $baseReg->x() . ', ' . $baseReg->x() . ', ' . $indexReg->x());
            } elseif ($elemSize === 2) {
                $this->emitLine('add ' . $baseReg->x() . ', ' . $baseReg->x() . ', ' . $indexReg->x() . ', lsl #1');
            } elseif ($elemSize === 4) {
                $this->emitLine('add ' . $baseReg->x() . ', ' . $baseReg->x() . ', ' . $indexReg->x() . ', lsl #2');
            } elseif ($elemSize === 8) {
                $this->emitLine('add ' . $baseReg->x() . ', ' . $baseReg->x() . ', ' . $indexReg->x() . ', lsl #3');
            } else {
                $this->emitLine('mov x16, #' . $elemSize);
                $this->emitLine('madd ' . $baseReg->x() . ', ' . $indexReg->x() . ', x16, ' . $baseReg->x());
            }
            $this->regAlloc->free($indexReg);
            return $baseReg;
        }

        if ($expr instanceof MemberExpr) {
            $m = $expr;
            if ($m->isArrow) {
                $baseReg = $this->emitExpr($m->base);
            } else {
                $baseReg = $this->emitAddr($m->base);
            }
            $offset = $this->memberOffset($this->exprType($m->base), $m->memberName);
            if ($offset !== 0) {
                $this->emitLine('add ' . $baseReg->x() . ', ' . $baseReg->x() . ', #' . $offset);
            }
            return $baseReg;
        }

        if ($expr instanceof UnaryExpr && $expr->op === UnaryOp::Dereference) {
            $u = $expr;
            $operandType = $this->exprType($u->operand)->unqualified();
            if ($operandType->isArray()) {
                return $this->emitAddr($u->operand);
            }
            return $this->emitExpr($u->operand);
        }

        if ($expr instanceof CompoundLiteralExpr) {
            $litType = $expr->type->unqualified();
            $size = $litType->sizeInBytes() ?? 8;
            if ($litType instanceof IncompleteArrayCType && $expr->initList instanceof InitListExpr) {
                $elemSize = $litType->of->sizeInBytes() ?? 4;
                $size = max($elemSize * count($expr->initList->values), $elemSize);
            }
            $this->ensureLocalSpace(max($size, 8));
            $offset = -$this->localOffset;
            $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            if ($offset >= -4095 && $offset <= 4095) {
                $this->emitLine('add ' . $reg->x() . ', x29, #' . $offset);
            } else {
                $this->emitLoadImm('x17', $offset);
                $this->emitLine('add ' . $reg->x() . ', x29, x17');
            }
            $this->emitLocalInit($reg, $expr->initList, $expr->type);

            return $reg;
        }

        if ($expr instanceof CallExpr) {
            $retType = $this->exprType($expr)->unqualified();
            $size = $retType->sizeInBytes() ?? 0;
            if (($retType instanceof StructCType || $retType instanceof UnionCType) && $size > 0 && $size <= 16) {
                // Spill register-returned aggregate so '.' / '&' can take its address.
                $this->ensureLocalSpace(max($size, 8));
                $offset = -$this->localOffset;
                $resultReg = $this->emitExpr($expr);
                if ($offset >= -256 && $offset <= 255) {
                    $this->emitLine('str ' . $resultReg->x() . ', [x29, #' . $offset . ']');
                    if ($size > 8) {
                        $this->emitLine('str x1, [x29, #' . ($offset + 8) . ']');
                    }
                } else {
                    $this->emitLoadImm('x17', $offset);
                    $this->emitLine('str ' . $resultReg->x() . ', [x29, x17]');
                    if ($size > 8) {
                        $this->emitLine('add x17, x17, #8');
                        $this->emitLine('str x1, [x29, x17]');
                    }
                }
                if ($resultReg !== Arm64Reg::X0) {
                    $this->regAlloc->free($resultReg);
                }
                $reg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                if ($offset >= -4095 && $offset <= 4095) {
                    $this->emitLine('add ' . $reg->x() . ', x29, #' . $offset);
                } else {
                    $this->emitLoadImm('x17', $offset);
                    $this->emitLine('add ' . $reg->x() . ', x29, x17');
                }

                return $reg;
            }
        }

        return $this->emitExpr($expr);
    }

    private function emitSubscriptExpr(SubscriptExpr $s): Arm64Reg
    {
        $addrReg = $this->emitAddr($s);
        $bt = $this->exprType($s->base)->unqualified();
        $elemType = IntCType::$instance;
        if ($bt instanceof ArrayCType) {
            $elemType = $bt->of;
        } elseif ($bt instanceof IncompleteArrayCType) {
            $elemType = $bt->of;
        } elseif ($bt instanceof PointerCType) {
            $elemType = $bt->to;
        }
        if ($elemType->unqualified() instanceof ArrayCType) {
            return $addrReg;
        }
        $this->emitLoad($addrReg, $elemType);
        return $addrReg;
    }

    private function emitMemberExpr(MemberExpr $m): Arm64Reg
    {
        $bf = $this->bitfieldInfo($this->exprType($m->base), $m->memberName);
        if ($bf !== null) {
            $addrReg = $this->emitAddr($m);
            match ($bf['unitSize']) {
                1 => $this->emitLine('ldrb ' . $addrReg->w() . ', [' . $addrReg->x() . ']'),
                2 => $this->emitLine('ldrh ' . $addrReg->w() . ', [' . $addrReg->x() . ']'),
                4 => $this->emitLine('ldr ' . $addrReg->w() . ', [' . $addrReg->x() . ']'),
                8 => $this->emitLine('ldr ' . $addrReg->x() . ', [' . $addrReg->x() . ']'),
                default => $this->emitLine('ldr ' . $addrReg->w() . ', [' . $addrReg->x() . ']'),
            };
            if ($bf['bitOffset'] > 0) {
                $this->emitLine('lsr ' . $addrReg->x() . ', ' . $addrReg->x() . ', #' . $bf['bitOffset']);
            }
            if ($bf['bitWidth'] < 64) {
                $mask = (1 << $bf['bitWidth']) - 1;
                if ($mask <= 255) {
                    $this->emitLine('and ' . $addrReg->x() . ', ' . $addrReg->x() . ', #' . $mask);
                } elseif ($mask <= 65535) {
                    $this->emitLine('mov x16, #' . $mask);
                    $this->emitLine('and ' . $addrReg->x() . ', ' . $addrReg->x() . ', x16');
                } else {
                    $this->emitLine('mov x16, #' . ($mask & 0xffff));
                    if ($mask > 0xffff) {
                        $this->emitLine('movk x16, #' . (($mask >> 16) & 0xffff) . ', lsl #16');
                    }
                    if ($mask > 0xffffff) {
                        $this->emitLine('movk x16, #' . (($mask >> 32) & 0xffff) . ', lsl #32');
                    }
                    $this->emitLine('and ' . $addrReg->x() . ', ' . $addrReg->x() . ', x16');
                }
            }
            return $addrReg;
        }
        $addrReg = $this->emitAddr($m);
        $mt = $this->exprType($m);
        if ($mt->unqualified() instanceof ArrayCType) {
            return $addrReg;
        }
        $this->emitLoad($addrReg, $mt);
        return $addrReg;
    }

    private function emitLoad(Arm64Reg $reg, CType $type): void
    {
        $t = $type->unqualified();
        if ($t instanceof BoolCType || $t instanceof UCharCType) {
            $this->emitLine('ldrb ' . $reg->w() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof SCharCType || $t instanceof CharCType) {
            // Sign-extend into X so values survive promotion to long.
            $this->emitLine('ldrsb ' . $reg->x() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof UShortCType) {
            $this->emitLine('ldrh ' . $reg->w() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof ShortCType) {
            $this->emitLine('ldrsh ' . $reg->x() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof UIntCType) {
            $this->emitLine('ldr ' . $reg->w() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof IntCType || $t instanceof EnumCType) {
            $this->emitLine('ldrsw ' . $reg->x() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof FloatCType) {
            $this->emitLine('ldr s' . $reg->regNum() . ', [' . $reg->x() . ']');
        } elseif ($t instanceof DoubleCType || $t instanceof LongDoubleCType) {
            $this->emitLine('ldr d' . $reg->regNum() . ', [' . $reg->x() . ']');
        } else {
            $this->emitLine('ldr ' . $reg->x() . ', [' . $reg->x() . ']');
        }
    }

    private function emitConditionalExpr(ConditionalExpr $c): Arm64Reg
    {
        $condReg = $this->emitExpr($c->condition);
        $elseLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $resultReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
        $this->emitLine('cbz ' . $condReg->x() . ', ' . $elseLabel);
        $trueReg = $this->emitExpr($c->trueExpr);
        $this->emitLine('mov ' . $resultReg->x() . ', ' . $trueReg->x());
        $this->regAlloc->free($trueReg);
        $this->emitLine('b ' . $endLabel);
        $this->emitLine($elseLabel . ':');
        $falseReg = $this->emitExpr($c->falseExpr);
        $this->emitLine('mov ' . $resultReg->x() . ', ' . $falseReg->x());
        $this->regAlloc->free($falseReg);
        $this->emitLine($endLabel . ':');
        return $resultReg;
    }

    private function storeLocal(string $name, Arm64Reg $reg, CType $type): void
    {
        if (!isset($this->localVarOffsets[$name])) {
            return;
        }
        $offset = $this->localVarOffsets[$name];
        $t = $type->unqualified();
        if ($t instanceof BoolCType) {
            $this->emitLine('cmp ' . $reg->x() . ', #0');
            $this->emitLine('cset ' . $reg->w() . ', ne');
            $this->emitStoreByteFP($reg->w(), $offset);
        } elseif ($t instanceof CharCType || $t instanceof SCharCType || $t instanceof UCharCType) {
            $this->emitStoreByteFP($reg->w(), $offset);
        } elseif ($t instanceof ShortCType || $t instanceof UShortCType) {
            $this->emitStoreHalfFP($reg->w(), $offset);
        } elseif ($t instanceof IntCType || $t instanceof UIntCType || $t instanceof EnumCType) {
            $this->emitStoreWordFP($reg->w(), $offset);
        } elseif ($t instanceof StructCType || $t instanceof UnionCType) {
            // Small aggregates returned in x0(/x1) per Apple ARM64 ABI.
            $size = $t->sizeInBytes() ?? 8;
            $this->emitStoreFP($reg->x(), $offset);
            if ($size > 8 && $size <= 16 && $reg === Arm64Reg::X0) {
                $this->emitStoreFP('x1', $offset + 8);
            }
        } else {
            $this->emitStoreFP($reg->x(), $offset);
        }
    }

    private function emitStoreByteFP(string $reg, int $offset): void
    {
        if ($offset >= -256 && $offset <= 255) {
            $this->emitLine('strb ' . $reg . ', [x29, #' . $offset . ']');
        } else {
            $this->emitLoadImm('x16', $offset);
            $this->emitLine('strb ' . $reg . ', [x29, x16]');
        }
    }

    private function emitStoreHalfFP(string $reg, int $offset): void
    {
        if ($offset >= -256 && $offset <= 255) {
            $this->emitLine('strh ' . $reg . ', [x29, #' . $offset . ']');
        } else {
            $this->emitLoadImm('x16', $offset);
            $this->emitLine('strh ' . $reg . ', [x29, x16]');
        }
    }

    private function emitStoreWordFP(string $reg, int $offset): void
    {
        if ($offset >= -256 && $offset <= 255) {
            $this->emitLine('str ' . $reg . ', [x29, #' . $offset . ']');
        } else {
            $this->emitLoadImm('x16', $offset);
            $this->emitLine('str ' . $reg . ', [x29, x16]');
        }
    }

    private function emitLine(string $s): void
    {
        $this->lines[] = $s;
    }

    private function emitStoreFP(string $reg, int $offset): void
    {
        if ($offset >= -256 && $offset <= 255) {
            $this->emitLine('str ' . $reg . ', [x29, #' . $offset . ']');
        } else {
            $this->emitLoadImm('x16', $offset);
            $this->emitLine('str ' . $reg . ', [x29, x16]');
        }
    }

    private function emitLoadFP(string $reg, int $offset): void
    {
        if ($offset >= -256 && $offset <= 255) {
            $this->emitLine('ldr ' . $reg . ', [x29, #' . $offset . ']');
        } else {
            $this->emitLoadImm('x16', $offset);
            $this->emitLine('ldr ' . $reg . ', [x29, x16]');
        }
    }

    /**
     * Expand nested designator paths (`.a.b` / `.a[1]`) into nested InitListExprs,
     * merging multiple paths that share a parent so subobjects are not re-zeroed.
     */
    private function normalizeInitListPaths(InitListExpr $expr): InitListExpr
    {
        /** @var list<Expr> $outValues */
        $outValues = [];
        /** @var list<null|string|int|list<string|int>> $outDes */
        $outDes = [];
        /** @var array<string, int> $indexByDes */
        $indexByDes = [];

        foreach ($expr->values as $i => $v) {
            $des = $expr->designators[$i] ?? null;
            if (is_array($des) && $des !== []) {
                $first = $des[0];
                $wrapped = $v;
                for ($k = count($des) - 1; $k >= 1; $k--) {
                    $wrapped = new InitListExpr(
                        values: [$wrapped],
                        loc: $expr->loc,
                        designators: [$des[$k]],
                    );
                }
                $des = $first;
                $v = $wrapped;
            }
            if ($v instanceof InitListExpr) {
                $v = $this->normalizeInitListPaths($v);
            }

            $key = $des === null ? null : serialize($des);
            if ($key !== null
                && isset($indexByDes[$key])
                && $outValues[$indexByDes[$key]] instanceof InitListExpr
                && $v instanceof InitListExpr
            ) {
                $idx = $indexByDes[$key];
                $prev = $outValues[$idx];
                $outValues[$idx] = $this->normalizeInitListPaths(new InitListExpr(
                    values: array_merge($prev->values, $v->values),
                    loc: $expr->loc,
                    designators: array_merge($prev->designators, $v->designators),
                ));
            } else {
                if ($key !== null) {
                    $indexByDes[$key] = count($outValues);
                }
                $outValues[] = $v;
                $outDes[] = $des;
            }
        }

        return new InitListExpr(values: $outValues, loc: $expr->loc, designators: $outDes);
    }

    private function emitCopyStringToAddr(Arm64Reg $addrReg, StringLiteral $sl, ArrayCType $arrType): void
    {
        $label = $this->addStringLiteral($sl->value);
        $srcReg = $this->regAlloc->alloc() ?? Arm64Reg::X10;
        $this->emitLine('adrp ' . $srcReg->x() . ', ' . $label . '@PAGE');
        $this->emitLine('add ' . $srcReg->x() . ', ' . $srcReg->x() . ', ' . $label . '@PAGEOFF');
        $bytes = array_values(unpack('C*', $sl->value));
        $count = $arrType->count;
        $byteCount = min($count, count($bytes) + 1);
        for ($bi = 0; $bi < $byteCount; $bi++) {
            if ($bi === 0) {
                $this->emitLine('ldrb w16, [' . $srcReg->x() . ']');
                $this->emitLine('strb w16, [' . $addrReg->x() . ']');
            } else {
                $this->emitLine('ldrb w16, [' . $srcReg->x() . ', #' . $bi . ']');
                $this->emitLine('strb w16, [' . $addrReg->x() . ', #' . $bi . ']');
            }
        }
        for (; $bi < $count; $bi++) {
            $this->emitLine('strb wzr, [' . $addrReg->x() . ', #' . $bi . ']');
        }
        $this->regAlloc->free($srcReg);
    }

    private function emitInitValueToAddr(Arm64Reg $addrReg, Expr $v, CType $type): void
    {
        if ($v instanceof InitListExpr) {
            $this->emitLocalInit($addrReg, $v, $type);

            return;
        }
        if ($v instanceof StringLiteral) {
            $tu = $type->unqualified();
            if ($tu instanceof ArrayCType && $tu->of->isChar()) {
                $this->emitCopyStringToAddr($addrReg, $v, $tu);

                return;
            }
        }
        $valReg = $this->emitExpr($v);
        $this->emitStoreToAddr($addrReg, $valReg, $type);
        $this->regAlloc->free($valReg);
    }

    private function emitLocalInit(Arm64Reg $addrReg, Expr $expr, CType $type): void
    {
        if (!($expr instanceof InitListExpr)) {
            return;
        }
        $expr = $this->normalizeInitListPaths($expr);
        $t = $type->unqualified();
        $this->emitLine('sub sp, sp, #16');
        $this->emitLine('str ' . $addrReg->x() . ', [sp, #0]');
        $totalSize = $t->sizeInBytes() ?? 0;
        if ($t instanceof IncompleteArrayCType && $expr instanceof InitListExpr) {
            $elemSize = $t->of->sizeInBytes() ?? 4;
            $totalSize = max($elemSize * count($expr->values), $elemSize);
        }
        if ($totalSize > 0) {
            // C zero-initializes omitted aggregate members.
            $zeroReg = $this->regAlloc->alloc() ?? Arm64Reg::X9;
            $this->emitLine('mov ' . $zeroReg->x() . ', #0');
            for ($off = 0; $off + 8 <= $totalSize; $off += 8) {
                $this->emitLine('ldr x16, [sp, #0]');
                if ($off !== 0) {
                    $this->emitLine('add x16, x16, #' . $off);
                }
                $this->emitLine('str ' . $zeroReg->x() . ', [x16]');
            }
            for (; $off < $totalSize; $off++) {
                $this->emitLine('ldr x16, [sp, #0]');
                if ($off !== 0) {
                    $this->emitLine('add x16, x16, #' . $off);
                }
                $this->emitLine('strb ' . $zeroReg->w() . ', [x16]');
            }
            $this->regAlloc->free($zeroReg);
        }
        if ($t instanceof StructCType) {
            $fields = $t->record->fields;
            $next = 0;
            foreach ($expr->values as $i => $v) {
                $des = $expr->designators[$i] ?? null;
                if (is_string($des)) {
                    $found = $this->findMemberField($t, $des);
                    if ($found === null) {
                        continue;
                    }
                    $fieldAddr = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                    $this->emitLine('ldr ' . $fieldAddr->x() . ', [sp, #0]');
                    if ($found['offset'] !== 0) {
                        $this->emitLine('add ' . $fieldAddr->x() . ', ' . $fieldAddr->x() . ', #' . $found['offset']);
                    }
                    if ($v instanceof InitListExpr) {
                        $this->emitLocalInit($fieldAddr, $v, $found['type']);
                    } else {
                        $this->emitInitValueToAddr($fieldAddr, $v, $found['type']);
                    }
                    $this->regAlloc->free($fieldAddr);
                    $next = 0;
                    foreach ($fields as $j => $f) {
                        if ($f->offset <= $found['offset']) {
                            $next = $j + 1;
                        }
                    }
                    continue;
                }
                $fi = $next;
                if ($fi >= count($fields)) {
                    continue;
                }
                $fieldOffset = $fields[$fi]->offset;
                $fieldAddr = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLine('ldr ' . $fieldAddr->x() . ', [sp, #0]');
                if ($fieldOffset !== 0) {
                    $this->emitLine('add ' . $fieldAddr->x() . ', ' . $fieldAddr->x() . ', #' . $fieldOffset);
                }
                if ($v instanceof InitListExpr) {
                    $this->emitLocalInit($fieldAddr, $v, $fields[$fi]->type);
                } else {
                    $this->emitInitValueToAddr($fieldAddr, $v, $fields[$fi]->type);
                }
                $this->regAlloc->free($fieldAddr);
                $next = $fi + 1;
            }
        } elseif ($t instanceof UnionCType) {
            // Only the (first / designated) member is initialized; rest stays zeroed above.
            $fields = $t->record->fields;
            if ($fields !== [] && $expr->values !== []) {
                $v = $expr->values[0];
                $des = $expr->designators[0] ?? null;
                $fi = 0;
                if (is_string($des)) {
                    foreach ($fields as $j => $f) {
                        if ($f->name === $des) {
                            $fi = $j;
                            break;
                        }
                    }
                }
                if ($fi < count($fields)) {
                    $fieldAddr = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                    $this->emitLine('ldr ' . $fieldAddr->x() . ', [sp, #0]');
                    $fieldOffset = $fields[$fi]->offset;
                    if ($fieldOffset !== 0) {
                        $this->emitLine('add ' . $fieldAddr->x() . ', ' . $fieldAddr->x() . ', #' . $fieldOffset);
                    }
                    if ($v instanceof InitListExpr) {
                        $this->emitLocalInit($fieldAddr, $v, $fields[$fi]->type);
                    } else {
                        $this->emitInitValueToAddr($fieldAddr, $v, $fields[$fi]->type);
                    }
                    $this->regAlloc->free($fieldAddr);
                }
            }
        } elseif ($t instanceof ArrayCType || $t instanceof IncompleteArrayCType) {
            $elemType = $t instanceof ArrayCType ? $t->of : $t->of;
            $elemSize = $elemType->sizeInBytes() ?? 8;
            $next = 0;
            foreach ($expr->values as $i => $v) {
                $des = $expr->designators[$i] ?? null;
                $idx = is_int($des) ? $des : $next;
                $elemAddr = $this->regAlloc->alloc() ?? Arm64Reg::X9;
                $this->emitLine('ldr ' . $elemAddr->x() . ', [sp, #0]');
                if ($idx > 0) {
                    $this->emitLine('add ' . $elemAddr->x() . ', ' . $elemAddr->x() . ', #' . ($idx * $elemSize));
                }
                if ($v instanceof InitListExpr) {
                    $this->emitLocalInit($elemAddr, $v, $elemType);
                } else {
                    $this->emitInitValueToAddr($elemAddr, $v, $elemType);
                }
                $this->regAlloc->free($elemAddr);
                $next = $idx + 1;
            }
        }
        $this->emitLine('ldr ' . $addrReg->x() . ', [sp, #0]');
        $this->emitLine('add sp, sp, #16');
    }

    private function emitStoreToAddr(Arm64Reg $addrReg, Arm64Reg $valReg, CType $type): void
    {
        $t = $type->unqualified();
        if ($t instanceof BoolCType) {
            $this->emitLine('cmp ' . $valReg->x() . ', #0');
            $this->emitLine('cset ' . $valReg->w() . ', ne');
            $this->emitLine('strb ' . $valReg->w() . ', [' . $addrReg->x() . ']');
        } elseif ($t instanceof CharCType || $t instanceof SCharCType || $t instanceof UCharCType) {
            $this->emitLine('strb ' . $valReg->w() . ', [' . $addrReg->x() . ']');
        } elseif ($t instanceof ShortCType || $t instanceof UShortCType) {
            $this->emitLine('strh ' . $valReg->w() . ', [' . $addrReg->x() . ']');
        } elseif ($t instanceof IntCType || $t instanceof UIntCType) {
            $this->emitLine('str ' . $valReg->w() . ', [' . $addrReg->x() . ']');
        } elseif ($t instanceof FloatCType) {
            $this->emitLine('str s' . $valReg->regNum() . ', [' . $addrReg->x() . ']');
        } elseif ($t instanceof DoubleCType || $t instanceof LongDoubleCType) {
            $this->emitLine('str d' . $valReg->regNum() . ', [' . $addrReg->x() . ']');
        } else {
            $this->emitLine('str ' . $valReg->x() . ', [' . $addrReg->x() . ']');
        }
    }

    private function emitLoadImm(string $reg, int $value): void
    {
        if ($value === 0) {
            $this->emitLine('mov '.$reg.', #0');

            return;
        }
        if ($value > 0 && $value <= 0xFFFF) {
            $this->emitLine('mov '.$reg.', #'.$value);

            return;
        }

        /** @var int $uv */
        $uv = unpack('P', pack('q', $value))[1];
        $w0 = $uv & 0xFFFF;
        $w1 = ($uv >> 16) & 0xFFFF;
        $w2 = ($uv >> 32) & 0xFFFF;
        $w3 = ($uv >> 48) & 0xFFFF;
        $this->emitLine('movz '.$reg.', #'.$w0);
        if ($w1 !== 0) {
            $this->emitLine('movk '.$reg.', #'.$w1.', lsl #16');
        }
        if ($w2 !== 0) {
            $this->emitLine('movk '.$reg.', #'.$w2.', lsl #32');
        }
        if ($w3 !== 0) {
            $this->emitLine('movk '.$reg.', #'.$w3.', lsl #48');
        }
    }

    private function newLabel(): string
    {
        $this->labelCounter++;
        return 'L_' . $this->currentFuncName . '_' . $this->labelCounter;
    }
}
