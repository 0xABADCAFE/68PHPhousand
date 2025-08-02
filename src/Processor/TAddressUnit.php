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
    protected EAMode\Bus $oSrcEAMemory;
    protected EAMode\Bus $oDstEAMemory;
    protected EAMode\Direct\DataRegister $oSrcEADataRegister;
    protected EAMode\Direct\DataRegister $oDstEADataRegister;
    protected EAMode\Direct\AddressRegister $oSrcEAAddressRegister;
    protected EAMode\Direct\AddressRegister $oDstEAAddressRegister;

    protected function initEAModes(): void
    {
        $this->oSrcEAMemory = new EAMode\Bus($this->oOutside);
        $this->oDstEAMemory = new EAMode\Bus($this->oOutside);
        $this->oSrcEADataRegister = new EAMode\Direct\DataRegister($this->oDataRegisters);
        $this->oDstEADataRegister = new EAMode\Direct\DataRegister($this->oDataRegisters);
        $this->oSrcEAAddressRegister = new EAMode\Direct\AddressRegister($this->oAddressRegisters);
        $this->oDstEAAddressRegister = new EAMode\Direct\AddressRegister($this->oAddressRegisters);
    }


    // TODO - Evolve EAMode into actual classes that do the complete EA decode
    //        and just select them based on the fields. That will allow the size
    //        of the operand to be implied by the read/write operation that is
    //        performed on them later.

    protected function decodeStandardSrcEAMode(int $iOpcode): EAMode\IReadable {
        $iMode      = $iOpcode & IOpcode::MASK_EA_MODE;
        $iModeParam = $iOpcode & IOpocde::MASK_EA_REG; // Register number or special

        switch ($iMode) {

            case IOpcode::LSB_EA_D:
                // Data register direct
                $this->oSrcEADataRegister->bind($iModeParam);
                return $this->oSrcEADataRegister;

            case IOpcode::LSB_EA_A:
                // Address register direct
                $this->oSrcEAAddressRegister->bind($iModeParam);
                return $this->oSrcEAAddressRegister;

            case IOpcode::LSB_EA_AI:
                // Address register indirect.
                $iAddress = $this->oAddressRegisters->aIndex[$iModeParam];
                $this->oSrcEAMemory->bind($iAddress);
                return $this->oSrcEAMemory;

            case IOpcode::LSB_EA_AIPI:

                return $this->oSrcEAMemory;

        }
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
