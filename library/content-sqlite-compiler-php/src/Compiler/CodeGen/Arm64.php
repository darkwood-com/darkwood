<?php

declare(strict_types=1);

namespace App\Compiler\CodeGen;

/**
 * ARM64 general-purpose registers.
 */
enum Arm64Reg: string
{
    case X0 = 'x0';
    case X1 = 'x1';
    case X2 = 'x2';
    case X3 = 'x3';
    case X4 = 'x4';
    case X5 = 'x5';
    case X6 = 'x6';
    case X7 = 'x7';
    case X8 = 'x8';
    case X9 = 'x9';
    case X10 = 'x10';
    case X11 = 'x11';
    case X12 = 'x12';
    case X13 = 'x13';
    case X14 = 'x14';
    case X15 = 'x15';
    case X16 = 'x16';
    case X17 = 'x17';
    case X18 = 'x18';
    case X19 = 'x19';
    case X20 = 'x20';
    case X21 = 'x21';
    case X22 = 'x22';
    case X23 = 'x23';
    case X24 = 'x24';
    case X25 = 'x25';
    case X26 = 'x26';
    case X27 = 'x27';
    case X28 = 'x28';
    case X29 = 'x29';
    case X30 = 'x30';
    case Sp = 'sp';
    case Xzr = 'xzr';
    case Wzr = 'wzr';

    /** 32-bit (W) register name. */
    public function w(): string
    {
        return match ($this) {
            self::X0 => 'w0', self::X1 => 'w1', self::X2 => 'w2',
            self::X3 => 'w3', self::X4 => 'w4', self::X5 => 'w5',
            self::X6 => 'w6', self::X7 => 'w7', self::X8 => 'w8',
            self::X9 => 'w9', self::X10 => 'w10', self::X11 => 'w11',
            self::X12 => 'w12', self::X13 => 'w13', self::X14 => 'w14',
            self::X15 => 'w15', self::X16 => 'w16', self::X17 => 'w17',
            self::X18 => 'w18', self::X19 => 'w19', self::X20 => 'w20',
            self::X21 => 'w21', self::X22 => 'w22', self::X23 => 'w23',
            self::X24 => 'w24', self::X25 => 'w25', self::X26 => 'w26',
            self::X27 => 'w27', self::X28 => 'w28', self::X29 => 'w29',
            self::X30 => 'w30',
            self::Sp => 'sp', self::Xzr, self::Wzr => 'wzr',
        };
    }

    /** 64-bit (X) register name. */
    public function x(): string
    {
        return match ($this) {
            self::Xzr => 'xzr',
            self::Wzr => 'wzr',
            default => $this->value,
        };
    }

    /** Register number (0-30). */
    public function regNum(): int
    {
        return match ($this) {
            self::X0 => 0, self::X1 => 1, self::X2 => 2,
            self::X3 => 3, self::X4 => 4, self::X5 => 5,
            self::X6 => 6, self::X7 => 7, self::X8 => 8,
            self::X9 => 9, self::X10 => 10, self::X11 => 11,
            self::X12 => 12, self::X13 => 13, self::X14 => 14,
            self::X15 => 15, self::X16 => 16, self::X17 => 17,
            self::X18 => 18, self::X19 => 19, self::X20 => 20,
            self::X21 => 21, self::X22 => 22, self::X23 => 23,
            self::X24 => 24, self::X25 => 25, self::X26 => 26,
            self::X27 => 27, self::X28 => 28, self::X29 => 29,
            self::X30 => 30,
            default => 0,
        };
    }
}

/** Available caller-saved registers for temporary use (x9-x15 are scratch). */
/** @var list<Arm64Reg> */
const SCRATCH_REGS = [
    Arm64Reg::X9, Arm64Reg::X10, Arm64Reg::X11, Arm64Reg::X12,
    Arm64Reg::X13, Arm64Reg::X14, Arm64Reg::X15,
];

/** Argument registers (AAPCS64). */
/** @var list<Arm64Reg> */
const ARG_REGS = [
    Arm64Reg::X0, Arm64Reg::X1, Arm64Reg::X2, Arm64Reg::X3,
    Arm64Reg::X4, Arm64Reg::X5, Arm64Reg::X6, Arm64Reg::X7,
];

/**
 * A simple register allocator that manages a pool of scratch registers.
 */
final class RegAlloc
{
    /** @var list<Arm64Reg> */
    public array $available;

    public function __construct()
    {
        $this->available = SCRATCH_REGS;
    }

    public function alloc(): ?Arm64Reg
    {
        if ($this->available === []) {
            return null;
        }

        return array_shift($this->available);
    }

    public function free(Arm64Reg $reg): void
    {
        array_unshift($this->available, $reg);
    }

    public function reset(): void
    {
        $this->available = SCRATCH_REGS;
    }
}
