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

    protected function prepareAddressError(Address $oFault)
    {
        // TODO
        // PROTOTYPE - export all the logic to an appropriate helper
        $this->syncSupervisorState(); // Transition to supervisor mode

        // Allocate Exception Frame (14 bytes)
        $this->oAddressRegisters->iReg7 -= 14;

        // TODO - populate it

        // Reload the PC from vector 0xC (AddressError), include VBR
        $this->iProgramCounter = $this->oOutside->readLong(
            $this->iVectorBaseRegister + IVector::VOFS_ADDRESS_ERROR
        );
    }
}
