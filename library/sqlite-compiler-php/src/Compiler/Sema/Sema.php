<?php

declare(strict_types=1);

namespace App\Compiler\Sema;

use App\Compiler\Common\ArrayCType;
use App\Compiler\Common\AssignExpr;
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
use App\Compiler\Common\CType;
use App\Compiler\Common\Decl;
use App\Compiler\Common\DeclStmt;
use App\Compiler\Common\DefaultStmt;
use App\Compiler\Common\DiagnosticEngine;
use App\Compiler\Common\DoWhileStmt;
use App\Compiler\Common\DoubleCType;
use App\Compiler\Common\EmptyStmt;
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
use App\Compiler\Common\MemberExpr;
use App\Compiler\Common\Param;
use App\Compiler\Common\PointerCType;
use App\Compiler\Common\QualifiedCType;
use App\Compiler\Common\RecordType;
use App\Compiler\Common\ReturnStmt;
use App\Compiler\Common\SCharCType;
use App\Compiler\Common\ShortCType;
use App\Compiler\Common\SizeofExpr;
use App\Compiler\Common\StaticAssertDecl;
use App\Compiler\Common\Stmt;
use App\Compiler\Common\StringLiteral;
use App\Compiler\Common\StructCType;
use App\Compiler\Common\StructDecl;
use App\Compiler\Common\SubscriptExpr;
use App\Compiler\Common\SwitchStmt;
use App\Compiler\Common\TypedefCType;
use App\Compiler\Common\TypedefDecl;
use App\Compiler\Common\UCharCType;
use App\Compiler\Common\ULongCType;
use App\Compiler\Common\UnionCType;
use App\Compiler\Common\UnionDecl;
use App\Compiler\Common\UnaryExpr;
use App\Compiler\Common\UnaryOp;
use App\Compiler\Common\UShortCType;
use App\Compiler\Common\VarDecl;
use App\Compiler\Common\WhileStmt;

/**
 * Semantic analysis: type-checks the AST, resolves symbols, and produces a typed AST.
 */
final class Sema
{
  private Scope $globalScope;
  private Scope $currentScope;

  /** @var array<string, CType> */
  private array $typedefs = [];

  /** @var array<string, RecordType> */
  private array $records = [];

  /** @var array<string, EnumType> */
  private array $enums = [];

  /** @var array<string, int> */
  public array $enumConstants = [];

  private CType $functionReturnType;
  private bool $inFunction = false;

  public function __construct(
    private readonly DiagnosticEngine $diags,
  ) {
    $this->globalScope = new Scope();
    $this->currentScope = $this->globalScope;
    $this->functionReturnType = IntCType::$instance;
  }

  /**
   * @param list<Decl> $decls
   *
   * @return list<Decl>
   */
  public function analyze(array $decls): array
  {
    foreach ($decls as $decl) {
      $this->collectTopLevel($decl);
    }

    foreach ($decls as $decl) {
      $this->analyzeDecl($decl);
    }

    return $decls;
  }

  private function collectTopLevel(Decl $decl): void
  {
    if ($decl instanceof FuncDecl) {
      $returnType = $this->resolveType($decl->returnType);
      $params = array_map(
        fn (Param $param): Param => new Param(
          name: $param->name,
          type: $this->resolveType($param->type),
          loc: $param->loc,
        ),
        $decl->params,
      );

      $params = array_map(
        function (Param $param): Param {
          $type = $param->type;
          $unqualified = $type->unqualified();
          if ($unqualified instanceof ArrayCType) {
            $type = new PointerCType($unqualified->of);
          } elseif ($unqualified instanceof IncompleteArrayCType) {
            $type = new PointerCType($unqualified->of);
          }

          return new Param(name: $param->name, type: $type, loc: $param->loc);
        },
        $params,
      );

      $funcType = new FunctionCType(
        params: array_map(static fn (Param $param): CType => $param->type, $params),
        returnType: $returnType,
        variadic: $decl->variadic,
      );

      $this->globalScope->insert(Symbol::function(
        name: $decl->name,
        type: $funcType,
        params: $params,
        variadic: $decl->variadic,
        defined: $decl->body !== null,
      ));

      return;
    }

    if ($decl instanceof VarDecl) {
      $type = $this->resolveType($decl->type);
      $this->globalScope->insert(Symbol::variable($decl->name, $type, isGlobal: true));

      return;
    }

    if ($decl instanceof TypedefDecl) {
      $type = $this->resolveType($decl->type);
      $this->typedefs[$decl->name] = $type;
      $this->globalScope->insert(Symbol::typedef($decl->name, $type));
      $this->registerEnumConstantsFromType($type);

      return;
    }

    if ($decl instanceof StructDecl) {
      $this->records[$decl->name ?? ''] = $decl->record;
      $this->registerEnumConstantsInRecord($decl->record);

      return;
    }

    if ($decl instanceof UnionDecl) {
      $this->records[$decl->name ?? ''] = $decl->record;
      $this->registerEnumConstantsInRecord($decl->record);

      return;
    }

    if ($decl instanceof EnumDecl) {
      $this->registerEnumConstants($decl->enumType);

      return;
    }
  }

  private function registerEnumConstants(EnumType $enumType): void
  {
    foreach ($enumType->cases as $case) {
      $this->enumConstants[$case->name] = $case->value;
      $this->globalScope->insert(Symbol::enumConstant(
        name: $case->name,
        value: $case->value,
        type: IntCType::$instance,
      ));
    }
  }

  private function registerEnumConstantsFromType(CType $type): void
  {
    $t = $type->unqualified();
    if ($t instanceof EnumCType && $t->enumType->cases !== []) {
      $this->registerEnumConstants($t->enumType);
    }
  }

  private function registerEnumConstantsInRecord(RecordType $record): void
  {
    foreach ($record->fields as $field) {
      $this->registerEnumConstantsFromType($field->type);
      $ft = $field->type->unqualified();
      if ($ft instanceof StructCType || $ft instanceof UnionCType) {
        $this->registerEnumConstantsInRecord($ft->record);
      }
    }
  }

  private function analyzeDecl(Decl $decl): void
  {
    if ($decl instanceof FuncDecl) {
      if ($decl->body !== null) {
        $this->analyzeFunctionBody($decl, $decl->body);
      }

      return;
    }

    if ($decl instanceof VarDecl) {
      if ($decl->initializer !== null) {
        $this->analyzeExpr($decl->initializer);
      }

      return;
    }

    if ($decl instanceof StaticAssertDecl) {
      $this->analyzeExpr($decl->condition);
    }
  }

  private function analyzeFunctionBody(FuncDecl $fd, CompoundStmt $body): void
  {
    $this->currentScope = new Scope(parent: $this->globalScope, isFunctionScope: true);
    $this->inFunction = true;
    $this->functionReturnType = $this->resolveType($fd->returnType);

    foreach ($fd->params as $param) {
      $this->currentScope->insert(Symbol::variable(
        name: $param->name ?? '',
        type: $this->resolveType($param->type),
        isGlobal: false,
      ));
    }

    $this->analyzeCompoundStmt($body);

    $this->currentScope = $this->globalScope;
    $this->inFunction = false;
  }

  private function analyzeStmt(Stmt $stmt): void
  {
    if ($stmt instanceof ExprStmt) {
      if ($stmt->expr !== null) {
        $this->analyzeExpr($stmt->expr);
      }

      return;
    }

    if ($stmt instanceof CompoundStmt) {
      $this->analyzeCompoundStmt($stmt);

      return;
    }

    if ($stmt instanceof IfStmt) {
      $this->analyzeExpr($stmt->condition);
      $this->analyzeStmt($stmt->thenStmt);
      if ($stmt->elseStmt !== null) {
        $this->analyzeStmt($stmt->elseStmt);
      }

      return;
    }

    if ($stmt instanceof WhileStmt) {
      $this->analyzeExpr($stmt->condition);
      $this->analyzeStmt($stmt->body);

      return;
    }

    if ($stmt instanceof DoWhileStmt) {
      $this->analyzeStmt($stmt->body);
      $this->analyzeExpr($stmt->condition);

      return;
    }

    if ($stmt instanceof ForStmt) {
      $this->currentScope = new Scope(parent: $this->currentScope);
      if ($stmt->initStmt !== null) {
        $this->analyzeStmt($stmt->initStmt);
      }
      if ($stmt->condition !== null) {
        $this->analyzeExpr($stmt->condition);
      }
      if ($stmt->increment !== null) {
        $this->analyzeExpr($stmt->increment);
      }
      $this->analyzeStmt($stmt->body);
      $this->currentScope = $this->currentScope->parent;

      return;
    }

    if ($stmt instanceof SwitchStmt) {
      $this->analyzeExpr($stmt->value);
      foreach ($stmt->cases as $case) {
        $this->analyzeStmt($case);
      }

      return;
    }

    if ($stmt instanceof CaseStmt) {
      $this->analyzeExpr($stmt->value);
      if ($stmt->stmt !== null) {
        $this->analyzeStmt($stmt->stmt);
      }

      return;
    }

    if ($stmt instanceof DefaultStmt) {
      if ($stmt->stmt !== null) {
        $this->analyzeStmt($stmt->stmt);
      }

      return;
    }

    if ($stmt instanceof BreakStmt || $stmt instanceof ContinueStmt || $stmt instanceof EmptyStmt || $stmt instanceof GotoStmt) {
      return;
    }

    if ($stmt instanceof ReturnStmt) {
      if ($stmt->value !== null) {
        $this->analyzeExpr($stmt->value);
      }

      return;
    }

    if ($stmt instanceof LabelStmt) {
      $this->analyzeStmt($stmt->stmt);

      return;
    }

    if ($stmt instanceof DeclStmt) {
      foreach ($stmt->decls as $d) {
        if ($d instanceof VarDecl) {
          $type = $this->resolveType($d->type);
          $this->currentScope->insert(Symbol::variable($d->name, $type, isGlobal: false));
          if ($d->initializer !== null) {
            $this->analyzeExpr($d->initializer);
          }
        }
      }
    }
  }

  private function analyzeCompoundStmt(CompoundStmt $cs): void
  {
    $this->currentScope = new Scope(parent: $this->currentScope);
    foreach ($cs->statements as $stmt) {
      $this->analyzeStmt($stmt);
    }
    $this->currentScope = $this->currentScope->parent;
  }

  /**
   * Analyze an expression and return its resolved type.
   */
  private function analyzeExpr(Expr $expr): CType
  {
    $type = match (true) {
      $expr instanceof IntegerLiteral => $expr->type,
      $expr instanceof FloatLiteral => $expr->type,
      $expr instanceof CharLiteral => IntCType::$instance,
      $expr instanceof StringLiteral => $expr->type,
      $expr instanceof BoolLiteral => BoolCType::$instance,
      $expr instanceof Identifier => $this->analyzeIdentifier($expr),
      $expr instanceof BinaryExpr => $this->analyzeBinaryExpr($expr),
      $expr instanceof UnaryExpr => $this->analyzeUnaryExpr($expr),
      $expr instanceof AssignExpr => $this->analyzeAssignExpr($expr),
      $expr instanceof ConditionalExpr => $this->analyzeConditionalExpr($expr),
      $expr instanceof CallExpr => $this->analyzeCallExpr($expr),
      $expr instanceof SubscriptExpr => $this->analyzeSubscriptExpr($expr),
      $expr instanceof MemberExpr => $this->analyzeMemberExpr($expr),
      $expr instanceof CastExpr => $this->analyzeCastExpr($expr),
      $expr instanceof SizeofExpr => $this->analyzeSizeofExpr($expr),
      $expr instanceof CompoundLiteralExpr => $this->analyzeCompoundLiteralExpr($expr),
      $expr instanceof InitListExpr => $this->analyzeInitListExpr($expr),
      default => IntCType::$instance,
    };

    $this->setResolvedType($expr, $type);

    return $type;
  }

  private function analyzeIdentifier(Identifier $id): CType
  {
    $sym = $this->currentScope->lookup($id->name);
    if ($sym !== null) {
      return match ($sym->kind) {
        SymbolKind::Variable => $sym->type,
        SymbolKind::Function => $sym->type,
        SymbolKind::EnumConstant => $sym->type,
        SymbolKind::Typedef => $sym->type,
      };
    }

    if (isset($this->enumConstants[$id->name])) {
      return IntCType::$instance;
    }

    $this->diags->error("use of undeclared identifier '{$id->name}'", $id->loc);

    return IntCType::$instance;
  }

  private function analyzeBinaryExpr(BinaryExpr $expr): CType
  {
    $leftType = $this->analyzeExpr($expr->left);
    $rightType = $this->analyzeExpr($expr->right);

    return $this->binaryResultType($expr->op, $leftType, $rightType);
  }

  private function analyzeUnaryExpr(UnaryExpr $expr): CType
  {
    $operandType = $this->analyzeExpr($expr->operand);

    return match ($expr->op) {
      UnaryOp::Neg, UnaryOp::Pos, UnaryOp::BitNot => $this->integerPromote($operandType),
      UnaryOp::Not => IntCType::$instance,
      UnaryOp::Dereference => $this->analyzeDereference($operandType),
      UnaryOp::AddressOf => $this->analyzeAddressOf($expr, $operandType),
      UnaryOp::PreInc, UnaryOp::PreDec, UnaryOp::PostInc, UnaryOp::PostDec => $this->analyzeIncDec($expr, $operandType),
    };
  }

  private function analyzeDereference(CType $operandType): CType
  {
    $unqualified = $operandType->unqualified();
    if ($unqualified instanceof PointerCType) {
      return $unqualified->to;
    }

    return IntCType::$instance;
  }

  private function analyzeAddressOf(UnaryExpr $expr, CType $operandType): CType
  {
    if (!$this->isLvalue($expr->operand)) {
      $this->diags->error('cannot take address of rvalue', $expr->loc);
    }

    return new PointerCType($operandType);
  }

  private function analyzeIncDec(UnaryExpr $expr, CType $operandType): CType
  {
    if (!$this->isLvalue($expr->operand)) {
      $this->diags->error('operand of increment/decrement must be an lvalue', $expr->loc);
    }

    return $operandType;
  }

  private function analyzeAssignExpr(AssignExpr $expr): CType
  {
    if (!$this->isLvalue($expr->target)) {
      $this->diags->error('expression is not assignable', $expr->target->loc);
    }

    $targetType = $this->analyzeExpr($expr->target);
    $this->analyzeExpr($expr->value);

    return $targetType;
  }

  private function analyzeConditionalExpr(ConditionalExpr $expr): CType
  {
    $this->analyzeExpr($expr->condition);
    $trueType = $this->analyzeExpr($expr->trueExpr);
    $falseType = $this->analyzeExpr($expr->falseExpr);

    return $this->usualArithmeticConversion($trueType, $falseType);
  }

  private function analyzeCallExpr(CallExpr $expr): CType
  {
    $funcType = $this->analyzeExpr($expr->function);
    foreach ($expr->arguments as $arg) {
      $this->analyzeExpr($arg);
    }

    $unqualified = $funcType->unqualified();
    if ($unqualified instanceof FunctionCType) {
      return $unqualified->returnType;
    }

    if ($unqualified instanceof PointerCType) {
      $pointee = $unqualified->to->unqualified();
      if ($pointee instanceof FunctionCType) {
        return $pointee->returnType;
      }
    }

    return IntCType::$instance;
  }

  private function analyzeSubscriptExpr(SubscriptExpr $expr): CType
  {
    $baseType = $this->analyzeExpr($expr->base);
    $this->analyzeExpr($expr->index);

    $unqualified = $baseType->unqualified();
    if ($unqualified instanceof PointerCType) {
      return $unqualified->to;
    }

    if ($unqualified instanceof ArrayCType) {
      return $unqualified->of;
    }

    return IntCType::$instance;
  }

    private function analyzeMemberExpr(MemberExpr $expr): CType
    {
        $baseType = $this->analyzeExpr($expr->base);
        $recordType = $baseType->unqualified();

        if ($expr->isArrow) {
            $baseUnqualified = $baseType->unqualified();
            if ($baseUnqualified instanceof PointerCType) {
                $recordType = $baseUnqualified->to;
            }
        }

        $found = $this->findMemberField($recordType, $expr->memberName);
        if ($found !== null) {
            return $found['type'];
        }

        return IntCType::$instance;
    }

    /**
     * @return array{offset: int, type: CType}|null
     */
    private function findMemberField(CType $baseType, string $memberName): ?array
    {
        $t = $baseType->unqualified();
        if ($t instanceof PointerCType) {
            $t = $t->to->unqualified();
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

  private function analyzeCastExpr(CastExpr $expr): CType
  {
    $this->analyzeExpr($expr->expr);

    return $expr->type;
  }

  private function analyzeSizeofExpr(SizeofExpr $expr): CType
  {
    if ($expr->typeName !== null) {
      return ULongCType::$instance;
    }

    if ($expr->expr !== null) {
      $this->analyzeExpr($expr->expr);
    }

    return ULongCType::$instance;
  }

  private function analyzeCompoundLiteralExpr(CompoundLiteralExpr $expr): CType
  {
    $this->analyzeExpr($expr->initList);

    return $expr->type;
  }

  private function analyzeInitListExpr(InitListExpr $expr): CType
  {
    foreach ($expr->values as $value) {
      $this->analyzeExpr($value);
    }

    return IntCType::$instance;
  }

  /**
   * Basic lvalue analysis for assignment, address-of, and inc/dec.
   */
  public function isLvalue(Expr $expr): bool
  {
    if ($expr instanceof CastExpr) {
      return $this->isLvalue($expr->expr);
    }

    if ($expr instanceof Identifier) {
      $sym = $this->currentScope->lookup($expr->name);
      if ($sym !== null) {
        return $sym->kind === SymbolKind::Variable;
      }

      return false;
    }

    if ($expr instanceof MemberExpr) {
      if ($expr->isArrow) {
        return true;
      }

      return $this->isLvalue($expr->base);
    }

    if ($expr instanceof SubscriptExpr) {
      return true;
    }

    // C99: a compound literal designates an unnamed object and is an lvalue.
    if ($expr instanceof CompoundLiteralExpr) {
      return true;
    }

    if ($expr instanceof UnaryExpr) {
      return match ($expr->op) {
        UnaryOp::Dereference => true,
        default => false,
      };
    }

    return false;
  }

  private function resolveType(CType $type): CType
  {
    if ($type instanceof TypedefCType) {
      if (isset($this->typedefs[$type->name])) {
        return $this->typedefs[$type->name];
      }

      return $this->resolveType($type->base);
    }

    if ($type instanceof QualifiedCType) {
      return new QualifiedCType(
        base: $this->resolveType($type->base),
        const: $type->const,
        volatile: $type->volatile,
        restrict: $type->restrict,
      );
    }

    if ($type instanceof PointerCType) {
      return new PointerCType($this->resolveType($type->to));
    }

    if ($type instanceof ArrayCType) {
      return new ArrayCType(
        of: $this->resolveType($type->of),
        count: $type->count,
      );
    }

    if ($type instanceof IncompleteArrayCType) {
      return new IncompleteArrayCType($this->resolveType($type->of));
    }

    if ($type instanceof FunctionCType) {
      return new FunctionCType(
        params: array_map(fn (CType $param): CType => $this->resolveType($param), $type->params),
        returnType: $this->resolveType($type->returnType),
        variadic: $type->variadic,
      );
    }

    if ($type instanceof StructCType) {
      $completed = $this->records[$type->record->name] ?? null;
      if ($completed !== null) {
        return new StructCType($completed);
      }

      return $type;
    }

    if ($type instanceof UnionCType) {
      $completed = $this->records[$type->record->name] ?? null;
      if ($completed !== null) {
        return new UnionCType($completed);
      }

      return $type;
    }

    if ($type instanceof EnumCType) {
      $completed = $this->enums[$type->enumType->name] ?? null;
      if ($completed !== null) {
        return new EnumCType($completed);
      }

      return $type;
    }

    return $type;
  }

  /**
   * Apply integer promotions (C99 6.3.1.1).
   */
  private function integerPromote(CType $type): CType
  {
    $t = $type->unqualified();

    return match (true) {
      $t instanceof BoolCType,
      $t instanceof CharCType,
      $t instanceof SCharCType,
      $t instanceof UCharCType,
      $t instanceof ShortCType,
      $t instanceof UShortCType => IntCType::$instance,
      default => $t,
    };
  }

  /**
   * Apply usual arithmetic conversions (C99 6.3.1.8).
   */
  private function usualArithmeticConversion(CType $a, CType $b): CType
  {
    $ta = $a->unqualified();
    $tb = $b->unqualified();

    if ($ta instanceof LongDoubleCType || $tb instanceof LongDoubleCType) {
      return LongDoubleCType::$instance;
    }

    if ($ta instanceof DoubleCType || $tb instanceof DoubleCType) {
      return DoubleCType::$instance;
    }

    if ($ta instanceof FloatCType || $tb instanceof FloatCType) {
      return FloatCType::$instance;
    }

    $ia = $this->integerPromote($ta);
    $ib = $this->integerPromote($tb);

    if ($this->typesEqual($ia, $ib)) {
      return $ia;
    }

    $aSigned = $ia->isSigned();
    $bSigned = $ib->isSigned();

    if ($aSigned === $bSigned) {
      $aSize = $ia->sizeInBytes() ?? 0;
      $bSize = $ib->sizeInBytes() ?? 0;

      return $aSize >= $bSize ? $ia : $ib;
    }

    $aSize = $ia->sizeInBytes() ?? 0;
    $bSize = $ib->sizeInBytes() ?? 0;

    if (!$aSigned && $aSize >= $bSize) {
      return $ia;
    }

    if (!$bSigned && $bSize >= $aSize) {
      return $ib;
    }

    if ($aSigned && $aSize > $bSize) {
      return $ia;
    }

    if ($bSigned && $bSize > $aSize) {
      return $ib;
    }

    return $ia;
  }

  private function binaryResultType(BinaryOp $op, CType $left, CType $right): CType
  {
    return match ($op) {
      BinaryOp::LogicAnd, BinaryOp::LogicOr,
      BinaryOp::Eq, BinaryOp::Ne, BinaryOp::Lt, BinaryOp::Le, BinaryOp::Gt, BinaryOp::Ge => IntCType::$instance,
      BinaryOp::Comma => $right,
      BinaryOp::Add, BinaryOp::Sub, BinaryOp::Mul, BinaryOp::Div, BinaryOp::Mod,
      BinaryOp::Shl, BinaryOp::Shr, BinaryOp::BitAnd, BinaryOp::BitOr, BinaryOp::BitXor => $this->arithmeticBinaryResultType($left, $right),
    };
  }

  private function arithmeticBinaryResultType(CType $left, CType $right): CType
  {
    if ($left->isPointer() && $right->isInteger()) {
      return $left;
    }

    if ($left->isInteger() && $right->isPointer()) {
      return $right;
    }

    if ($left->isPointer() && $right->isPointer()) {
      return LongCType::$instance;
    }

    return $this->usualArithmeticConversion($left, $right);
  }

  private function typesEqual(CType $a, CType $b): bool
  {
    $a = $a->unqualified();
    $b = $b->unqualified();

    if ($a === $b) {
      return true;
    }

    if ($a::class !== $b::class) {
      return false;
    }

    if ($a instanceof PointerCType && $b instanceof PointerCType) {
      return $this->typesEqual($a->to, $b->to);
    }

    if ($a instanceof ArrayCType && $b instanceof ArrayCType) {
      return $a->count === $b->count && $this->typesEqual($a->of, $b->of);
    }

    if ($a instanceof IncompleteArrayCType && $b instanceof IncompleteArrayCType) {
      return $this->typesEqual($a->of, $b->of);
    }

    if ($a instanceof FunctionCType && $b instanceof FunctionCType) {
      if ($a->variadic !== $b->variadic || count($a->params) !== count($b->params)) {
        return false;
      }

      if (!$this->typesEqual($a->returnType, $b->returnType)) {
        return false;
      }

      foreach ($a->params as $i => $param) {
        if (!$this->typesEqual($param, $b->params[$i])) {
          return false;
        }
      }

      return true;
    }

    if ($a instanceof StructCType && $b instanceof StructCType) {
      return $a->record->name === $b->record->name;
    }

    if ($a instanceof UnionCType && $b instanceof UnionCType) {
      return $a->record->name === $b->record->name;
    }

    if ($a instanceof EnumCType && $b instanceof EnumCType) {
      return $a->enumType->name === $b->enumType->name;
    }

    return false;
  }

  private function setResolvedType(Expr $expr, CType $type): void
  {
    if (property_exists($expr, 'resolvedType')) {
      $expr->resolvedType = $type;
    }
  }
}
