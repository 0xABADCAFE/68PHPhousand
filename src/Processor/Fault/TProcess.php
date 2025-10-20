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

/**
 * Mixin of helper logic for dealing with faults
 */
trait TProcess
{
    protected function prepareUserTrap(int $iTrapNumber)
    {
        assert(
            0 === ($iTrapNumber & ~0xF),
            new \LogicException('Invalid TRAP number')
        );


        $this->syncSupervisorState();

        // a7 is now SSP
        $this->oAddressRegisters->iReg7 -= ISize::LONG;
        $this->oOutside->writeLong(
            $this->oAddressRegisters->iReg7,
            $this->iProgramCounter
        );

        $this->oAddressRegisters->iReg7 -= ISize::WORD;
        $this->oOutside->writeWord(
            $this->oAddressRegisters->iReg7,
            ($this->iStatusRegister << 8) |
            ($this->iConditionRegister)
        );

        // Jump!
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + ($iTrapNumber << 2) + IVector::VOFS_TRAP_USER
        );
    }

    protected function prepareAddressError(Address $oFault, int $iPCAddress, int $iOpcode)
    {
        // TODO
        // PROTOTYPE - export all the logic to an appropriate helper
        $this->syncSupervisorState(); // Transition to supervisor mode

        // Populate exception frame

        // Faulting address
        $this->oOutside->writeLong(
            ($this->oAddressRegisters->iReg7 -= ISize::LONG),
            $iPCAddress
        );

        // Full status register
        $this->oOutside->writeWord(
            ($this->oAddressRegisters->iReg7 -= ISize::WORD),
            $this->iStatusRegister << 8 | $this->iConditionRegister
        );

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

        // TODO - populate it

        // Reload the PC from vector 0xC (AddressError), include VBR
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_ADDRESS_ERROR
        );
    }
}
