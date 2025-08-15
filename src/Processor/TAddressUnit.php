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

/**
 * Trait for effective address calculations
 */
trait TAddressUnit
{
    use TRegisterUnit;

    /**
     * Effective Address targets for source and destination operands.
     *
     * When evaluating an EA from an opcode, one of these will be returned.
     * Generally
     */

    /** @var array<int, EAMode\IReadOnly> */
    protected array $aSrcEAModes = [];

    /** @var array<int, EAMode\IReadWrite> */
    protected array $aDstEAModes = [];

    protected function initEAModes(): void
    {
        $this->aSrcEAModes = [];
        $this->aDstEAModes = [];

        // Data Register Direct dN [000 nnn]
        for ($iReg = IRegister::D0; $iReg <= IRegister::D7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_D|$iReg] = new EAMode\Direct\DataRegister(
                $this->oDataRegisters,
                $iReg
            );
        }

        // Address Register direct aN [001 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_A|$iReg] = new EAMode\Direct\AddressRegister(
                $this->oDataRegisters,
                $iReg
            );
        }

        // Address Register indirect (aN) [010 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AI|$iReg] = new EAMode\Indirect\Basic(
                $this->oAddressRegisters,
                $iReg,
                $this->oOutside
            );
        }


        // Address Register indirect, post increment (aN)+ [011 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AIPI|$iReg] = new EAMode\Indirect\PostIncrement(
                $this->oDataRegisters,
                $iReg,
                $this->oOutside
            );
        }

        // Address Register indirect, pre decrement -(aN) [100 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AIPD|$iReg] = new EAMode\Indirect\PreDecrement(
                $this->oDataRegisters,
                $iReg,
                $this->oOutside
            );
        }

        // Address Register indirect with displacement d16(aN) [101 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AD|$iReg] = new EAMode\Indirect\Displacement(
                $this->iProgramCounter,
                $this->oDataRegisters,
                $iReg,
                $this->oOutside
            );
        }

        // Address Register indirect with index d8(aN,xN.w|l) [110 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AD|$iReg] = new EAMode\Indirect\Indexed(
                $this->iProgramCounter,
                $this->oDataRegisters,
                $this->oDataRegisters,
                $iReg,
                $this->oOutside
            );
        }


        // TODO absolute modes

        // Abs short (xxx).w [111 000]
        // Abs long (xxx).l [111 001]


        // The current set of EA modes is common to both source and destination operands.
        // We split after this with some source only
        $this->aDstEAModes = $this->aSrcEAModes;


        // TODO Special source only modes next

        // Program Counter with Displacement d16(pc) [111 010]
        // Program Counter with Index d8(pc,xN) [111 011]

        // Immediate [111 100]
        $this->aSrcEAModes[IOpcode::LSB_EA_IMM] = new EAMode\Direct\Immediate(
            $this->iProgramCounter,
            $this->oOutside
        );
    }

}
