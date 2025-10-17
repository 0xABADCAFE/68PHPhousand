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
 * Main Register unit
 */
trait TRegisterUnit
{
    // User regs
    protected int $iProgramCounter     = 0;
    protected int $iConditionRegister  = 0;
    protected DataRegisterSet    $oDataRegisters;
    protected AddressRegisterSet $oAddressRegisters;

    // Supervisor regs
    protected int $iStatusRegister             = 0;
    protected int $iUserStackPtrRegister       = 0;
    protected int $iSupervisorStackPtrRegister = 0;
    protected int $iVectorBaseRegister         = 0; // 68010+

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
        $this->aRegisterNames[IRegister::USP_NAME] = &$this->iUserStackPtrRegister;
        $this->aRegisterNames[IRegister::SSP_NAME] = &$this->iSupervisorStackPtrRegister;
        $this->aRegisterNames[IRegister::VBR_NAME] = &$this->iVectorBaseRegister;

        foreach (IRegister::ADDR_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oAddressRegisters->aIndex[$iIndex];
        }
        foreach (IRegister::DATA_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oDataRegisters->aIndex[$iIndex];
        }
    }

    protected function registerReset(): void
    {
        $this->iProgramCounter             = 0;
        $this->iStatusRegister             = 0;
        $this->iConditionRegister          = 0;
        $this->iUserStackPtrRegister       = 0;
        $this->iSupervisorStackPtrRegister = 0;
        $this->iVectorBaseRegister         = 0; // 68010+
        $this->oAddressRegisters->reset();
        $this->oDataRegisters->reset();
    }
}
