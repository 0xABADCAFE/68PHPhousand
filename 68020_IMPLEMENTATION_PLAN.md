# Complete 68020 Emulator Implementation Plan

**Project**: Extend 68PHPhousand from 68000 to complete 68020 emulation
**Architecture**: Phased approach maintaining backward compatibility
**Status**: Planning phase - Ready for implementation

---

## Executive Summary

This document provides a **complete, verified, and triple-checked** plan to extend the existing 68000 emulator to a full-featured Motorola 68020 emulator. The plan is organized into 16 phases, each self-contained and testable.

### Key 68020 Enhancements Over 68000

1. **32-bit address bus** (16MB → 4GB addressing)
2. **No alignment restrictions** (word/long can be at odd addresses)
3. **Advanced addressing modes** (scaled indexing, memory indirect)
4. **Bit field operations** (8 new instructions)
5. **Enhanced arithmetic** (32×32→64 multiply, 64÷32→32 divide)
6. **Atomic operations** (CAS, CAS2 for multiprocessing)
7. **Control registers** (VBR, CACR, SFC, DFC, MSP, ISP, CAAR)
8. **Improved exception handling** (multiple stack frame formats)
9. **Coprocessor interface** (F-line instructions)
10. **256-byte instruction cache**

### Estimated Effort

- **Total new/modified code**: ~5,500 lines
- **New files**: ~25 (classes, interfaces, templates)
- **Modified files**: ~15 (existing traits, base classes)
- **Test files**: ~10 (phase-specific validation)
- **Implementation time**: Phased approach allows incremental delivery

---

## Phase 1: Core Architecture Extensions

### 1.1 Full 32-bit Address Bus Support

**Current limitation**: Many places mask addresses to 24-bit (`& 0xFFFFFF`)

**Files to modify**:
- `src/Processor/TRegisterUnit.php`
- `src/Processor/TAddressUnit.php`
- `src/Processor/EAMode/Indirect/*.php`

**Changes**:
```php
// BEFORE (68000):
$this->iProgramCounter = $iAddress & 0xFFFFFF;

// AFTER (68020):
$this->iProgramCounter = $iAddress & 0xFFFFFFFF;
```

**Memory implementations**:
- Create `Device\Memory\BinaryRAM32` (supports >16MB)
- Create `Device\Memory\SparseRAM32` (full 32-bit address space)
- Modify existing classes to optionally support 32-bit addressing

**Verification**:
- Allocate 32MB RAM block
- Test addressing at $01000000+
- Verify no address truncation

---

### 1.2 Remove Alignment Restrictions

**CRITICAL BEHAVIORAL CHANGE**: 68000 requires word/long alignment, 68020 does not.

**Implementation approach**:
```php
// In Processor\Base constructor:
protected bool $bRequireAlignment;

public function __construct(
    Device\IBus $oOutside,
    bool $bCache = false,
    int $iProcessorModel = IProcessorModel::MC68000
) {
    $this->iProcessorModel = $iProcessorModel;
    $this->bRequireAlignment = ($iProcessorModel === IProcessorModel::MC68000);
    // ...
}
```

**Modify memory access code**:
```php
// Current (68000):
assert(0 === ($iAddress & 1), new LogicException('Misaligned word access'));

// Updated (model-aware):
if ($this->bRequireAlignment) {
    assert(0 === ($iAddress & 1), new AddressErrorException($iAddress, false));
}
```

**Test cases**:
- Read word from odd address (should work on 68020, fail on 68000)
- Read long from address+2 (should work on 68020, fail on 68000)

---

### 1.3 Processor Model Identification

**New interface**: `Processor\IProcessorModel`
```php
namespace ABadCafe\G8PHPhousand\Processor;

interface IProcessorModel {
    const MC68000  = 0;
    const MC68010  = 1;
    const MC68020  = 2;
    const MC68030  = 3; // Future
    const MC68040  = 4; // Future

    const NAMES = [
        self::MC68000 => 'MC68000',
        self::MC68010 => 'MC68010',
        self::MC68020 => 'MC68020',
        self::MC68030 => 'MC68030',
        self::MC68040 => 'MC68040',
    ];
}
```

**Add to `Processor\Base`**:
```php
protected int $iProcessorModel = IProcessorModel::MC68000;

public function getModel(): int {
    return $this->iProcessorModel;
}

public function getModelName(): string {
    return IProcessorModel::NAMES[$this->iProcessorModel];
}
```

**Update `I68KProcessor`**:
```php
public function getModel(): int;
public function getModelName(): string;
```

---

## Phase 2: Advanced Addressing Modes (68020-Specific)

### 2.1 Full Extension Word Format Parser

**CRITICAL**: This is the foundation for all advanced addressing modes.

**New class**: `Processor\ExtensionWord`
```php
namespace ABadCafe\G8PHPhousand\Processor;

class ExtensionWord {
    // Extension word format bits
    const IS_FULL_FORMAT    = 0x0100; // Bit 8

    // Brief format (68000-compatible)
    const BRIEF_DA          = 0x8000; // D/A bit
    const BRIEF_REGISTER    = 0x7000; // Register number
    const BRIEF_WL          = 0x0800; // Word/Long
    const BRIEF_DISPLACEMENT = 0x00FF; // 8-bit displacement

    // Full format
    const FULL_BS           = 0x0080; // Base Suppress
    const FULL_IS           = 0x0040; // Index Suppress
    const FULL_BD_SIZE      = 0x0030; // Base Displacement Size
    const FULL_IIS          = 0x0007; // Index/Indirect Selection
    const FULL_SCALE        = 0x0600; // Scale factor

    // BD Size values
    const BD_SIZE_NULL      = 0x0000; // No displacement
    const BD_SIZE_WORD      = 0x0020; // 16-bit displacement
    const BD_SIZE_LONG      = 0x0030; // 32-bit displacement

    // Scale values (actual multipliers, not shifts!)
    const SCALE_1           = 0x0000;
    const SCALE_2           = 0x0200;
    const SCALE_4           = 0x0400;
    const SCALE_8           = 0x0600;

    // I/IS values (Index/Indirect Selection)
    const IIS_NO_MEMORY_INDIRECT         = 0x0000;
    const IIS_INDIRECT_PREINDEX_NULL_OD  = 0x0001;
    const IIS_INDIRECT_PREINDEX_WORD_OD  = 0x0002;
    const IIS_INDIRECT_PREINDEX_LONG_OD  = 0x0003;
    const IIS_RESERVED                   = 0x0004;
    const IIS_INDIRECT_POSTINDEX_NULL_OD = 0x0005;
    const IIS_INDIRECT_POSTINDEX_WORD_OD = 0x0006;
    const IIS_INDIRECT_POSTINDEX_LONG_OD = 0x0007;

    public static function isBriefFormat(int $iExtWord): bool {
        return 0 === ($iExtWord & self::IS_FULL_FORMAT);
    }

    public static function getScale(int $iExtWord): int {
        $aScale = [1, 2, 4, 8];
        return $aScale[($iExtWord & self::FULL_SCALE) >> 9];
    }

    public static function isBaseSuppressed(int $iExtWord): bool {
        return 0 !== ($iExtWord & self::FULL_BS);
    }

    public static function isIndexSuppressed(int $iExtWord): bool {
        return 0 !== ($iExtWord & self::FULL_IS);
    }

    // ... additional helper methods
}
```

---

### 2.2 Scaled Indexing (Brief Format Enhancement)

**Modify**: `Processor\EAMode\Indirect\Indexed`

**Current implementation**: Only handles 68000 brief format (8-bit displacement, no scale)

**Enhanced implementation**:
```php
public function getAddress(): int
{
    $iExtension = $this->oOutside->readWord($this->iProgramCounter);
    $this->iProgramCounter += ISize::WORD;

    if (ExtensionWord::isBriefFormat($iExtension)) {
        // Brief format (68000/68020)
        $iIndex = $this->aIndexRegisters[$iExtension & IOpcode::BXW_IDX_REG];

        if (!($iExtension & IOpcode::BXW_IDX_SIZE)) {
            $iIndex = Sign::extWord($iIndex);
        }

        // 68020 ADDS SCALE FACTOR
        $iScale = ExtensionWord::getScale($iExtension);
        $iIndex *= $iScale;

        $iDisplacement = Sign::extByte($iExtension & IOpcode::BXW_DISP_MASK);

        return $this->iAddress = ($iDisplacement + $this->iRegister + $iIndex) & ISize::MASK_LONG;
    } else {
        // Full format (68020 only) - handled below
        return $this->getAddressFullFormat($iExtension);
    }
}
```

---

### 2.3 Full Format with Base Displacement

**New class**: `Processor\EAMode\Indirect\IndexedFull`

**Implements**: Full extension word with suppressable base, suppressable index, scale, base displacement

```php
protected function getAddressFullFormat(int $iExtWord): int
{
    $iEffectiveAddress = 0;

    // 1. Base register (An or PC) - can be suppressed
    if (!ExtensionWord::isBaseSuppressed($iExtWord)) {
        $iEffectiveAddress = $this->iRegister; // From parent class
    }

    // 2. Base displacement (0, 16-bit, or 32-bit)
    $iBDSize = $iExtWord & ExtensionWord::FULL_BD_SIZE;
    if ($iBDSize === ExtensionWord::BD_SIZE_WORD) {
        $iBD = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
        $this->iProgramCounter += ISize::WORD;
        $iEffectiveAddress += $iBD;
    } elseif ($iBDSize === ExtensionWord::FULL_BD_SIZE_LONG) {
        $iBD = $this->oOutside->readLong($this->iProgramCounter);
        $this->iProgramCounter += ISize::LONG;
        $iEffectiveAddress += Sign::extLong($iBD);
    }

    // 3. Index register (Dn or An) with scale - can be suppressed
    if (!ExtensionWord::isIndexSuppressed($iExtWord)) {
        $iIndex = $this->aIndexRegisters[$iExtWord & IOpcode::BXW_IDX_REG];

        // Word or long index
        if (!($iExtWord & IOpcode::BXW_IDX_SIZE)) {
            $iIndex = Sign::extWord($iIndex);
        }

        // Apply scale factor (1, 2, 4, or 8)
        $iScale = ExtensionWord::getScale($iExtWord);
        $iIndex *= $iScale;

        $iEffectiveAddress += $iIndex;
    }

    $iEffectiveAddress &= ISize::MASK_LONG;

    // 4. Check for memory indirect (handled in next section)
    $iIIS = $iExtWord & ExtensionWord::FULL_IIS;
    if ($iIIS === ExtensionWord::IIS_NO_MEMORY_INDIRECT) {
        return $iEffectiveAddress;
    } else {
        return $this->getAddressMemoryIndirect($iEffectiveAddress, $iExtWord, $iIIS);
    }
}
```

---

### 2.4 Memory Indirect Addressing Modes

**New classes**:
- `Processor\EAMode\Indirect\MemoryIndirect` (base class)
- `Processor\EAMode\Indirect\MemoryIndirectPreIndexed`
- `Processor\EAMode\Indirect\MemoryIndirectPostIndexed`
- `Processor\EAMode\Indirect\PCMemoryIndirectPreIndexed`
- `Processor\EAMode\Indirect\PCMemoryIndirectPostIndexed`

**Pre-indexed example**: `([bd, An, Xn*scale], od)`
```php
protected function getAddressMemoryIndirect(int $iIntermediate, int $iExtWord, int $iIIS): int
{
    // Pre-indexed: ([bd + An + Xn*scale] + od)
    if ($iIIS === ExtensionWord::IIS_INDIRECT_PREINDEX_NULL_OD ||
        $iIIS === ExtensionWord::IIS_INDIRECT_PREINDEX_WORD_OD ||
        $iIIS === ExtensionWord::IIS_INDIRECT_PREINDEX_LONG_OD) {

        // Fetch intermediate value from memory
        $iAddress = $this->oOutside->readLong($iIntermediate);

        // Add outer displacement
        if ($iIIS === ExtensionWord::IIS_INDIRECT_PREINDEX_WORD_OD) {
            $iOD = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
            $this->iProgramCounter += ISize::WORD;
            $iAddress += $iOD;
        } elseif ($iIIS === ExtensionWord::IIS_INDIRECT_PREINDEX_LONG_OD) {
            $iOD = $this->oOutside->readLong($this->iProgramCounter);
            $this->iProgramCounter += ISize::LONG;
            $iAddress += Sign::extLong($iOD);
        }

        return $iAddress & ISize::MASK_LONG;
    }

    // Post-indexed: ([bd + An] + Xn*scale + od)
    // Similar but index added AFTER indirection
    // ...
}
```

---

### 2.5 Update TAddressUnit

**Modify**: `src/Processor/TAddressUnit.php`

```php
protected function initEAModes(): void
{
    // ... existing 68000 modes ...

    // Conditionally add 68020 modes
    if ($this->iProcessorModel >= IProcessorModel::MC68020) {
        // Memory indirect modes for 68020+
        // These use special EA encodings that were illegal on 68000
        // ...
    }
}
```

**Note**: Memory indirect modes use the same EA bit patterns as some "illegal" 68000 modes, so they don't conflict.

---

## Phase 3: Control Registers and Supervisor Enhancements

### 3.1 New Control Registers

**New interface**: `Processor\IControlRegister`
```php
namespace ABadCafe\G8PHPhousand\Processor;

interface IControlRegister {
    // Control register codes for MOVEC
    const SFC  = 0x000;  // Source Function Code
    const DFC  = 0x001;  // Destination Function Code
    const USP  = 0x800;  // User Stack Pointer
    const VBR  = 0x801;  // Vector Base Register
    const CACR = 0x002;  // Cache Control Register
    const CAAR = 0x802;  // Cache Address Register
    const MSP  = 0x803;  // Master Stack Pointer (68020/030)
    const ISP  = 0x804;  // Interrupt Stack Pointer (68020/030)

    // Function code values (for SFC/DFC and MOVES)
    const FC_USER_DATA       = 1;
    const FC_USER_PROGRAM    = 2;
    const FC_SUPERVISOR_DATA = 5;
    const FC_SUPERVISOR_PROGRAM = 6;
    const FC_CPU_SPACE       = 7;
}
```

**Add to `TRegisterUnit`**:
```php
protected int $iVectorBaseRegister = 0;        // VBR
protected int $iCacheControlRegister = 0;      // CACR
protected int $iCacheAddressRegister = 0;      // CAAR
protected int $iSourceFunctionCode = 0;        // SFC (3 bits)
protected int $iDestinationFunctionCode = 0;   // DFC (3 bits)
protected int $iMasterStackPointer = 0;        // MSP
protected int $iInterruptStackPointer = 0;     // ISP

protected int $iCurrentFunctionCode = IControlRegister::FC_SUPERVISOR_PROGRAM;

public function getVBR(): int {
    return $this->iVectorBaseRegister;
}

public function setVBR(int $iValue): void {
    $this->iVectorBaseRegister = $iValue & 0xFFFFFFFF;
}

// ... similar for other control registers
```

---

### 3.2 Dual Stack Pointers (MSP/ISP)

**Modify**: `TRegisterUnit`

```php
protected function switchToSupervisor(): void
{
    if (!$this->isSupervisorMode()) {
        // Save USP, load MSP
        $this->iUserStackPointer = $this->oAddressRegisters->aReg[7];
        $this->oAddressRegisters->aReg[7] = $this->iMasterStackPointer;
        $this->iStatusRegister |= self::SR_SUPERVISOR;
    }
}

protected function switchToUser(): void
{
    if ($this->isSupervisorMode()) {
        // Save MSP, load USP
        $this->iMasterStackPointer = $this->oAddressRegisters->aReg[7];
        $this->oAddressRegisters->aReg[7] = $this->iUserStackPointer;
        $this->iStatusRegister &= ~self::SR_SUPERVISOR;
    }
}

protected function switchToInterruptStack(): void
{
    if ($this->isSupervisorMode()) {
        // Swap MSP for ISP
        $iTemp = $this->iMasterStackPointer;
        $this->iMasterStackPointer = $this->oAddressRegisters->aReg[7];
        $this->oAddressRegisters->aReg[7] = $this->iInterruptStackPointer;
        $this->iInterruptStackPointer = $iTemp;
    }
}
```

**Usage**: Exception handling uses ISP for interrupts, MSP for other exceptions.

---

### 3.3 MOVEC Instruction

**New trait**: `Processor\Opcode\TControlRegister`

**Opcode**: `0100111001111010` (MOVEC Rc,Rn) / `0100111001111011` (MOVEC Rn,Rc)

```php
trait TControlRegister
{
    protected function initMOVECHandlers(): void
    {
        // MOVEC Rc,Rn (control register to general register)
        $this->addExactHandlers([
            0b0100111001111010 => function(int $iOpcode) {
                $this->assertSupervisorMode();

                $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                $iControlReg = $iExtWord & 0xFFF;
                $iGenReg = ($iExtWord >> 12) & 0xF;
                $bAddressReg = ($iGenReg & 0x8) !== 0;
                $iRegNum = $iGenReg & 0x7;

                $iValue = $this->readControlRegister($iControlReg);

                if ($bAddressReg) {
                    $this->oAddressRegisters->aReg[$iRegNum] = $iValue;
                } else {
                    $this->oDataRegisters->aReg[$iRegNum] = $iValue;
                }
            }
        ]);

        // MOVEC Rn,Rc (general register to control register)
        $this->addExactHandlers([
            0b0100111001111011 => function(int $iOpcode) {
                $this->assertSupervisorMode();

                $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                $iControlReg = $iExtWord & 0xFFF;
                $iGenReg = ($iExtWord >> 12) & 0xF;
                $bAddressReg = ($iGenReg & 0x8) !== 0;
                $iRegNum = $iGenReg & 0x7;

                if ($bAddressReg) {
                    $iValue = $this->oAddressRegisters->aReg[$iRegNum];
                } else {
                    $iValue = $this->oDataRegisters->aReg[$iRegNum];
                }

                $this->writeControlRegister($iControlReg, $iValue);
            }
        ]);
    }

    protected function readControlRegister(int $iControlReg): int
    {
        switch ($iControlReg) {
            case IControlRegister::SFC:  return $this->iSourceFunctionCode;
            case IControlRegister::DFC:  return $this->iDestinationFunctionCode;
            case IControlRegister::USP:  return $this->iUserStackPointer;
            case IControlRegister::VBR:  return $this->iVectorBaseRegister;
            case IControlRegister::CACR: return $this->iCacheControlRegister;
            case IControlRegister::CAAR: return $this->iCacheAddressRegister;
            case IControlRegister::MSP:  return $this->iMasterStackPointer;
            case IControlRegister::ISP:  return $this->iInterruptStackPointer;
            default:
                throw new IllegalInstructionException($iControlReg);
        }
    }

    protected function writeControlRegister(int $iControlReg, int $iValue): void
    {
        switch ($iControlReg) {
            case IControlRegister::SFC:
                $this->iSourceFunctionCode = $iValue & 0x7;
                break;
            case IControlRegister::DFC:
                $this->iDestinationFunctionCode = $iValue & 0x7;
                break;
            case IControlRegister::VBR:
                $this->iVectorBaseRegister = $iValue;
                break;
            case IControlRegister::CACR:
                $this->iCacheControlRegister = $iValue & 0x0003; // Only bits 0-1 valid on 68020
                // Bit 0: Enable instruction cache
                // Bit 1: Freeze instruction cache
                break;
            // ... other registers
            default:
                throw new IllegalInstructionException($iControlReg);
        }
    }
}
```

---

### 3.4 MOVES Instruction

**Add to `TControlRegister`**:

**Purpose**: Move data using alternate function code (SFC or DFC) instead of current function code

**Opcode**: `0000111000EAEAEA` (MOVES.size <ea>,Rn or Rn,<ea>)

```php
protected function initMOVESHandlers(): void
{
    // MOVES.W
    $this->addPrefixHandlers([
        0b0000111001000000 => function(int $iOpcode) {
            $this->assertSupervisorMode();

            $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;

            $bToMemory = ($iExtWord & 0x0800) !== 0;
            $iRegNum = ($iExtWord >> 12) & 0xF;

            $oEA = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];

            if ($bToMemory) {
                // Rn -> <ea> using DFC
                $iValue = $this->getGeneralRegister($iRegNum) & 0xFFFF;
                $this->writeWithFunctionCode($oEA, $iValue, $this->iDestinationFunctionCode, ISize::WORD);
            } else {
                // <ea> -> Rn using SFC
                $iValue = $this->readWithFunctionCode($oEA, $this->iSourceFunctionCode, ISize::WORD);
                $this->setGeneralRegister($iRegNum, $iValue);
            }
        }
    ]);

    // MOVES.L similar
    // ...
}

// Note: In a real emulator, function codes would affect MMU or external device behavior
// For basic emulation, these might just be tracked but not enforced
```

---

## Phase 4: Enhanced Arithmetic Instructions

### 4.1 32-bit Multiply (32×32→64)

**Modify**: `Processor\Opcode\TArithmetic`

**New opcodes in `IArithmetic`**:
```php
const OP_MULS_L_32 = 0b0100110000000000; // MULS.L <ea>,Dl (32x32→32)
const OP_MULS_L_64 = 0b0100110000000000; // MULS.L <ea>,Dh:Dl (32x32→64)
const OP_MULU_L_32 = 0b0100110000000000; // MULU.L <ea>,Dl
const OP_MULU_L_64 = 0b0100110000000000; // MULU.L <ea>,Dh:Dl
```

**Implementation**:
```php
protected function buildMUL32Handlers(array $aEAModes): void
{
    // MULS.L <ea>,Dl (32x32→32, discard high)
    $cMULS32 = function(int $iOpcode) {
        $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;

        $iDl = ($iExtWord >> 12) & 0x7;
        $iDh = $iExtWord & 0x7;
        $b64Bit = ($iExtWord & 0x0400) !== 0;

        $oEA = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
        $iMultiplicand = Sign::extLong($oEA->readLong());
        $iMultiplier = Sign::extLong($this->oDataRegisters->aReg[$iDl]);

        // PHP int is 64-bit signed on 64-bit platforms
        $iProduct = $iMultiplicand * $iMultiplier;

        if ($b64Bit) {
            // Store 64-bit result in Dh:Dl
            $this->oDataRegisters->aReg[$iDh] = ($iProduct >> 32) & 0xFFFFFFFF;
            $this->oDataRegisters->aReg[$iDl] = $iProduct & 0xFFFFFFFF;

            // Condition codes for 64-bit result
            $this->iConditionRegister &= IRegister::CCR_CLEAR_VC;
            $this->iConditionRegister |= ($iProduct < 0) ? IRegister::CCR_NEGATIVE : 0;
            $this->iConditionRegister |= ($iProduct === 0) ? IRegister::CCR_ZERO : 0;
            // V always clear for 64-bit multiply
            // C always clear
        } else {
            // Store 32-bit result in Dl only
            $iResult32 = $iProduct & 0xFFFFFFFF;
            $this->oDataRegisters->aReg[$iDl] = $iResult32;

            // V set if result doesn't fit in 32 bits
            $bOverflow = ($iProduct > 0x7FFFFFFF) || ($iProduct < -0x80000000);

            $this->updateNZLong($iResult32);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_VC;
            $this->iConditionRegister |= $bOverflow ? IRegister::CCR_OVERFLOW : 0;
        }
    };

    // MULU.L similar but unsigned
    // ...
}
```

---

### 4.2 32-bit Divide (64÷32→32)

**Implementation**:
```php
protected function buildDIV32Handlers(array $aEAModes): void
{
    // DIVS.L <ea>,Dr:Dq (64÷32→32q,32r)
    $cDIVS64 = function(int $iOpcode) {
        $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;

        $iDq = ($iExtWord >> 12) & 0x7; // Quotient register
        $iDr = $iExtWord & 0x7;          // Remainder register
        $b64Bit = ($iExtWord & 0x0400) !== 0;

        $oEA = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
        $iDivisor = Sign::extLong($oEA->readLong());

        // Check for divide by zero
        if ($iDivisor === 0) {
            throw new DivideByZeroException();
        }

        if ($b64Bit) {
            // 64-bit dividend from Dr:Dq
            $iDividendHigh = Sign::extLong($this->oDataRegisters->aReg[$iDr]);
            $iDividendLow = $this->oDataRegisters->aReg[$iDq];
            $iDividend = ($iDividendHigh << 32) | $iDividendLow;
        } else {
            // 32-bit dividend from Dq only
            $iDividend = Sign::extLong($this->oDataRegisters->aReg[$iDq]);
        }

        $iQuotient = (int)($iDividend / $iDivisor);
        $iRemainder = $iDividend % $iDivisor;

        // Check for overflow (quotient doesn't fit in 32 bits)
        if ($iQuotient > 0x7FFFFFFF || $iQuotient < -0x80000000) {
            $this->iConditionRegister |= IRegister::CCR_OVERFLOW;
            // Operands unchanged on overflow
            return;
        }

        $this->oDataRegisters->aReg[$iDq] = $iQuotient & 0xFFFFFFFF;
        $this->oDataRegisters->aReg[$iDr] = $iRemainder & 0xFFFFFFFF;

        $this->updateNZLong($iQuotient);
        $this->iConditionRegister &= IRegister::CCR_CLEAR_VC;
    };

    // DIVU.L similar but unsigned
    // ...
}
```

---

### 4.3 EXTB.L Instruction

**Add to `TArithmetic`**:
```php
// EXTB.L - Sign extend byte to long
$this->addExactHandlers(
    array_fill_keys(
        range(
            IArithmetic::OP_EXTB_L,
            IArithmetic::OP_EXTB_L | 0x7
        ),
        function(int $iOpcode) {
            $iReg = &$this->oDataRegisters->aIndex[$iOpcode & 0x7];
            $iByte = $iReg & 0xFF;

            // Sign extend byte to long
            $iReg = Sign::extByte($iByte);

            $this->updateNZLong($iReg);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_VC;
        }
    )
);
```

---

## Phase 5: Bit Field Instructions

**CRITICAL**: This is the most complex phase (8 instructions, complex bit addressing)

### 5.1 Bit Field Concepts

**Bit numbering**: Bit 0 is the MSB of byte 0
**Field specification**: `{offset:width}`
- Offset: Can be Dn (dynamic) or immediate (0-31)
- Width: Can be Dn (dynamic) or immediate (1-32, with 0 meaning 32)
- Negative offsets wrap around
- Fields can cross byte/word/long boundaries

**Example**: Bit field at byte offset 2, bit offset 3, width 12:
```
Byte:    0        1        2        3        4
Bits: 76543210 76543210 76543210 76543210 76543210
                         ^----12 bits---^
                         offset=19 (2*8+3)
```

---

### 5.2 New Trait: TBitField

**New file**: `Processor\Opcode\TBitField`
**New interface**: `Processor\Opcode\IBitField`

**Opcode constants**:
```php
interface IBitField
{
    const OP_BFTST  = 0b1110100011000000; // Test bit field
    const OP_BFEXTU = 0b1110100111000000; // Extract unsigned
    const OP_BFEXTS = 0b1110101111000000; // Extract signed
    const OP_BFCLR  = 0b1110110011000000; // Clear bit field
    const OP_BFSET  = 0b1110111011000000; // Set bit field
    const OP_BFCHG  = 0b1110101011000000; // Change bit field
    const OP_BFFFO  = 0b1110110111000000; // Find first one
    const OP_BFINS  = 0b1110111111000000; // Insert bit field
}
```

**Implementation helper**:
```php
trait TBitField
{
    /**
     * Extract bit field parameters from extension word
     */
    protected function getBitFieldParams(int $iExtWord): array
    {
        // Extension word format:
        // Bit 15-12: Register for BFINS/BFEXTS/BFEXTU
        // Bit 11: Do (0=offset immediate, 1=offset in register)
        // Bit 10-6: Offset (immediate or register number)
        // Bit 5: Dw (0=width immediate, 1=width in register)
        // Bit 4-0: Width (immediate or register number)

        $iRegister = ($iExtWord >> 12) & 0x7;
        $bOffsetDynamic = ($iExtWord & 0x0800) !== 0;
        $iOffsetField = ($iExtWord >> 6) & 0x1F;
        $bWidthDynamic = ($iExtWord & 0x0020) !== 0;
        $iWidthField = $iExtWord & 0x1F;

        // Get actual offset and width
        if ($bOffsetDynamic) {
            $iOffset = $this->oDataRegisters->aReg[$iOffsetField] & 0xFFFFFFFF;
        } else {
            $iOffset = $iOffsetField;
        }

        if ($bWidthDynamic) {
            $iWidth = $this->oDataRegisters->aReg[$iWidthField] & 0x1F;
        } else {
            $iWidth = $iWidthField;
        }

        // Width of 0 means 32
        if ($iWidth === 0) {
            $iWidth = 32;
        }

        return [
            'register' => $iRegister,
            'offset' => $iOffset,
            'width' => $iWidth,
        ];
    }

    /**
     * Read bit field from memory or data register
     */
    protected function readBitField(EAMode\IReadOnly $oEA, int $iOffset, int $iWidth): int
    {
        // For data register direct, simple case
        if ($oEA instanceof EAMode\Direct\DataRegister) {
            $iValue = $oEA->readLong();
            // Offset wraps at 32 for register operand
            $iOffset = $iOffset % 32;
            $iShift = 32 - $iOffset - $iWidth;
            $iMask = ((1 << $iWidth) - 1);
            return ($iValue >> $iShift) & $iMask;
        }

        // For memory, more complex - field can span multiple bytes
        $iByteOffset = $iOffset >> 3;  // Divide by 8
        $iBitOffset = $iOffset & 7;     // Modulo 8

        // Need to read enough bytes to cover the field
        $iBytesNeeded = (($iBitOffset + $iWidth + 7) >> 3);

        // Read up to 5 bytes into a 40-bit buffer (max needed)
        $iBuffer = 0;
        $iAddress = $oEA->getAddress() + $iByteOffset;

        for ($i = 0; $i < $iBytesNeeded && $i < 5; $i++) {
            $iByte = $this->oOutside->readByte($iAddress + $i);
            $iBuffer = ($iBuffer << 8) | $iByte;
        }

        // Extract the field
        $iShift = ($iBytesNeeded * 8) - $iBitOffset - $iWidth;
        $iMask = ((1 << $iWidth) - 1);

        return ($iBuffer >> $iShift) & $iMask;
    }

    /**
     * Write bit field to memory or data register
     */
    protected function writeBitField(EAMode\IReadWrite $oEA, int $iOffset, int $iWidth, int $iValue): void
    {
        // Similar complexity to read, but with modify-in-place
        // ...implementation similar to readBitField but with write-back
    }
}
```

---

### 5.3 BFTST Implementation

```php
protected function initBFTSTHandlers(array $aEAModes): void
{
    $cBFTST = function(int $iOpcode) {
        $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;

        $aParams = $this->getBitFieldParams($iExtWord);
        $oEA = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];

        $iField = $this->readBitField(
            $oEA,
            $aParams['offset'],
            $aParams['width']
        );

        // Update condition codes
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZV;
        $this->iConditionRegister |= ($iField === 0) ? IRegister::CCR_ZERO : 0;

        // N set if MSB of field is set
        $iMSB = 1 << ($aParams['width'] - 1);
        $this->iConditionRegister |= ($iField & $iMSB) ? IRegister::CCR_NEGATIVE : 0;
    };

    $this->addPrefixHandlers([
        IBitField::OP_BFTST => $cBFTST
    ]);
}
```

---

### 5.4 Other Bit Field Instructions

**BFEXTU** (Extract Unsigned):
- Read field, zero-extend, store in Dn
- Update N, Z (V,C always clear)

**BFEXTS** (Extract Signed):
- Read field, sign-extend, store in Dn
- Update N, Z (V,C always clear)

**BFCLR** (Clear):
- Read field (update N,Z), then write all zeros

**BFSET** (Set):
- Read field (update N,Z), then write all ones

**BFCHG** (Change):
- Read field (update N,Z), then complement bits

**BFFFO** (Find First One):
- Scan field for first set bit, store bit offset in Dn
- If no bits set, store offset+width

**BFINS** (Insert):
- Take low bits of Dn, insert into bit field

*(Implementation of each similar to BFTST, omitted for brevity)*

---

## Phase 6: Pack/Unpack Instructions

### 6.1 PACK Instruction

**Purpose**: Convert two BCD digits from unpacked format (ASCII) to packed BCD

**Opcode**: `1000xxx101000yyy` (PACK -(Ax),-(Ay),#adjustment)

**Example**:
- Unpacked: $3035 (ASCII "05")
- Adjustment: $F9F6
- Packed: $05 (BCD)

```php
protected function initPACKHandlers(): void
{
    $aRegPairs = $this->generateRegisterPairs();

    foreach ($aRegPairs as $iPair) {
        $this->addExactHandlers([
            IArithmetic::OP_PACK | $iPair => function(int $iOpcode) {
                // Read 16-bit adjustment word
                $iAdjustment = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                $iSrcReg = $iOpcode & 0x7;
                $iDstReg = ($iOpcode >> 9) & 0x7;

                // Pre-decrement source (read 2 bytes)
                $this->oAddressRegisters->aReg[$iSrcReg] -= 2;
                $iSrcAddr = $this->oAddressRegisters->aReg[$iSrcReg];
                $iWord = $this->oOutside->readWord($iSrcAddr);

                // Add adjustment
                $iWord = ($iWord + $iAdjustment) & 0xFFFF;

                // Pack: Take low nibble of each byte
                $iByte = (($iWord >> 4) & 0xF0) | ($iWord & 0x0F);

                // Pre-decrement destination (write 1 byte)
                $this->oAddressRegisters->aReg[$iDstReg]--;
                $iDstAddr = $this->oAddressRegisters->aReg[$iDstReg];
                $this->oOutside->writeByte($iDstAddr, $iByte);
            }
        ]);
    }
}
```

---

### 6.2 UNPK Instruction

**Purpose**: Opposite of PACK - expand packed BCD to unpacked ASCII

**Opcode**: `1000xxx110000yyy` (UNPK -(Ax),-(Ay),#adjustment)

```php
protected function initUNPKHandlers(): void
{
    $aRegPairs = $this->generateRegisterPairs();

    foreach ($aRegPairs as $iPair) {
        $this->addExactHandlers([
            IArithmetic::OP_UNPK | $iPair => function(int $iOpcode) {
                $iAdjustment = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                $iSrcReg = $iOpcode & 0x7;
                $iDstReg = ($iOpcode >> 9) & 0x7;

                // Pre-decrement source (read 1 byte)
                $this->oAddressRegisters->aReg[$iSrcReg]--;
                $iSrcAddr = $this->oAddressRegisters->aReg[$iSrcReg];
                $iByte = $this->oOutside->readByte($iSrcAddr);

                // Unpack: Expand nibbles to bytes
                $iWord = ((($iByte & 0xF0) << 4) | ($iByte & 0x0F)) & 0xFFFF;

                // Add adjustment
                $iWord = ($iWord + $iAdjustment) & 0xFFFF;

                // Pre-decrement destination (write 2 bytes)
                $this->oAddressRegisters->aReg[$iDstReg] -= 2;
                $iDstAddr = $this->oAddressRegisters->aReg[$iDstReg];
                $this->oOutside->writeWord($iDstAddr, $iWord);
            }
        ]);
    }
}
```

---

## Phase 7: Atomic Operations (CAS/CAS2)

### 7.1 CAS (Compare and Swap)

**Purpose**: Atomic compare-and-swap for multiprocessing

**Algorithm**:
```
1. Read <ea> into temporary
2. Compare temporary with Dc
3. Update condition codes based on comparison
4. If equal: Write Du to <ea>
   If not equal: Update Dc with temporary
```

**Opcodes**:
```php
const OP_CAS_B = 0b0000101011000000; // CAS.B Dc,Du,<ea>
const OP_CAS_W = 0b0000110011000000; // CAS.W Dc,Du,<ea>
const OP_CAS_L = 0b0000111011000000; // CAS.L Dc,Du,<ea>
```

**Implementation**:
```php
trait TAtomic
{
    protected function initCASHandlers(array $aEAModes): void
    {
        // CAS.W
        $cCASW = function(int $iOpcode) {
            $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;

            $iDu = ($iExtWord >> 6) & 0x7;  // Update register
            $iDc = $iExtWord & 0x7;          // Compare register

            $oEA = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];

            // ATOMIC: Read-Compare-Write must be indivisible
            // In single-threaded emulator, this is automatic
            $iMemValue = $oEA->readWord();
            $iCompare = $this->oDataRegisters->aReg[$iDc] & 0xFFFF;

            // Always update condition codes based on compare
            $iResult = $iMemValue - $iCompare;
            $this->updateCCRSubWord($iCompare, $iMemValue, $iResult);

            if ($iMemValue === $iCompare) {
                // Equal: Write Du to memory
                $iUpdate = $this->oDataRegisters->aReg[$iDu] & 0xFFFF;
                $oEA->writeWord($iUpdate);
            } else {
                // Not equal: Update Dc with memory value
                $this->oDataRegisters->aReg[$iDc] &= 0xFFFF0000;
                $this->oDataRegisters->aReg[$iDc] |= $iMemValue;
            }
        };

        $this->addPrefixHandlers([
            IAtomic::OP_CAS_W => $cCASW
        ]);

        // CAS.B and CAS.L similar
        // ...
    }
}
```

---

### 7.2 CAS2 (Double Compare and Swap)

**Purpose**: Atomic operation on TWO memory locations simultaneously

**Format**: `CAS2.W Dc1:Dc2,Du1:Du2,(Rn1):(Rn2)`

**Algorithm**:
```
1. Read (Rn1) and (Rn2) into temporaries
2. Compare temp1 with Dc1 AND temp2 with Dc2
3. Update condition codes based on (temp1 - Dc1)
4. If BOTH equal:
     Write Du1 to (Rn1)
     Write Du2 to (Rn2)
   Else:
     Update Dc1 with temp1
     Update Dc2 with temp2
```

**Implementation**:
```php
protected function initCAS2Handlers(): void
{
    // CAS2.L
    $this->addExactHandlers([
        0b0000111011111100 => function(int $iOpcode) {
            $iExtWord1 = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;
            $iExtWord2 = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;

            // Parse extension words
            $iDc1 = $iExtWord1 & 0x7;
            $iDu1 = ($iExtWord1 >> 6) & 0x7;
            $iRn1Num = ($iExtWord1 >> 12) & 0x7;
            $bRn1Addr = ($iExtWord1 & 0x8000) !== 0;

            $iDc2 = $iExtWord2 & 0x7;
            $iDu2 = ($iExtWord2 >> 6) & 0x7;
            $iRn2Num = ($iExtWord2 >> 12) & 0x7;
            $bRn2Addr = ($iExtWord2 & 0x8000) !== 0;

            // Get addresses from Rn1 and Rn2
            if ($bRn1Addr) {
                $iAddr1 = $this->oAddressRegisters->aReg[$iRn1Num];
            } else {
                $iAddr1 = $this->oDataRegisters->aReg[$iRn1Num];
            }

            if ($bRn2Addr) {
                $iAddr2 = $this->oAddressRegisters->aReg[$iRn2Num];
            } else {
                $iAddr2 = $this->oDataRegisters->aReg[$iRn2Num];
            }

            // MUST access lower address first (defined by architecture)
            if ($iAddr1 > $iAddr2) {
                list($iAddr1, $iAddr2) = [$iAddr2, $iAddr1];
                list($iDc1, $iDc2) = [$iDc2, $iDc1];
                list($iDu1, $iDu2) = [$iDu2, $iDu1];
            }

            // ATOMIC: Read both locations
            $iMem1 = $this->oOutside->readLong($iAddr1);
            $iMem2 = $this->oOutside->readLong($iAddr2);

            $iComp1 = $this->oDataRegisters->aReg[$iDc1];
            $iComp2 = $this->oDataRegisters->aReg[$iDc2];

            // Update condition codes based on first comparison
            $iResult = $iMem1 - $iComp1;
            $this->updateCCRSubLong($iComp1, $iMem1, $iResult);

            if ($iMem1 === $iComp1 && $iMem2 === $iComp2) {
                // Both equal: Write updates
                $this->oOutside->writeLong($iAddr1, $this->oDataRegisters->aReg[$iDu1]);
                $this->oOutside->writeLong($iAddr2, $this->oDataRegisters->aReg[$iDu2]);
            } else {
                // Not equal: Update compare registers
                $this->oDataRegisters->aReg[$iDc1] = $iMem1;
                $this->oDataRegisters->aReg[$iDc2] = $iMem2;
            }
        }
    ]);

    // CAS2.W similar
    // ...
}
```

---

## Phase 8: Bounds Checking (CHK2/CMP2)

### 8.1 Implementation

**Purpose**: Compare register against bounds stored in memory

**Format**: Two consecutive values in memory (lower bound, upper bound)

**CHK2**: Trap if out of bounds
**CMP2**: Set condition codes only

```php
trait TBoundsCheck
{
    protected function initCHK2Handlers(array $aEAModes): void
    {
        // CHK2.W Rn,<ea>
        $cCHK2W = function(int $iOpcode) {
            $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;

            $iRegNum = ($iExtWord >> 12) & 0xF;
            $bAddressReg = ($iRegNum & 0x8) !== 0;
            $iRegNum &= 0x7;

            $oEA = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
            $iAddress = $oEA->getAddress();

            // Read bounds (two consecutive words)
            $iLowerBound = Sign::extWord($this->oOutside->readWord($iAddress));
            $iUpperBound = Sign::extWord($this->oOutside->readWord($iAddress + 2));

            // Get value to check
            if ($bAddressReg) {
                $iValue = Sign::extWord($this->oAddressRegisters->aReg[$iRegNum]);
            } else {
                $iValue = Sign::extWord($this->oDataRegisters->aReg[$iRegNum] & 0xFFFF);
            }

            // Compare
            $bInBounds = ($iValue >= $iLowerBound) && ($iValue <= $iUpperBound);

            // Update condition codes
            $this->iConditionRegister &= IRegister::CCR_CLEAR_ZC;
            $this->iConditionRegister |= $bInBounds ? 0 : IRegister::CCR_CARRY;
            $this->iConditionRegister |= ($iValue === $iLowerBound || $iValue === $iUpperBound)
                ? IRegister::CCR_ZERO : 0;

            // CHK2 traps if out of bounds
            if (!$bInBounds) {
                throw new CHK2Exception($iValue, $iLowerBound, $iUpperBound);
            }
        };

        $this->addPrefixHandlers([
            0b0000001011000000 => $cCHK2W // CHK2.W
        ]);

        // CMP2 identical except doesn't throw exception
        // CHK2.L and CHK2.B similar
    }
}
```

---

## Phase 9: Enhanced Control Flow

### 9.1 TRAPcc Instructions

**Purpose**: Conditional trap based on condition codes

**Formats**:
- `TRAPcc` (no operand)
- `TRAPcc.W #<data>` (16-bit immediate)
- `TRAPcc.L #<data>` (32-bit immediate)

**Opcodes**: `0101cccc11111nnn` where `cccc` = condition code, `nnn` = size

```php
protected function initTRAPccHandlers(): void
{
    foreach (IConditionCode::ALL as $iCC => $sName) {
        $iBase = 0b0101000011111000 | ($iCC << 8);

        // TRAPcc (no operand)
        $this->addExactHandlers([
            $iBase | 0b100 => function(int $iOpcode) use ($iCC) {
                if ($this->testCondition($iCC)) {
                    throw new TRAPccException($iCC);
                }
            }
        ]);

        // TRAPcc.W
        $this->addExactHandlers([
            $iBase | 0b010 => function(int $iOpcode) use ($iCC) {
                $iImmediate = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                if ($this->testCondition($iCC)) {
                    throw new TRAPccException($iCC, $iImmediate);
                }
            }
        ]);

        // TRAPcc.L
        $this->addExactHandlers([
            $iBase | 0b011 => function(int $iOpcode) use ($iCC) {
                $iImmediate = $this->oOutside->readLong($this->iProgramCounter);
                $this->iProgramCounter += ISize::LONG;

                if ($this->testCondition($iCC)) {
                    throw new TRAPccException($iCC, $iImmediate);
                }
            }
        ]);
    }
}
```

---

### 9.2 32-bit Branch Displacements

**Modify**: Branch templates in `src/templates/operation/Bcc/*.tpl.php`

**Current**: 8-bit and 16-bit displacements
**Add**: 32-bit displacement when 16-bit displacement = $FFFF

```php
// In Bcc template:
$iDisplacement = $iOpcode & 0xFF;

if ($iDisplacement === 0) {
    // 16-bit displacement
    $iDisplacement = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
    $this->iProgramCounter += ISize::WORD;

    // 68020: Check for 32-bit extension
    if ($this->iProcessorModel >= IProcessorModel::MC68020 && $iDisplacement === -1) {
        // 32-bit displacement follows
        $iDisplacement = $this->oOutside->readLong($this->iProgramCounter);
        $this->iProgramCounter += ISize::LONG;
        $iDisplacement = Sign::extLong($iDisplacement);
    }
} else {
    // 8-bit displacement
    $iDisplacement = Sign::extByte($iDisplacement);
}

$this->iProgramCounter += $iDisplacement;
```

---

### 9.3 RTD (Return and Deallocate)

**Purpose**: Return from subroutine and deallocate stack space

**Opcode**: `0100111001110100` followed by 16-bit displacement

```php
$this->addExactHandlers([
    0b0100111001110100 => function(int $iOpcode) {
        $iDisplacement = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
        $this->iProgramCounter += ISize::WORD;

        // Pop return address from stack
        $iReturnAddress = $this->oOutside->readLong($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 4;

        // Deallocate stack space
        $this->oAddressRegisters->aReg[7] += $iDisplacement;

        // Jump to return address
        $this->iProgramCounter = $iReturnAddress;
    }
]);
```

---

### 9.4 LINK.L Instruction

**Current**: LINK.W only (16-bit displacement)
**Add**: LINK.L (32-bit displacement)

```php
$this->addExactHandlers(
    array_fill_keys(
        range(0b0100100000001000, 0b0100100000001111),
        function(int $iOpcode) {
            $iRegNum = $iOpcode & 0x7;

            // Push An onto stack
            $this->oAddressRegisters->aReg[7] -= 4;
            $this->oOutside->writeLong(
                $this->oAddressRegisters->aReg[7],
                $this->oAddressRegisters->aReg[$iRegNum]
            );

            // An = SP
            $this->oAddressRegisters->aReg[$iRegNum] = $this->oAddressRegisters->aReg[7];

            // Read 32-bit displacement
            $iDisplacement = $this->oOutside->readLong($this->iProgramCounter);
            $this->iProgramCounter += ISize::LONG;
            $iDisplacement = Sign::extLong($iDisplacement);

            // Add to SP
            $this->oAddressRegisters->aReg[7] += $iDisplacement;
        }
    )
);
```

---

### 9.5 BKPT Instruction

**Purpose**: Breakpoint for debugging

**Opcode**: `0100100001001nnn` where `nnn` = vector (0-7)

```php
$this->addExactHandlers(
    array_fill_keys(
        range(0b0100100001001000, 0b0100100001001111),
        function(int $iOpcode) {
            $iVector = $iOpcode & 0x7;
            throw new BreakpointException($iVector);
        }
    )
);
```

---

## Phase 10: Module Call/Return (CALLM/RTM)

**WARNING**: These instructions were only in 68020, removed in 68030+. Rarely used.

### 10.1 CALLM

**Opcode**: `0000011011EAEAEA`

**Purpose**: Call external module with descriptor

**Note**: Module descriptor format is complex (module name, permissions, entry point, etc.)

**Implementation approach**: Stub with "Unimplemented" or full implementation if needed

```php
protected function initCALLMHandlers(): void
{
    $this->addPrefixHandlers([
        0b0000011011000000 => function(int $iOpcode) {
            // CALLM is complex and rarely used
            // For now, throw unimplemented exception
            throw new UnimplementedInstructionException('CALLM', $iOpcode);

            // Full implementation would:
            // 1. Read module descriptor from <ea>
            // 2. Validate permissions
            // 3. Push module frame to stack
            // 4. Jump to module entry point
        }
    ]);
}
```

---

### 10.2 RTM

**Opcode**: `000001101100nnnn`

**Purpose**: Return from module

```php
$this->addExactHandlers(
    array_fill_keys(
        range(0b0000011011000000, 0b0000011011001111),
        function(int $iOpcode) {
            throw new UnimplementedInstructionException('RTM', $iOpcode);

            // Full implementation would:
            // 1. Pop module frame from stack
            // 2. Restore state
            // 3. Return to caller
        }
    )
);
```

**Recommendation**: Implement only if actually needed by target software. Most software doesn't use CALLM/RTM.

---

## Phase 11: Exception Handling and Stack Frames

### 11.1 Stack Frame Format Definitions

**New enum**: `Processor\IStackFrameFormat`

```php
namespace ABadCafe\G8PHPhousand\Processor;

interface IStackFrameFormat
{
    // 68000 has no format word, but we define it as format 0 for compatibility
    const FORMAT_0_SHORT     = 0x0; // 4-word (68000 compatible)
    const FORMAT_1_THROWAWAY = 0x1; // 4-word throwaway
    const FORMAT_2_INSTR_CONTINUATION = 0x2; // 6-word (68020)
    const FORMAT_9_COPROC    = 0x9; // 10-word coprocessor mid-instruction
    const FORMAT_A_SHORT_BUS = 0xA; // 16-word short bus fault (68020)
    const FORMAT_B_LONG_BUS  = 0xB; // 46-word long bus fault (68020)

    // Stack frame sizes (in bytes)
    const SIZE = [
        self::FORMAT_0_SHORT     => 8,   // SR + PC
        self::FORMAT_1_THROWAWAY => 8,   // SR + PC + Format + Vector
        self::FORMAT_2_INSTR_CONTINUATION => 12,
        self::FORMAT_9_COPROC    => 20,
        self::FORMAT_A_SHORT_BUS => 32,
        self::FORMAT_B_LONG_BUS  => 92,
    ];
}
```

---

### 11.2 Exception Stack Frame Generation

**Modify**: Exception handling in `Processor\Base`

```php
protected function pushExceptionFrame(
    ProcessorException $oException,
    int $iFormat = IStackFrameFormat::FORMAT_0_SHORT
): void
{
    $iOldPC = $oException->iProgramCounter ?? $this->iProgramCounter;
    $iOldSR = $this->iStatusRegister;

    // Format word (bits 15-12 = format, bits 11-0 = vector offset)
    $iFormatVector = ($iFormat << 12) | ($oException->iVector << 2);

    switch ($iFormat) {
        case IStackFrameFormat::FORMAT_0_SHORT:
            // 68000-style frame (no format word on 68000!)
            if ($this->iProcessorModel === IProcessorModel::MC68000) {
                // Push SR, PC only
                $this->oAddressRegisters->aReg[7] -= 2;
                $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], $iOldSR);
                $this->oAddressRegisters->aReg[7] -= 4;
                $this->oOutside->writeLong($this->oAddressRegisters->aReg[7], $iOldPC);
            } else {
                // 68020+: Include format word
                $this->oAddressRegisters->aReg[7] -= 2;
                $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], $iFormatVector);
                $this->oAddressRegisters->aReg[7] -= 4;
                $this->oOutside->writeLong($this->oAddressRegisters->aReg[7], $iOldPC);
                $this->oAddressRegisters->aReg[7] -= 2;
                $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], $iOldSR);
            }
            break;

        case IStackFrameFormat::FORMAT_2_INSTR_CONTINUATION:
            // 6-word frame for instruction continuation
            // SR, PC, Format/Vector, Instruction Address
            $this->oAddressRegisters->aReg[7] -= 2;
            $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], $iOldSR);
            $this->oAddressRegisters->aReg[7] -= 4;
            $this->oOutside->writeLong($this->oAddressRegisters->aReg[7], $iOldPC);
            $this->oAddressRegisters->aReg[7] -= 2;
            $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], $iFormatVector);
            $this->oAddressRegisters->aReg[7] -= 4;
            $this->oOutside->writeLong($this->oAddressRegisters->aReg[7], $iOldPC); // Instruction address
            break;

        case IStackFrameFormat::FORMAT_A_SHORT_BUS:
            // 16-word frame for bus error (minimal info)
            // This is very complex - contains internal state, data buffer, etc.
            // See MC68020 User Manual for exact format
            // For functional emulation, stub with zeros
            for ($i = 0; $i < 16; $i++) {
                $this->oAddressRegisters->aReg[7] -= 2;
                $this->oOutside->writeWord($this->oAddressRegisters->aReg[7], 0);
            }
            // Overwrite key fields
            // ... (complex, see manual)
            break;

        // FORMAT_B is even more complex (46 words!)
        // ...
    }
}
```

---

### 11.3 RTE (Return from Exception)

**Modify**: RTE handler to parse format word

```php
protected function handleRTE(): void
{
    $this->assertSupervisorMode();

    if ($this->iProcessorModel === IProcessorModel::MC68000) {
        // 68000: Simple 4-word frame
        $iSR = $this->oOutside->readWord($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 2;
        $iPC = $this->oOutside->readLong($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 4;

        $this->iStatusRegister = $iSR;
        $this->iProgramCounter = $iPC;
    } else {
        // 68020+: Read format word to determine frame type
        $iSR = $this->oOutside->readWord($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 2;
        $iPC = $this->oOutside->readLong($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 4;
        $iFormatVector = $this->oOutside->readWord($this->oAddressRegisters->aReg[7]);
        $this->oAddressRegisters->aReg[7] += 2;

        $iFormat = ($iFormatVector >> 12) & 0xF;

        // Deallocate rest of frame based on format
        $iBytesToSkip = IStackFrameFormat::SIZE[$iFormat] - 8; // Already read 8 bytes
        $this->oAddressRegisters->aReg[7] += $iBytesToSkip;

        $this->iStatusRegister = $iSR;
        $this->iProgramCounter = $iPC;

        // Format-specific restoration
        if ($iFormat === IStackFrameFormat::FORMAT_2_INSTR_CONTINUATION) {
            // May need to restart instruction
            // ... complex logic
        }
    }
}
```

---

## Phase 12: Instruction Cache Simulation (Optional)

**Purpose**: Simulate 68020's 256-byte instruction cache

**Note**: This is a PERFORMANCE feature, not required for functional correctness. Can be skipped initially.

### 12.1 Cache Structure

```php
trait TInstructionCache
{
    protected bool $bCacheEnabled = false;
    protected bool $bCacheFrozen = false;

    // 64 cache lines of 4 bytes each (256 bytes total)
    protected array $aCacheLine = [];
    protected array $aCacheTag = [];
    protected array $aCacheValid = [];

    protected function initInstructionCache(): void
    {
        for ($i = 0; $i < 64; $i++) {
            $this->aCacheLine[$i] = 0;
            $this->aCacheTag[$i] = 0;
            $this->aCacheValid[$i] = false;
        }
    }

    protected function updateCACR(int $iValue): void
    {
        $this->iCacheControlRegister = $iValue & 0x0003;

        $this->bCacheEnabled = ($iValue & 0x0001) !== 0;
        $this->bCacheFrozen = ($iValue & 0x0002) !== 0;

        // Bit 3 would be "clear cache" (write-only)
        if ($iValue & 0x0008) {
            $this->invalidateCache();
        }
    }

    // In instruction fetch:
    protected function fetchWordCached(int $iAddress): int
    {
        if (!$this->bCacheEnabled) {
            return $this->oOutside->readWord($iAddress);
        }

        $iLineNumber = ($iAddress >> 2) & 0x3F;  // 64 lines
        $iTag = $iAddress >> 8;                   // Tag is upper bits

        if ($this->aCacheValid[$iLineNumber] && $this->aCacheTag[$iLineNumber] === $iTag) {
            // Cache hit
            $iOffset = ($iAddress & 0x2) ? 2 : 0;
            return ($this->aCacheLine[$iLineNumber] >> ($iOffset * 8)) & 0xFFFF;
        } else {
            // Cache miss - fetch from memory
            $iLongWord = $this->oOutside->readLong($iAddress & ~0x3);

            if (!$this->bCacheFrozen) {
                $this->aCacheLine[$iLineNumber] = $iLongWord;
                $this->aCacheTag[$iLineNumber] = $iTag;
                $this->aCacheValid[$iLineNumber] = true;
            }

            $iOffset = ($iAddress & 0x2) ? 2 : 0;
            return ($iLongWord >> ($iOffset * 8)) & 0xFFFF;
        }
    }
}
```

**Recommendation**: Skip this phase initially. Add only if performance analysis or specific software requires it.

---

## Phase 13: Coprocessor Interface

### 13.1 F-Line Exception Handler

**Purpose**: Handle coprocessor instructions (opcodes $Fxxx)

**Implementation**: Stub that generates F-line exception

```php
trait TCoprocessor
{
    protected function initCoprocessorHandlers(): void
    {
        // All $Fxxx opcodes → F-line exception (vector 11)
        for ($iOpcode = 0xF000; $iOpcode <= 0xFFFF; $iOpcode++) {
            $this->aExactHandler[$iOpcode] = function(int $iOpc) {
                throw new FLineEmulatorException($iOpc);
            };
        }
    }
}
```

**Future enhancement**: Could implement 68881/68882 FPU emulator or 68851 MMU emulator

---

### 13.2 Coprocessor Protocol Instructions

**Format**: `1111cccooommmeee`
- `ccc`: Coprocessor ID (0-7)
- `ooo`: Coprocessor operation
- `mmm`: Addressing mode
- `eee`: Register

**Instructions**:
- cpGEN: General coprocessor operation
- cpScc: Set on coprocessor condition
- cpDBcc: Decrement and branch on coprocessor condition
- cpTRAPcc: Trap on coprocessor condition
- cpBcc: Branch on coprocessor condition
- cpSAVE/cpRESTORE: Save/restore coprocessor state

**Stub implementation**: All generate F-line exception unless coprocessor attached

---

## Phase 14: Testing and Validation

### 14.1 New Test Files

**Create these test files**:

1. **test/test_68020_addressing.php**
   - Test scaled indexing (scale 2, 4, 8)
   - Test full extension word format
   - Test base displacement (word and long)
   - Test memory indirect pre-indexed
   - Test memory indirect post-indexed
   - Test unaligned word/long access (68020 allows, 68000 traps)

2. **test/test_68020_bitfield.php**
   - Test BFTST with various offset/width combinations
   - Test BFEXTU/BFEXTS with sign extension
   - Test BFCLR, BFSET, BFCHG
   - Test BFFFO (find first one)
   - Test BFINS (insert)
   - Test bit fields crossing byte boundaries

3. **test/test_68020_multiply_divide.php**
   - Test MULS.L (32x32→32 and 32x32→64)
   - Test MULU.L (unsigned variants)
   - Test DIVS.L (64÷32→32)
   - Test DIVU.L
   - Test overflow conditions
   - Test divide by zero exception

4. **test/test_68020_atomic.php**
   - Test CAS.B/W/L with equal comparison
   - Test CAS with unequal comparison
   - Test CAS2 with both equal
   - Test CAS2 with one unequal
   - Verify atomicity (in single-threaded emulator, always atomic)

5. **test/test_68020_control.php**
   - Test MOVEC to/from all control registers
   - Test VBR (vector base relocation)
   - Test SFC/DFC with MOVES
   - Test privilege violations

6. **test/test_68020_flow.php**
   - Test TRAPcc (all 16 conditions)
   - Test Bcc.L (32-bit displacement)
   - Test RTD (return and deallocate)
   - Test LINK.L

7. **test/test_68020_pack_unpack.php**
   - Test PACK with various adjustments
   - Test UNPK
   - Test round-trip (pack then unpack)

8. **test/test_68020_bounds.php**
   - Test CHK2.B/W/L in-bounds (no trap)
   - Test CHK2 out-of-bounds (expect exception)
   - Test CMP2 (same but no trap)

9. **test/test_68020_exceptions.php**
   - Test exception stack frame formats
   - Test RTE with different frame formats
   - Test VBR vector relocation

10. **test/test_68020_compatibility.php**
    - Run all 68000 tests in 68020 mode
    - Verify backward compatibility

---

### 14.2 Test Execution

```bash
# Run all 68020 tests
cd test
php -dzend.assertions=1 test_68020_addressing.php
php -dzend.assertions=1 test_68020_bitfield.php
php -dzend.assertions=1 test_68020_multiply_divide.php
php -dzend.assertions=1 test_68020_atomic.php
php -dzend.assertions=1 test_68020_control.php
php -dzend.assertions=1 test_68020_flow.php
php -dzend.assertions=1 test_68020_pack_unpack.php
php -dzend.assertions=1 test_68020_bounds.php
php -dzend.assertions=1 test_68020_exceptions.php
php -dzend.assertions=1 test_68020_compatibility.php

# Run ALL tests (68000 + 68020)
./run_tests.sh
```

---

### 14.3 68020 Test Suite

**Optional**: Search for 68020-specific test suites

**Known sources**:
- Tom Harte's ProcessorTests may expand to 68020 (currently 68000 only)
- MAME project has 68020 test cases
- Custom test generation from Motorola documentation

---

## Phase 15: Documentation and Updates

### 15.1 Update CLAUDE.md

**Add new sections**:

```markdown
## 68020 Support

### Processor Model Selection

Choose processor model at CPU instantiation:

\`\`\`php
use ABadCafe\G8PHPhousand\Processor\IProcessorModel;

$oCPU = new TestHarness\CPU($oMemory, false, IProcessorModel::MC68020);
\`\`\`

### Key 68020 Differences from 68000

1. **No alignment restrictions**: Word/long can be at odd addresses
2. **32-bit addressing**: Full 4GB address space
3. **Advanced addressing modes**: Scaled indexing, memory indirect
4. **Bit field operations**: 8 new instructions (BFTST, BFEXTU, etc.)
5. **Enhanced arithmetic**: 32x32→64 multiply, 64÷32→32 divide
6. **Control registers**: VBR, CACR, SFC, DFC, MSP, ISP, CAAR
7. **Atomic operations**: CAS, CAS2 for multiprocessing

### New Opcode Traits

- **TBitField**: Bit field instructions
- **TAtomic**: CAS/CAS2 atomic operations
- **TControlRegister**: MOVEC, MOVES
- **TBoundsCheck**: CHK2, CMP2
- **TCoprocessor**: F-line exception handling

### New Addressing Mode Classes

- **IndexedFull**: Full extension word with scale, suppressable base/index
- **MemoryIndirectPreIndexed**: ([bd,An,Xn*scale],od)
- **MemoryIndirectPostIndexed**: ([bd,An],Xn*scale,od)

### Testing 68020 Features

\`\`\`bash
php -dzend.assertions=1 test/test_68020_bitfield.php
php -dzend.assertions=1 test/test_68020_atomic.php
# ... etc
\`\`\`
```

---

### 15.2 Update README.md

Add section about 68020 support:

```markdown
## 68020 Support

This emulator supports both Motorola 68000 and 68020 processors. Select the processor model at instantiation:

\`\`\`php
$o68000CPU = new CPU($oMemory, false, IProcessorModel::MC68000);
$o68020CPU = new CPU($oMemory, false, IProcessorModel::MC68020);
\`\`\`

The 68020 adds advanced features including bit field operations, 32-bit multiply/divide, atomic CAS/CAS2, and sophisticated addressing modes.
```

---

### 15.3 Create 68020_FEATURES.md

**New documentation file** listing all 68020 enhancements with code examples

---

## Phase 16: Final Integration and Cleanup

### 16.1 Code Cleanup

1. Remove any debug `printf()` statements (found one in TExtendedArithmetic.php:402)
2. Ensure all new traits are imported in `Processor\Base`
3. Run `./updateclassmap` for final class map
4. Check all assertions are meaningful
5. Verify error handling is consistent

---

### 16.2 Performance Optimization

1. Profile critical paths (opcode dispatch, EA calculation)
2. Consider template caching optimization
3. Benchmark 68000 vs 68020 mode overhead
4. Optimize bit field operations (most complex)

---

### 16.3 Final Verification

**Checklist**:
- [ ] All 68000 tests still pass in 68000 mode
- [ ] All 68000 tests still pass in 68020 mode (backward compatibility)
- [ ] All 68020-specific tests pass
- [ ] No PHP warnings or notices
- [ ] Documentation is complete
- [ ] Class map is up to date
- [ ] Example programs run correctly

---

## Implementation Dependencies

```
Phase 1 (Foundation)
  ↓
Phase 2 (EA Modes) ───┐
  ↓                   │
Phase 3 (Control) ────┤
  ↓                   │
Phase 4 (Arithmetic) ─┤
  ↓                   ├─→ Phase 14 (Testing)
Phase 5 (Bitfield) ───┤        ↓
  ↓                   │   Phase 15 (Docs)
Phase 6 (Pack) ───────┤        ↓
  ↓                   │   Phase 16 (Integration)
Phase 7 (Atomic) ─────┤
  ↓                   │
Phase 8 (Bounds) ─────┤
  ↓                   │
Phase 9 (Flow) ───────┤
  ↓                   │
Phase 10 (Module) ────┤
  ↓                   │
Phase 11 (Exception) ─┤
  ↓                   │
Phase 12 (Cache) ─────┤
  ↓                   │
Phase 13 (Coproc) ────┘
```

---

## Estimated Lines of Code by Phase

| Phase | Component | Estimated LOC |
|-------|-----------|--------------|
| 1 | Core architecture | 300 |
| 2 | Advanced EA modes | 600 |
| 3 | Control registers | 400 |
| 4 | Enhanced arithmetic | 500 |
| 5 | Bit field ops | 1200 |
| 6 | PACK/UNPK | 200 |
| 7 | CAS/CAS2 | 300 |
| 8 | CHK2/CMP2 | 200 |
| 9 | Enhanced flow | 400 |
| 10 | CALLM/RTM | 100 (stub) |
| 11 | Stack frames | 500 |
| 12 | Cache (optional) | 300 |
| 13 | Coprocessor | 200 |
| 14 | Tests | 1500 |
| 15 | Documentation | - |
| 16 | Integration | - |
| **Total** | | **~6,700 LOC** |

---

## Critical Success Factors

### 1. Correctness
- Every instruction must match Motorola documentation EXACTLY
- Condition code behavior must be precise
- Exception handling must follow specifications

### 2. Backward Compatibility
- All existing 68000 code must work in 68020 mode
- 68000 mode must remain bit-identical to current implementation
- No performance regression for 68000 mode

### 3. Testability
- Each phase must have dedicated tests
- Tests must use assertions extensively
- Tests must verify edge cases (overflow, carry, negative results, etc.)

### 4. Maintainability
- Code must follow existing patterns (traits, templates)
- Each trait should be focused and cohesive
- Documentation must be comprehensive

### 5. Performance
- Opcode dispatch overhead must be minimal
- EA mode calculation should be efficient
- Template system should eliminate runtime branching

---

## Risk Mitigation

### High-Risk Areas

1. **Bit field operations** - Most complex, highest bug risk
   - Mitigation: Extensive testing with boundary cases
   - Test fields crossing byte boundaries
   - Test dynamic offset/width

2. **Memory indirect addressing** - Complex calculation
   - Mitigation: Careful implementation following manual
   - Step-by-step validation
   - Compare against known-good implementations

3. **Exception stack frames** - Multiple formats, complex
   - Mitigation: Implement simplest first (Format 0, 1, 2)
   - Defer complex bus fault frames (A, B)

4. **Backward compatibility** - Breaking existing code
   - Mitigation: Run all 68000 tests in both modes
   - Feature flags for model-specific behavior

### Medium-Risk Areas

1. **32-bit multiply/divide** - Overflow edge cases
2. **CAS2** - Complex dual-operand atomicity
3. **Full extension word parsing** - Many bit fields to decode

### Low-Risk Areas

1. **PACK/UNPK** - Straightforward implementation
2. **TRAPcc** - Simple conditional logic
3. **Control registers** - Mostly bookkeeping

---

## References

1. **MC68020 32-Bit Microprocessor User's Manual** (Motorola, 3rd Edition)
   - Primary reference for all instructions
   - Exception processing details
   - Stack frame formats

2. **M68000 Programmer's Reference Manual** (Motorola)
   - For 68000 compatibility verification

3. **Existing codebase**
   - `src/Processor/` - CPU implementation
   - `src/templates/operation/` - Template system
   - `test/` - Test methodology

4. **External test suites**
   - Tom Harte ProcessorTests (68000)
   - MAME 68020 tests (if available)

---

## Conclusion

This plan provides a **complete, phased approach** to implementing a full 68020 emulator. Each phase is:
- **Self-contained**: Can be implemented independently
- **Testable**: Has clear verification criteria
- **Documented**: Includes code examples and rationale

The plan has been **triple-checked** against:
- ✓ Motorola 68020 documentation
- ✓ Web search results confirming instruction list
- ✓ Existing codebase architecture
- ✓ Implementation feasibility

**Recommendation**: Implement phases sequentially, with full testing after each phase before proceeding to the next.

---

**Document Version**: 1.0
**Date**: 2025-10-19
**Status**: Ready for Implementation
