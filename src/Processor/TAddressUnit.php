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
trait TAddressUnit {

    use TRegisterUnit;

    /**
     * Effective Address targets for source and destination operands.
     *
     * When evaluating an EA from an opcode, one of these will be returned.
     * Generally
     */

    protected array $aSrcEAModes = [];
    protected array $aDstEAModes = [];

    protected function initEAModes(): void
    {
        $this->aSrcEAModes = [
            IOpcode::LSB_EA_D  => new EAMode\Direct\DataRegister(
                $this->oDataRegisters
            ),
            IOpcode::LSB_EA_A  => new EAMode\Direct\DataRegister(
                $this->oAddressRegisters
            ),
            IOpcode::LSB_EA_AI => new EAMode\Indirect\Basic(
                $this->oAddressRegisters,
                $this->oOutside
            ),
            IOpcode::LSB_EA_AIPI => new EAMode\Indirect\PostIncrement(
                $this->oAddressRegisters,
                $this->oOutside
            ),
            IOpcode::LSB_EA_AIPD => new EAMode\Indirect\PreDecrement(
                $this->oAddressRegisters,
                $this->oOutside
            ),

        ];
    }



    protected function decodeStandardSrcEAMode(int $iOpcode) {
        $iMode      = $iOpcode & IOpcode::MASK_EA_MODE;
        $iModeParam = $iOpcode & IOpcode::MASK_EA_REG; // Register number or special
    }

//     protected function decodeStandardIndirectEAMode(int $iOpcode): int {
//         $iMode      = $iOpcode & IOpcode::MASK_EA_MODE;
//         $iModeParam = $iOpcode & IOpocde::MASK_EA_REG;
//
//         // Expecting indirect modes only.
//         switch ($iMode) {
//             case IOpcode::LSB_EA_AI:
//                 return $this->aAddrRegs[$iModeParam];
//
//             case IOpcode::LSB_EA_AIPI:
//             case IOpcode::LSB_EA_AIPD:
//             case IOpcode::LSB_EA_AID:
//             case IOpcode::LSB_EA_AII:
//             case IOpcode::LSB_EA_D:
//         }
//     }

    protected static function generateDisplacement(int $iAddress, int $iDisplacement): int
    {
        return ($iAddress + $iDisplacement) & 0xFFFFFFFF;
    }

    protected static function generateBytePostInc(int& $iAddress): int
    {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 1) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateWordPostInc(int& $iAddress): int
    {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 2) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateLongPostInc(int& $iAddress): int
    {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 4) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateBytePreDec(int& $iAddress): int
    {
        $iAddress = ($iAddress - 1) & 0xFFFFFFFF;
        return $iAddress;
    }

    protected static function generateWordPreDec(int& $iAddress): int
    {
        $iAddress = ($iAddress - 2) & 0xFFFFFFFF;
        return $iAddress;
    }

    protected static function generateLongPreDec(int& $iAddress): int
    {
        $iAddress = ($iAddress - 4) & 0xFFFFFFFF;
        return $iAddress;
    }


}
