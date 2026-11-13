<?php

declare(strict_types=1);

namespace App\Compiler\Common;

interface AstNode
{
    public SourceLoc $loc { get; }
}

enum StorageClass
{
    case None;
    case Static;
    case Extern;
    case Auto;
    case Register;
}

// MARK: - Declarations

abstract class Decl implements AstNode
{
    public function __construct(
        public SourceLoc $loc,
    ) {
    }
}

final class VarDecl extends Decl
{
    public function __construct(
        public string $name,
        public CType $type,
        SourceLoc $loc,
        public ?Expr $initializer = null,
        public StorageClass $storageClass = StorageClass::None,
        public bool $isGlobal = false,
    ) {
        parent::__construct($loc);
    }
}

final class FuncDecl extends Decl
{
    /**
     * @param list<Param> $params
     */
    public function __construct(
        public string $name,
        public CType $returnType,
        public array $params,
        public bool $variadic,
        SourceLoc $loc,
        public ?CompoundStmt $body = null,
        public StorageClass $storageClass = StorageClass::None,
        public bool $isInline = false,
    ) {
        parent::__construct($loc);
    }
}

final readonly class Param implements AstNode
{
    public function __construct(
        public ?string $name,
        public CType $type,
        public SourceLoc $loc,
    ) {
    }
}

final class TypedefDecl extends Decl
{
    public function __construct(
        public string $name,
        public CType $type,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class StructDecl extends Decl
{
    public function __construct(
        public ?string $name,
        public RecordType $record,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class UnionDecl extends Decl
{
    public function __construct(
        public ?string $name,
        public RecordType $record,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class EnumDecl extends Decl
{
    public function __construct(
        public ?string $name,
        public EnumType $enumType,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class StaticAssertDecl extends Decl
{
    public function __construct(
        public Expr $condition,
        public ?string $message,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

// MARK: - Statements

abstract class Stmt implements AstNode
{
    public function __construct(
        public SourceLoc $loc,
    ) {
    }
}

final class ExprStmt extends Stmt
{
    public function __construct(
        public ?Expr $expr,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class CompoundStmt extends Stmt
{
    /**
     * @param list<Stmt> $statements
     */
    public function __construct(
        public array $statements,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class IfStmt extends Stmt
{
    public function __construct(
        public Expr $condition,
        public Stmt $thenStmt,
        public ?Stmt $elseStmt,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class WhileStmt extends Stmt
{
    public function __construct(
        public Expr $condition,
        public Stmt $body,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class DoWhileStmt extends Stmt
{
    public function __construct(
        public Stmt $body,
        public Expr $condition,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class ForStmt extends Stmt
{
    public function __construct(
        public ?Stmt $initStmt,
        public ?Expr $condition,
        public ?Expr $increment,
        public Stmt $body,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class SwitchStmt extends Stmt
{
    /**
     * @param list<Stmt> $cases CaseStmt and DefaultStmt nodes, followed by statements
     */
    public function __construct(
        public Expr $value,
        public array $cases,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class CaseStmt extends Stmt
{
    public function __construct(
        public Expr $value,
        public ?Stmt $stmt,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class DefaultStmt extends Stmt
{
    public function __construct(
        public ?Stmt $stmt,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class BreakStmt extends Stmt
{
    public function __construct(SourceLoc $loc)
    {
        parent::__construct($loc);
    }
}

final class ContinueStmt extends Stmt
{
    public function __construct(SourceLoc $loc)
    {
        parent::__construct($loc);
    }
}

final class ReturnStmt extends Stmt
{
    public function __construct(
        public ?Expr $value,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class GotoStmt extends Stmt
{
    public function __construct(
        public string $label,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class LabelStmt extends Stmt
{
    public function __construct(
        public string $name,
        public Stmt $stmt,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class DeclStmt extends Stmt
{
    /**
     * @param list<Decl> $decls
     */
    public function __construct(
        public array $decls,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class EmptyStmt extends Stmt
{
    public function __construct(SourceLoc $loc)
    {
        parent::__construct($loc);
    }
}

// MARK: - Expressions

abstract class Expr implements AstNode
{
    public function __construct(
        public SourceLoc $loc,
    ) {
    }
}

final class IntegerLiteral extends Expr
{
    public CType $type;

    public function __construct(
        public int $value,
        SourceLoc $loc,
        public bool $isUnsigned = false,
        ?CType $type = null,
    ) {
        parent::__construct($loc);
        $this->type = $type ?? IntCType::$instance;
    }
}

final class FloatLiteral extends Expr
{
    public CType $type;

    public function __construct(
        public float $value,
        SourceLoc $loc,
        ?CType $type = null,
    ) {
        parent::__construct($loc);
        $this->type = $type ?? DoubleCType::$instance;
    }
}

final class CharLiteral extends Expr
{
    public CType $type;

    public function __construct(
        public int $value,
        SourceLoc $loc,
        ?CType $type = null,
    ) {
        parent::__construct($loc);
        $this->type = $type ?? IntCType::$instance;
    }
}

final class StringLiteral extends Expr
{
    public function __construct(
        public string $value,
        public CType $type,
        SourceLoc $loc,
    ) {
        parent::__construct($loc);
    }
}

final class BoolLiteral extends Expr
{
    public CType $type;

    public function __construct(
        public bool $value,
        SourceLoc $loc,
        ?CType $type = null,
    ) {
        parent::__construct($loc);
        $this->type = $type ?? BoolCType::$instance;
    }
}

final class Identifier extends Expr
{
    public function __construct(
        public string $name,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class MemberExpr extends Expr
{
    public function __construct(
        public Expr $base,
        public string $memberName,
        public bool $isArrow,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

enum BinaryOp: string
{
    case Add = '+';
    case Sub = '-';
    case Mul = '*';
    case Div = '/';
    case Mod = '%';
    case Shl = '<<';
    case Shr = '>>';
    case BitAnd = '&';
    case BitOr = '|';
    case BitXor = '^';
    case LogicAnd = '&&';
    case LogicOr = '||';
    case Eq = '==';
    case Ne = '!=';
    case Lt = '<';
    case Le = '<=';
    case Gt = '>';
    case Ge = '>=';
    case Comma = ',';
}

final class BinaryExpr extends Expr
{
    public function __construct(
        public BinaryOp $op,
        public Expr $left,
        public Expr $right,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

enum UnaryOp: string
{
    case Neg = '-';
    case Pos = '+';
    case Not = '!';
    case BitNot = '~';
    case PreInc = '++p';
    case PreDec = '--p';
    case PostInc = 'p++';
    case PostDec = 'p--';
    case AddressOf = '&';
    case Dereference = '*';
}

final class UnaryExpr extends Expr
{
    public function __construct(
        public UnaryOp $op,
        public Expr $operand,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

enum AssignOp: string
{
    case Assign = '=';
    case AddAssign = '+=';
    case SubAssign = '-=';
    case MulAssign = '*=';
    case DivAssign = '/=';
    case ModAssign = '%=';
    case ShlAssign = '<<=';
    case ShrAssign = '>>=';
    case AndAssign = '&=';
    case OrAssign = '|=';
    case XorAssign = '^=';
}

final class AssignExpr extends Expr
{
    public function __construct(
        public AssignOp $op,
        public Expr $target,
        public Expr $value,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class ConditionalExpr extends Expr
{
    public function __construct(
        public Expr $condition,
        public Expr $trueExpr,
        public Expr $falseExpr,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class CallExpr extends Expr
{
    /**
     * @param list<Expr> $arguments
     */
    public function __construct(
        public Expr $function,
        public array $arguments,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class SubscriptExpr extends Expr
{
    public function __construct(
        public Expr $base,
        public Expr $index,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class CastExpr extends Expr
{
    public function __construct(
        public CType $type,
        public Expr $expr,
        SourceLoc $loc,
        public ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
    }
}

final class SizeofExpr extends Expr
{
    public ?CType $resolvedType;

    public function __construct(
        public ?Expr $expr,
        public ?CType $typeName,
        SourceLoc $loc,
        ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
        $this->resolvedType = $resolvedType ?? ULongCType::$instance;
    }
}

final class CompoundLiteralExpr extends Expr
{
    public ?CType $resolvedType;

    public function __construct(
        public CType $type,
        public Expr $initList,
        SourceLoc $loc,
        ?CType $resolvedType = null,
    ) {
        parent::__construct($loc);
        $this->resolvedType = $resolvedType ?? $type;
    }
}

final class InitListExpr extends Expr
{
    /**
     * @param list<Expr> $values
     * @param list<null|string|int|list<string|int>> $designators null = next sequential field/element;
     *        string = `.field`; int = `[index]`; list = nested designator path (`.a.b` / `.a[1]`)
     */
    public function __construct(
        public array $values,
        SourceLoc $loc,
        public array $designators = [],
    ) {
        parent::__construct($loc);
    }
}
