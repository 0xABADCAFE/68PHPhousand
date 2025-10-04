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
                $this->oAddressRegisters,
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
        for ($iReg = IRegister::A0; $iReg <= IRegister::A6; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AIPI|$iReg] = new EAMode\Indirect\PostIncrement(
                $this->oAddressRegisters,
                $iReg,
                $this->oOutside
            );
        }

        // A7 (aka SP) is a special case here...
        $this->aSrcEAModes[IOpcode::LSB_EA_AIPI|IRegister::SP] = new EAMode\Indirect\PostIncrementSP(
            $this->oAddressRegisters,
            IRegister::SP,
            $this->oOutside
        );


        // Address Register indirect, pre decrement -(aN) [100 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A6; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AIPD|$iReg] = new EAMode\Indirect\PreDecrement(
                $this->oAddressRegisters,
                $iReg,
                $this->oOutside
            );
        }

        $this->aSrcEAModes[IOpcode::LSB_EA_AIPD|IRegister::SP] = new EAMode\Indirect\PreDecrementSP(
            $this->oAddressRegisters,
            IRegister::SP,
            $this->oOutside
        );


        // Address Register indirect with displacement d16(aN) [101 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AD|$iReg] = new EAMode\Indirect\Displacement(
                $this->iProgramCounter,
                $this->oAddressRegisters,
                $iReg,
                $this->oOutside
            );
        }

        // Address Register indirect with index d8(aN,xN.w|l) [110 nnn]
        for ($iReg = IRegister::A0; $iReg <= IRegister::A7; ++$iReg) {
            $this->aSrcEAModes[IOpcode::LSB_EA_AII|$iReg] = new EAMode\Indirect\Indexed(
                $this->iProgramCounter,
                $this->oAddressRegisters,
                $this->oDataRegisters,
                $iReg,
                $this->oOutside
            );
        }


        // TODO absolute modes

        // Abs short (xxx).w [111 000]
        $this->aSrcEAModes[IOpcode::LSB_EA_SHORT] = new EAMode\Indirect\AbsoluteShort(
            $this->iProgramCounter,
            $this->oOutside
        );

        // Abs long (xxx).l [111 001]
        $this->aSrcEAModes[IOpcode::LSB_EA_LONG]  = new EAMode\Indirect\AbsoluteLong(
            $this->iProgramCounter,
            $this->oOutside
        );

        // The current set of EA modes is common to both source and destination operands.
        // We split after this with some source only
        $this->aDstEAModes = $this->aSrcEAModes;

        // Read Only Modes

        // Program Counter with Displacement d16(pc)
        $this->aSrcEAModes[IOpcode::LSB_EA_PC_D] = new EAMode\Indirect\PCDisplacement(
            $this->iProgramCounter,
            $this->oOutside
        );

        // Program Counter with Index d8(pc,xN) [111 011]
        $this->aSrcEAModes[IOpcode::LSB_EA_PC_X] = new EAMode\Indirect\PCIndexed(
            $this->iProgramCounter,
            $this->oAddressRegisters,
            $this->oDataRegisters,
            $this->oOutside
        );

        // Immediate [111 100]
        $this->aSrcEAModes[IOpcode::LSB_EA_IMM] = new EAMode\Direct\Immediate(
            $this->iProgramCounter,
            $this->oOutside
        );

        // Add traps for unsupported modes
        for ($i = 0; $i < 64; ++$i) {
            if (!isset($this->aSrcEAModes[$i])) {
                $this->aSrcEAModes[$i] = new EAMode\Illegal(array_keys($this->aSrcEAModes), $i);
            }
            if (!isset($this->aDstEAModes[$i])) {
                $this->aDstEAModes[$i] = new EAMode\Illegal(array_keys($this->aDstEAModes), $i);
            }
        }
    }

}
