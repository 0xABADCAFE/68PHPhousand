<?php

/**
 * MC68020 Usage Examples
 *
 * This file demonstrates how to use the 68020 emulator features.
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use ABadCafe\G8PHPhousand\Processor\IProcessorModel;
use ABadCafe\G8PHPhousand\TestHarness\CPU;
use ABadCafe\G8PHPhousand\Device\Memory\SparseWordRAM;

echo "=== MC68020 Emulator Usage Examples ===\n\n";

// ============================================================================
// Example 1: Creating an MC68020 CPU Instance
// ============================================================================

echo "Example 1: Creating MC68020 Instance\n";
echo "-------------------------------------\n";

$oMemory = new SparseWordRAM();
$oCPU = new CPU($oMemory, IProcessorModel::MC68020);

echo "Created MC68020 CPU with 32-bit address space (4GB)\n";
echo "Processor model: " . $oCPU->getModelName() . "\n\n";

// ============================================================================
// Example 2: 32-bit Multiply (MULS.L)
// ============================================================================

echo "Example 2: 32-bit Multiply\n";
echo "--------------------------\n";

// MULS.L #$12345678,D0   ; 32×32→64 multiply
// Result will be in D0 (low 32 bits) and extension word specifies high reg
//
// Opcode: 0x4C3C (MULS.L immediate)
// Extension word for D0 result
// Immediate long value

$oMemory->writeLong(0x1000, 0x4C3C_0800);     // MULS.L #imm,D0 (32-bit result)
$oMemory->writeLong(0x1004, 0x1234_5678);     // Immediate value
$oMemory->writeLong(0x1008, 0x4E75);          // RTS

echo "Code assembled at 0x1000: MULS.L #\$12345678,D0\n";
echo "This demonstrates 32-bit signed multiply\n\n";

// ============================================================================
// Example 3: Bit Field Operations (BFTST)
// ============================================================================

echo "Example 3: Bit Field Test\n";
echo "-------------------------\n";

// BFTST D0{4:8}  ; Test 8 bits starting at bit 4 in D0
//
// Opcode: 0xE8C0 (BFTST Dn)
// Extension word: offset=4 (immediate), width=8 (immediate)

$oMemory->writeLong(0x2000, 0xE8C0_0408);     // BFTST D0{4:8}
$oMemory->writeLong(0x2004, 0x4E75);          // RTS

echo "Code assembled at 0x2000: BFTST D0{4:8}\n";
echo "Tests 8-bit field at bit offset 4 in D0\n";
echo "Bit fields can be 1-32 bits, offset can be in register or immediate\n\n";

// ============================================================================
// Example 4: Atomic Compare and Swap (CAS.L)
// ============================================================================

echo "Example 4: Atomic Compare and Swap\n";
echo "-----------------------------------\n";

// CAS.L D1,D2,(A0)   ; Compare (A0) with D1, if equal swap with D2
//
// Opcode: 0x0ED0 (CAS.L (An))
// Extension word: Dc=D1, Du=D2

$oMemory->writeLong(0x3000, 0x0ED0_0142);     // CAS.L D1,D2,(A0)
$oMemory->writeLong(0x3004, 0x4E75);          // RTS

echo "Code assembled at 0x3000: CAS.L D1,D2,(A0)\n";
echo "Atomic operation for multiprocessing:\n";
echo "- If (A0) == D1, then (A0) := D2 and Z=1\n";
echo "- Else D1 := (A0) and Z=0\n\n";

// ============================================================================
// Example 5: Bounds Checking (CHK2.L)
// ============================================================================

echo "Example 5: Bounds Checking\n";
echo "--------------------------\n";

// CHK2.L (A0),D0   ; Check if D0 is within bounds at (A0)
//
// Opcode: 0x00D0 (CHK2.L (An))
// Extension word: D/A=0 (Dn), register=0 (D0), CHK2=1, size=10 (long)

$oMemory->writeLong(0x4000, 0x00D0_0C00);     // CHK2.L (A0),D0
$oMemory->writeLong(0x4004, 0x4E75);          // RTS

echo "Code assembled at 0x4000: CHK2.L (A0),D0\n";
echo "(A0) points to two consecutive longs: lower_bound, upper_bound\n";
echo "If D0 < lower or D0 > upper, exception is generated\n\n";

// ============================================================================
// Example 6: PACK/UNPK BCD Operations
// ============================================================================

echo "Example 6: BCD Pack Operation\n";
echo "-----------------------------\n";

// PACK -(A1),-(A0),#$0000   ; Pack two unpacked BCD bytes
//
// Opcode: 0x8141 (PACK -(Ax),-(Ay))
// Adjustment: $0000

$oMemory->writeLong(0x5000, 0x8141_0000);     // PACK -(A1),-(A0),#$0000
$oMemory->writeLong(0x5004, 0x4E75);          // RTS

echo "Code assembled at 0x5000: PACK -(A1),-(A0),#\$0000\n";
echo "Converts two unpacked BCD bytes to one packed BCD byte\n";
echo "Used for BCD arithmetic and decimal display\n\n";

// ============================================================================
// Example 7: Control Register Access (MOVEC)
// ============================================================================

echo "Example 7: Control Register Access\n";
echo "-----------------------------------\n";

// MOVEC VBR,D0   ; Move Vector Base Register to D0
//
// Opcode: 0x4E7A (MOVEC Rc,Rn)
// Extension word: D/A=0 (Dn), register=0 (D0), control_reg=0x801 (VBR)

$oMemory->writeLong(0x6000, 0x4E7A_0801);     // MOVEC VBR,D0
$oMemory->writeLong(0x6004, 0x4E75);          // RTS

echo "Code assembled at 0x6000: MOVEC VBR,D0\n";
echo "Reads Vector Base Register (exception table base)\n";
echo "Available control registers: VBR, CACR, CAAR, SFC, DFC, USP, MSP, ISP\n\n";

// ============================================================================
// Example 8: Conditional Trap (TRAPcc)
// ============================================================================

echo "Example 8: Conditional Trap\n";
echo "---------------------------\n";

// TRAPNE.W #$1234   ; Trap if not equal (Z=0)
//
// Opcode: 0x56FA (TRAPNE.W)
// Operand: $1234

$oMemory->writeLong(0x7000, 0x56FA_1234);     // TRAPNE.W #$1234
$oMemory->writeLong(0x7004, 0x4E75);          // RTS

echo "Code assembled at 0x7000: TRAPNE.W #\$1234\n";
echo "Conditional trap based on condition codes\n";
echo "16 condition codes available: T, F, HI, LS, CC, CS, NE, EQ, etc.\n\n";

// ============================================================================
// Summary
// ============================================================================

echo "=== Summary ===\n";
echo "The MC68020 emulator provides:\n";
echo "- 32-bit addressing (4GB address space)\n";
echo "- Extended arithmetic (32-bit multiply/divide)\n";
echo "- Bit field operations (8 instructions)\n";
echo "- Atomic operations (CAS/CAS2)\n";
echo "- Bounds checking (CHK2/CMP2)\n";
echo "- BCD pack/unpack (PACK/UNPK)\n";
echo "- Control registers (MOVEC)\n";
echo "- Enhanced flow control (TRAPcc, RTD, LINK.L)\n";
echo "- Coprocessor interface (F-line exceptions)\n";
echo "\nAll MC68000 code remains fully compatible!\n";
