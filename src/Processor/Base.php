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

use ABadCafe\G8PHPhousand\IDevice;

use ABadCafe\G8PHPhousand\Device;

/**
 * Base class implementation
 */
abstract class Base implements IDevice {

    protected Device\IBus $oOutside;

    protected int $iProgramCounter = 0;
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

    // Indexable references to the registers
    protected array $aDataRegs = [];
    protected array $aAddrRegs = [];

    public function __construct(Device\IBus $oOutside) {
        $this->oOutside  = $oOutside;
        $this->initRegIndexes();
        $this->softReset();
    }

    public function softReset(): self {
        $this->internalReset();
        $this->oOutside->softReset();
        return $this;
    }

    public function hardReset(): self {
        $this->internalReset();
        $this->oOutside->hardReset();
        return $this;
    }

    public function getPC(): int {
        return $this->iProgramCounter;
    }

    public function getD0(): int {
        return $this->iRegD0;
    }

    public function getD1(): int {
        return $this->iRegD1;
    }

    public function getD2(): int {
        return $this->iRegD2;
    }

    public function getD3(): int {
        return $this->iRegD3;
    }

    public function getD4(): int {
        return $this->iRegD4;
    }

    public function getD5(): int {
        return $this->iRegD5;
    }

    public function getD6(): int {
        return $this->iRegD6;
    }

    public function getD7(): int {
        return $this->iRegD7;
    }

    public function getA0(): int {
        return $this->iRegA0;
    }

    public function getA1(): int {
        return $this->iRegA1;
    }

    public function getA2(): int {
        return $this->iRegA2;
    }

    public function getA3(): int {
        return $this->iRegA3;
    }

    public function getA4(): int {
        return $this->iRegA4;
    }

    public function getA5(): int {
        return $this->iRegA5;
    }

    public function getA6(): int {
        return $this->iRegA6;
    }

    public function getA7(): int {
        return $this->iRegA7;
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
        ];
    }

    protected function internalReset(): void {
        $this->iProgramCounter = 0;
        foreach($this->aDataRegs as &$iReg) {
            $iReg = 0;
        }
        foreach($this->aAddrRegs as &$iReg) {
            $iReg = 0;
        }
    }
}
