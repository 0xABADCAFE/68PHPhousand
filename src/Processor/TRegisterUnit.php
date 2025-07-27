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

use ValueError;

/**
 * Trait for the main register set. Registers are explicitly named members
 * which allows us to enforce types for them.
 */
trait TRegisterUnit {

    protected int $iProgramCounter = 0;
    protected int $iStatusRegister = 0;
    protected int $iRegD0 = 0;
    protected int $iRegD1 = 0;
    protected int $iRegD2 = 0;
    protected int $iRegD3 = 0;
    protected int $iRegD4 = 0;
    protected int $iRegD5 = 0;
    protected int $iRegD6 = 0;
    protected int $iRegD7 = 0;
    protected int $iRegA0 = 0;
    protected int $iRegA1 = 0;
    protected int $iRegA2 = 0;
    protected int $iRegA3 = 0;
    protected int $iRegA4 = 0;
    protected int $iRegA5 = 0;
    protected int $iRegA6 = 0;
    protected int $iRegA7 = 0;

    // Indexable references to the registers.
    // These can be accessed by masked bits in instruction opcodes.

    protected array $aDataRegs = [];
    protected array $aAddrRegs = [];

    public function getPC(): int {
        return $this->iProgramCounter;
    }

    public function setPC(int $iAddress): self {
        assert(0 === ($iAddress & 1), new LogicException('Misaligned PC'));
        $this->iProgramCounter = $iAddress & 0xFFFFFFFF;
        return $this;
    }

    public function getDataName(string $sRegName): int {
        assert(isset($this->aDataRegs[$sRegName]), new ValueError('Illegal register name'));
        return $this->aDataRegs[$sRegName];
    }

    public function setDataName(string $sRegName, int $iValue): self {
        assert(isset($this->aDataRegs[$sRegName]), new ValueError('Illegal register name'));
        $this->aDataRegs[$sRegName] = $iValue & 0xFFFFFFFF;
        return $this;
    }

    public function getAddrName(string $sRegName): int {
        assert(isset($this->aAddrRegs[$sRegName]), new ValueError('Illegal register name'));
        return $this->aAddrRegs[$sRegName];
    }

    public function setAddrName(string $sRegName, int $iValue): self {
        assert(isset($this->aAddrRegs[$sRegName]), new ValueError('Illegal register name'));
        $this->aAddrRegs[$sRegName] = $iValue & 0xFFFFFFFF;
        return $this;
    }


    protected function initRegIndexes(): void {
        $this->aDataRegs = [
            IOpcode::REG_0 => &$this->iRegD0,
            IOpcode::REG_1 => &$this->iRegD1,
            IOpcode::REG_2 => &$this->iRegD2,
            IOpcode::REG_3 => &$this->iRegD3,
            IOpcode::REG_4 => &$this->iRegD4,
            IOpcode::REG_5 => &$this->iRegD5,
            IOpcode::REG_5 => &$this->iRegD6,
            IOpcode::REG_7 => &$this->iRegD7,
            IOpcode::REG_UP_D0 => &$this->iRegD0,
            IOpcode::REG_UP_D1 => &$this->iRegD1,
            IOpcode::REG_UP_D2 => &$this->iRegD2,
            IOpcode::REG_UP_D3 => &$this->iRegD3,
            IOpcode::REG_UP_D4 => &$this->iRegD4,
            IOpcode::REG_UP_D5 => &$this->iRegD5,
            IOpcode::REG_UP_D6 => &$this->iRegD6,
            IOpcode::REG_UP_D7 => &$this->iRegD7,
            'd0' => &$this->iRegD0,
            'd1' => &$this->iRegD1,
            'd2' => &$this->iRegD2,
            'd3' => &$this->iRegD3,
            'd4' => &$this->iRegD4,
            'd5' => &$this->iRegD5,
            'd6' => &$this->iRegD6,
            'd7' => &$this->iRegD7,
        ];
        $this->aAddrRegs = [
            IOpcode::REG_0 => &$this->iRegA0,
            IOpcode::REG_1 => &$this->iRegA1,
            IOpcode::REG_2 => &$this->iRegA2,
            IOpcode::REG_3 => &$this->iRegA3,
            IOpcode::REG_4 => &$this->iRegA4,
            IOpcode::REG_5 => &$this->iRegA5,
            IOpcode::REG_6 => &$this->iRegA6,
            IOpcode::REG_7 => &$this->iRegA7,
            IOpcode::REG_UP_A0 => &$this->iRegA0,
            IOpcode::REG_UP_A1 => &$this->iRegA1,
            IOpcode::REG_UP_A2 => &$this->iRegA2,
            IOpcode::REG_UP_A3 => &$this->iRegA3,
            IOpcode::REG_UP_A4 => &$this->iRegA4,
            IOpcode::REG_UP_A5 => &$this->iRegA5,
            IOpcode::REG_UP_A6 => &$this->iRegA6,
            IOpcode::REG_UP_A7 => &$this->iRegA7,
            'a0' => &$this->iRegA0,
            'a1' => &$this->iRegA1,
            'a2' => &$this->iRegA2,
            'a3' => &$this->iRegA3,
            'a4' => &$this->iRegA4,
            'a5' => &$this->iRegA5,
            'a6' => &$this->iRegA6,
            'a7' => &$this->iRegA7,
        ];
    }

    protected function registerReset(): void {
        $this->iProgramCounter = 0;
        $this->iStatusRegister = 0;
        foreach($this->aDataRegs as &$iReg) {
            $iReg = 0;
        }
        foreach($this->aAddrRegs as &$iReg) {
            $iReg = 0;
        }
    }
}
