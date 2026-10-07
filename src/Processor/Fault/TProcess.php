<?php

/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 *
 *
 * TODO - Everywhere where the CCR is modified sequentially, we need to ensure that
 * any operands have been fetched since access may trigger an access error. The CCR
 * must be unaffected.
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Processor\Fault;

use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IVector;

/**
 * Mixin of helper logic for dealing with faults
 */
trait TProcess
{

    protected function beginStackFrame(int $iProgramCounter)
    {
        if ($this->iStatusRegister & IRegister::SR_MASK_SUPER) {
            $iSSP = &$this->oAddressRegisters->iReg7;
        } else {
            $iSSP = &$this->iSupervisorStackPtrRegister;
        }

        $iSSP -= ISize::LONG;
        $this->oOutside->writeLong(
            $iSSP,
            $iProgramCounter
        );

        $iSSP -= ISize::WORD;
        $this->oOutside->writeWord(
            $iSSP,
            ($this->iStatusRegister << 8) |
            ($this->iConditionRegister)
        );
    }

    protected function processPrivilegeViolation()
    {
        $this->beginStackFrame($this->iProgramCounter);
        $this->syncSupervisorState();
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_PRIVILEGE_VIOLATION
        );
    }

    protected function processZeroDivideError()
    {
        $this->iConditionRegister &= IRegister::CCR_EXTEND;
        $this->beginStackFrame($this->iProgramCounter);

        $this->syncSupervisorState();
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_INTEGER_DIVIDE_BY_ZERO
        );
    }

    protected function prepareUserTrap(int $iTrapNumber)
    {
        assert(
            0 === ($iTrapNumber & ~0xF),
            new \LogicException('Invalid TRAP number')
        );
        $this->beginStackFrame($this->iProgramCounter);
        $this->syncSupervisorState();
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + ($iTrapNumber << 2) + IVector::VOFS_TRAP_USER
        );
    }

    /**
     * TODO - validate the expected frame format
     */
    protected function processAccessError(Access $oFault, int $iPCAddress, int $iOpcode)
    {
        $this->beginStackFrame($iPCAddress);
        $this->syncSupervisorState(); // Transition to supervisor mode

        // Extended frame data

        // Faulting instruction
        $this->oOutside->writeWord(
            ($this->oAddressRegisters->iReg7 -= ISize::WORD),
            $iOpcode
        );

        // Access address
        $this->oOutside->writeLong(
            ($this->oAddressRegisters->iReg7 -= ISize::LONG),
            $oFault->iAddress
        );

        // Allocate Exception Frame (14 bytes)
        $this->oAddressRegisters->iReg7 -= 2;

        // TODO - populate it with the remaining

        // Reload the PC from vector 0xC (AccessError), include VBR
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_ACCESS_FAULT
        );
    }

    protected function processAddressError(Address $oFault, int $iPCAddress, int $iOpcode)
    {
        $this->beginStackFrame($iPCAddress);
        $this->syncSupervisorState(); // Transition to supervisor mode

        // Extended frame data

        // Faulting instruction
        $this->oOutside->writeWord(
            ($this->oAddressRegisters->iReg7 -= ISize::WORD),
            $iOpcode
        );

        // Access address
        $this->oOutside->writeLong(
            ($this->oAddressRegisters->iReg7 -= ISize::LONG),
            $oFault->iAddress
        );

        // Allocate Exception Frame (14 bytes)
        $this->oAddressRegisters->iReg7 -= 2;

        // TODO - populate it with the remaining

        // Reload the PC from vector 0xC (AddressError), include VBR
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_ADDRESS_ERROR
        );
    }
}
