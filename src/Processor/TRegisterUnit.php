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

use LogicException;
use ValueError;

/**
 */
trait TRegisterUnit
{
    protected int $iProgramCounter    = 0;
    protected int $iStatusRegister    = 0;
    protected int $iConditionRegister = 0;

    protected AddressRegisterSet $oAddressRegisters;
    protected DataRegisterSet $oDataRegisters;

    protected array $aRegisterNames = [];

    public function getPC(): int
    {
        return $this->iProgramCounter;
    }

    public function setPC(int $iAddress): self
    {
        assert(0 === ($iAddress & 1), new LogicException('Misaligned PC'));
        $this->iProgramCounter = $iAddress & 0xFFFFFFFF;
        return $this;
    }

    public function getRegister(string $sRegName): int
    {
        assert(
            isset($this->aRegisterNames[$sRegName]),
            new ValueError('Illegal register name ' . $sRegName)
        );
        return $this->aRegisterNames[$sRegName];
    }

    public function setRegister(string $sRegName, int $iValue): self
    {
        assert(
            isset($this->aRegisterNames[$sRegName]),
            new ValueError('Illegal register name ' . $sRegName)
        );
        $this->aRegisterNames[$sRegName] = $iValue & 0xFFFFFFFF;
        return $this;
    }

    protected function initRegIndexes(): void
    {
        $this->oAddressRegisters = new AddressRegisterSet();
        $this->oDataRegisters    = new DataRegisterSet();
        $this->aRegisterNames[IRegister::PC_NAME]  = &$this->iProgramCounter;
        $this->aRegisterNames[IRegister::SR_NAME]  = &$this->iStatusRegister;
        $this->aRegisterNames[IRegister::CCR_NAME] = &$this->iConditionRegister;
        foreach (IRegister::ADDR_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oAddressRegisters->aIndex[$iIndex];
        }
        foreach (IRegister::DATA_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oDataRegisters->aIndex[$iIndex];
        }
    }

    protected function registerReset(): void
    {
        $this->iProgramCounter = 0;
        $this->iStatusRegister = 0;
        $this->oAddressRegisters->reset();
        $this->oDataRegisters->reset();
    }
}
