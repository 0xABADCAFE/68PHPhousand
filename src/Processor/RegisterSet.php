<?php
/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Processor;

/**
 * Basic set of 8 integer regs, for use as either a0-a7 or d0-d7
 *
 * Each register is a public int type to simplify access and maintain type safety.
 * There is also an array which indexes these by reference so that they can be accessed
 * by bitfield indexes in opcodes etc.
 */
class RegisterSet
{
    // Public or direct access within the processor logic
    public int $iReg0 = 0;
    public int $iReg1 = 0;
    public int $iReg2 = 0;
    public int $iReg3 = 0;
    public int $iReg4 = 0;
    public int $iReg5 = 0;
    public int $iReg6 = 0;
    public int $iReg7 = 0;

    /** @var array<int|string, int&> $aRegs */
    public array $aIndex = [];

    public function __construct()
    {
        $this->aIndex = [
            // Indexed by straight position
            IRegister::X0 => &$this->iReg0,
            IRegister::X1 => &$this->iReg1,
            IRegister::X2 => &$this->iReg2,
            IRegister::X3 => &$this->iReg3,
            IRegister::X4 => &$this->iReg4,
            IRegister::X5 => &$this->iReg5,
            IRegister::X6 => &$this->iReg6,
            IRegister::X7 => &$this->iReg7,

            // Indexed by upper opcode masked position
            IRegister::X0 << IOpcode::REG_UP_SHIFT => &$this->iReg0,
            IRegister::X1 << IOpcode::REG_UP_SHIFT => &$this->iReg1,
            IRegister::X2 << IOpcode::REG_UP_SHIFT => &$this->iReg2,
            IRegister::X3 << IOpcode::REG_UP_SHIFT => &$this->iReg3,
            IRegister::X4 << IOpcode::REG_UP_SHIFT => &$this->iReg4,
            IRegister::X5 << IOpcode::REG_UP_SHIFT => &$this->iReg5,
            IRegister::X6 << IOpcode::REG_UP_SHIFT => &$this->iReg6,
            IRegister::X7 << IOpcode::REG_UP_SHIFT => &$this->iReg7,
        ];
    }

    public function reset(): void
    {
        $this->iReg0 = 0;
        $this->iReg1 = 0;
        $this->iReg2 = 0;
        $this->iReg3 = 0;
        $this->iReg4 = 0;
        $this->iReg5 = 0;
        $this->iReg6 = 0;
        $this->iReg7 = 0;
    }
}
