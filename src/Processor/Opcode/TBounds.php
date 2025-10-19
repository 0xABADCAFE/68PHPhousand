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

namespace ABadCafe\G8PHPhousand\Processor\Opcode;

use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\Sign;

use LogicException;

/**
 * TBounds
 *
 * Implements 68020+ bounds checking instructions:
 * - CHK2 - Check register against bounds, trap if out of bounds
 * - CMP2 - Compare register against bounds, set condition codes
 */
trait TBounds
{
    use Processor\TOpcode;

    /**
     * Initialize bounds checking handlers for 68020+
     */
    private function initBoundsHandlers(): void
    {
        // CHK2/CMP2 use control addressing modes (memory only)
        $aCtrlModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_CONTROL
        );

        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBounds::OP_CHK2_CMP2, $aCtrlModes),
                function(int $iOpcode) {
                    $this->executeCHK2CMP2($iOpcode);
                }
            )
        );
    }

    /**
     * Execute CHK2 or CMP2 instruction
     *
     * Extension word format:
     * Bit 15: D/A (0=Dn, 1=An)
     * Bits 14-12: Register number (0-7)
     * Bit 11: 1=CHK2, 0=CMP2
     * Bits 10-9: Size (00=byte, 01=word, 10=long)
     * Bits 8-0: Reserved
     */
    private function executeCHK2CMP2(int $iOpcode): void
    {
        // Get EA mode for bounds location
        $oEAMode = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];

        // Read extension word
        $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;
        $this->iProgramCounter &= ISize::MASK_LONG;

        // Parse extension word
        $iRegNum = ($iExtWord >> 12) & 0x7;                // Bits 14-12: register number
        $bIsAddress = (($iExtWord >> 15) & 1) === 1;       // Bit 15: 0=Dn, 1=An
        $bIsCHK2 = (($iExtWord >> 11) & 1) === 1;          // Bit 11: 1=CHK2, 0=CMP2
        $iSize = ($iExtWord >> 9) & 0x3;                   // Bits 10-9: size

        // Get register value to check
        if ($bIsAddress) {
            $iValue = $this->oAddressRegisters->aIndex[$iRegNum];
        } else {
            $iValue = $this->oDataRegisters->aIndex[$iRegNum];
        }

        // Calculate bounds address
        $iBoundsAddr = $oEAMode->getEffectiveAddress();

        // Read bounds from memory and compare based on size
        switch ($iSize) {
            case 0: // Byte
                $iLowerBound = Sign::extByte($this->oOutside->readByte($iBoundsAddr));
                $iUpperBound = Sign::extByte($this->oOutside->readByte($iBoundsAddr + 1));
                $iValue = Sign::extByte($iValue & ISize::MASK_BYTE);
                break;

            case 1: // Word
                $iLowerBound = Sign::extWord($this->oOutside->readWord($iBoundsAddr));
                $iUpperBound = Sign::extWord($this->oOutside->readWord($iBoundsAddr + 2));
                $iValue = Sign::extWord($iValue & ISize::MASK_WORD);
                break;

            case 2: // Long
                $iLowerBound = $this->oOutside->readLong($iBoundsAddr);
                $iUpperBound = $this->oOutside->readLong($iBoundsAddr + 4);
                $iValue = $iValue & ISize::MASK_LONG;
                // Sign extend to 64-bit for proper comparison
                if ($iLowerBound & ISize::SIGN_BIT_LONG) {
                    $iLowerBound |= 0xFFFFFFFF00000000;
                }
                if ($iUpperBound & ISize::SIGN_BIT_LONG) {
                    $iUpperBound |= 0xFFFFFFFF00000000;
                }
                if ($iValue & ISize::SIGN_BIT_LONG) {
                    $iValue |= 0xFFFFFFFF00000000;
                }
                break;

            default:
                throw new LogicException(
                    sprintf('Invalid size for CHK2/CMP2: %d', $iSize)
                );
        }

        // Perform bounds check
        $bInBounds = ($iValue >= $iLowerBound) && ($iValue <= $iUpperBound);

        // Set condition codes
        // Z flag: set if equal to either bound
        // C flag: set if out of bounds
        $this->iConditionRegister &= IRegister::CCR_CLEAR_ZC;

        if ($iValue === $iLowerBound || $iValue === $iUpperBound) {
            $this->iConditionRegister |= IRegister::CCR_ZERO;
        }

        if (!$bInBounds) {
            $this->iConditionRegister |= IRegister::CCR_CARRY;
        }

        // CHK2: trap if out of bounds
        if ($bIsCHK2 && !$bInBounds) {
            throw new LogicException(
                sprintf(
                    'CHK2 bounds violation: value=%d, bounds=[%d, %d]',
                    $iValue,
                    $iLowerBound,
                    $iUpperBound
                )
            );
        }
    }
}
