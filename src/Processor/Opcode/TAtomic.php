<?php

/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Processor\Opcode;
use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\IRegister;

use LogicException;

/**
 * TAtomic
 *
 * Implements atomic compare-and-swap operations for 68020+
 *
 * Instructions:
 *   - CAS (Compare and Swap) - byte, word, long
 *   - CAS2 (Double Compare and Swap) - word, long
 */
trait TAtomic
{
    /**
     * Initialize CAS and CAS2 instruction handlers
     */
    private function initAtomicHandlers(): void
    {
        // Get data alterable EA modes for CAS
        $aEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_DATA_ALTERABLE);

        // CAS.B
        $this->buildCASHandlers(IAtomic::OP_CAS_B, $aEAModes, ISize::BYTE);

        // CAS.W
        $this->buildCASHandlers(IAtomic::OP_CAS_W, $aEAModes, ISize::WORD);

        // CAS.L
        $this->buildCASHandlers(IAtomic::OP_CAS_L, $aEAModes, ISize::LONG);

        // CAS2.W
        $this->aExactHandler[IAtomic::OP_CAS2_W] = $this->buildCAS2Handler(ISize::WORD);

        // CAS2.L
        $this->aExactHandler[IAtomic::OP_CAS2_L] = $this->buildCAS2Handler(ISize::LONG);
    }

    /**
     * Build CAS handlers for a specific size
     */
    private function buildCASHandlers(int $iOpcode, array $aEAModes, int $iSize): void
    {
        foreach ($aEAModes as $iEAMode) {
            $iFullOpcode = $iOpcode | $iEAMode;

            $this->aExactHandler[$iFullOpcode] = function(int $iOpcode) use ($iSize) {
                // Read extension word
                $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;

                // Extract registers from extension word
                $iDc = ($iExtWord & 0x7);        // Compare operand (bits 2-0)
                $iDu = ($iExtWord >> 6) & 0x7;   // Update operand (bits 8-6)

                // Get EA mode
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];

                // Read memory value
                $iMemValue = match($iSize) {
                    ISize::BYTE => $oEAMode->readByte(),
                    ISize::WORD => $oEAMode->readWord(),
                    ISize::LONG => $oEAMode->readLong(),
                };

                // Get compare value from Dc
                $iCompareValue = $this->oDataRegisters->aIndex[$iDc] & match($iSize) {
                    ISize::BYTE => ISize::MASK_BYTE,
                    ISize::WORD => ISize::MASK_WORD,
                    ISize::LONG => ISize::MASK_LONG,
                };

                // Compare memory value with Dc
                if ($iMemValue === $iCompareValue) {
                    // Values match - write Du to memory
                    $iUpdateValue = $this->oDataRegisters->aIndex[$iDu] & match($iSize) {
                        ISize::BYTE => ISize::MASK_BYTE,
                        ISize::WORD => ISize::MASK_WORD,
                        ISize::LONG => ISize::MASK_LONG,
                    };

                    match($iSize) {
                        ISize::BYTE => $oEAMode->writeByte($iUpdateValue),
                        ISize::WORD => $oEAMode->writeWord($iUpdateValue),
                        ISize::LONG => $oEAMode->writeLong($iUpdateValue),
                    };

                    // Set Z flag (values matched)
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_NV;
                    $this->iConditionRegister |= IRegister::CCR_ZERO;
                } else {
                    // Values don't match - write memory value to Dc
                    if ($iSize === ISize::BYTE) {
                        $this->oDataRegisters->aIndex[$iDc] =
                            ($this->oDataRegisters->aIndex[$iDc] & 0xFFFFFF00) | $iMemValue;
                    } else if ($iSize === ISize::WORD) {
                        $this->oDataRegisters->aIndex[$iDc] =
                            ($this->oDataRegisters->aIndex[$iDc] & 0xFFFF0000) | $iMemValue;
                    } else {
                        $this->oDataRegisters->aIndex[$iDc] = $iMemValue;
                    }

                    // Update condition codes based on memory value - compare
                    match($iSize) {
                        ISize::BYTE => $this->updateNZByte($iMemValue),
                        ISize::WORD => $this->updateNZWord($iMemValue),
                        ISize::LONG => $this->updateNZLong($iMemValue),
                    };
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_V;
                }

                // C always clear
                $this->iConditionRegister &= IRegister::CCR_CLEAR_C;

                return 12; // Approximate cycle count
            };
        }
    }

    /**
     * Build CAS2 handler for a specific size
     */
    private function buildCAS2Handler(int $iSize): callable
    {
        return function(int $iOpcode) use ($iSize) {
            // Read extension words (CAS2 uses two extension words)
            $iExtWord1 = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;
            $iExtWord2 = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;

            // Parse first extension word
            $iDc1 = ($iExtWord1 & 0x7);          // Compare operand 1 (bits 2-0)
            $iDu1 = ($iExtWord1 >> 6) & 0x7;     // Update operand 1 (bits 8-6)
            $bRn1IsAddress = 0 !== ($iExtWord1 & 0x8000);  // D/A bit (bit 15)
            $iRn1 = ($iExtWord1 >> 12) & 0x7;    // Register number 1 (bits 14-12)

            // Parse second extension word
            $iDc2 = ($iExtWord2 & 0x7);          // Compare operand 2
            $iDu2 = ($iExtWord2 >> 6) & 0x7;     // Update operand 2
            $bRn2IsAddress = 0 !== ($iExtWord2 & 0x8000);
            $iRn2 = ($iExtWord2 >> 12) & 0x7;    // Register number 2

            // Get addresses from Rn1 and Rn2
            $iAddr1 = $bRn1IsAddress
                ? $this->oAddressRegisters->aIndex[$iRn1]
                : $this->oDataRegisters->aIndex[$iRn1];

            $iAddr2 = $bRn2IsAddress
                ? $this->oAddressRegisters->aIndex[$iRn2]
                : $this->oDataRegisters->aIndex[$iRn2];

            // Read memory values
            $iMemValue1 = match($iSize) {
                ISize::WORD => $this->oOutside->readWord($iAddr1),
                ISize::LONG => $this->oOutside->readLong($iAddr1),
            };

            $iMemValue2 = match($iSize) {
                ISize::WORD => $this->oOutside->readWord($iAddr2),
                ISize::LONG => $this->oOutside->readLong($iAddr2),
            };

            // Get compare values
            $iCompareValue1 = $this->oDataRegisters->aIndex[$iDc1] & match($iSize) {
                ISize::WORD => ISize::MASK_WORD,
                ISize::LONG => ISize::MASK_LONG,
            };

            $iCompareValue2 = $this->oDataRegisters->aIndex[$iDc2] & match($iSize) {
                ISize::WORD => ISize::MASK_WORD,
                ISize::LONG => ISize::MASK_LONG,
            };

            // Compare both values
            if ($iMemValue1 === $iCompareValue1 && $iMemValue2 === $iCompareValue2) {
                // Both match - write Du1:Du2 to memory
                $iUpdateValue1 = $this->oDataRegisters->aIndex[$iDu1] & match($iSize) {
                    ISize::WORD => ISize::MASK_WORD,
                    ISize::LONG => ISize::MASK_LONG,
                };

                $iUpdateValue2 = $this->oDataRegisters->aIndex[$iDu2] & match($iSize) {
                    ISize::WORD => ISize::MASK_WORD,
                    ISize::LONG => ISize::MASK_LONG,
                };

                match($iSize) {
                    ISize::WORD => $this->oOutside->writeWord($iAddr1, $iUpdateValue1),
                    ISize::LONG => $this->oOutside->writeLong($iAddr1, $iUpdateValue1),
                };

                match($iSize) {
                    ISize::WORD => $this->oOutside->writeWord($iAddr2, $iUpdateValue2),
                    ISize::LONG => $this->oOutside->writeLong($iAddr2, $iUpdateValue2),
                };

                // Set Z flag (values matched)
                $this->iConditionRegister &= IRegister::CCR_CLEAR_NV;
                $this->iConditionRegister |= IRegister::CCR_ZERO;
            } else {
                // Values don't match - write memory values to Dc1:Dc2
                if ($iSize === ISize::WORD) {
                    $this->oDataRegisters->aIndex[$iDc1] =
                        ($this->oDataRegisters->aIndex[$iDc1] & 0xFFFF0000) | $iMemValue1;
                    $this->oDataRegisters->aIndex[$iDc2] =
                        ($this->oDataRegisters->aIndex[$iDc2] & 0xFFFF0000) | $iMemValue2;
                } else {
                    $this->oDataRegisters->aIndex[$iDc1] = $iMemValue1;
                    $this->oDataRegisters->aIndex[$iDc2] = $iMemValue2;
                }

                // Update condition codes based on comparison
                // Form 64-bit or 32-bit value for comparison
                $iMemCombined = ($iSize === ISize::WORD)
                    ? (($iMemValue1 << 16) | $iMemValue2)
                    : (($iMemValue1 << 32) | $iMemValue2);
                $iCompareCombined = ($iSize === ISize::WORD)
                    ? (($iCompareValue1 << 16) | $iCompareValue2)
                    : (($iCompareValue1 << 32) | $iCompareValue2);

                // Clear Z flag
                $this->iConditionRegister &= IRegister::CCR_CLEAR_NZ | IRegister::CCR_CLEAR_V;

                // Set N flag if memory < compare
                if ($iMemCombined < $iCompareCombined) {
                    $this->iConditionRegister |= IRegister::CCR_NEGATIVE;
                }
            }

            // C always clear
            $this->iConditionRegister &= IRegister::CCR_CLEAR_C;

            return 18; // Approximate cycle count
        };
    }
}
