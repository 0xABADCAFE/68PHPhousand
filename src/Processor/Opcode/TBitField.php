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

/**
 * TBitField
 *
 * Implements 68020+ bit field instructions.
 *
 * Bit fields are arbitrary sequences of bits (1-32 bits) that can start at any
 * bit position and span multiple bytes in memory or within a data register.
 */
trait TBitField
{
    use Processor\TOpcode;

    /**
     * Initialize bit field handlers for 68020+
     */
    private function initBitFieldHandlers(): void
    {
        // BFTST, BFCLR, BFSET, BFCHG use data control addressing modes
        $aDataCtrlModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_DATA_CONTROL
        );

        // BFEXTU, BFEXTS, BFFFO, BFINS use data control addressing modes
        $aDataAltModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_DATA_ALTERABLE
        );

        // BFTST - Test bit field (read-only, sets condition codes)
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFTST, $aDataCtrlModes),
                function(int $iOpcode) {
                    $this->executeBFTST($iOpcode);
                }
            )
        );

        // BFEXTU - Extract bit field unsigned
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFEXTU, $aDataCtrlModes),
                function(int $iOpcode) {
                    $this->executeBFEXTU($iOpcode);
                }
            )
        );

        // BFEXTS - Extract bit field signed
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFEXTS, $aDataCtrlModes),
                function(int $iOpcode) {
                    $this->executeBFEXTS($iOpcode);
                }
            )
        );

        // BFCLR - Clear bit field
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFCLR, $aDataAltModes),
                function(int $iOpcode) {
                    $this->executeBFCLR($iOpcode);
                }
            )
        );

        // BFSET - Set bit field
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFSET, $aDataAltModes),
                function(int $iOpcode) {
                    $this->executeBFSET($iOpcode);
                }
            )
        );

        // BFCHG - Change (toggle) bit field
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFCHG, $aDataAltModes),
                function(int $iOpcode) {
                    $this->executeBFCHG($iOpcode);
                }
            )
        );

        // BFFFO - Find first one in bit field
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFFFO, $aDataCtrlModes),
                function(int $iOpcode) {
                    $this->executeBFFFO($iOpcode);
                }
            )
        );

        // BFINS - Insert bit field
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(IBitField::OP_BFINS, $aDataAltModes),
                function(int $iOpcode) {
                    $this->executeBFINS($iOpcode);
                }
            )
        );
    }

    /**
     * Parse bit field extension word
     *
     * Extension word format:
     * - Bits 15-12: Register number (for BFINS/BFEXTS/BFEXTU/BFFFO)
     * - Bit 11: Offset field (0=immediate, 1=data register)
     * - Bits 10-6: Offset value (0-31) or register number (0-7)
     * - Bit 5: Width field (0=immediate, 1=data register)
     * - Bits 4-0: Width value (1-32, encoded as 0-31) or register number (0-7)
     *
     * @return array{0: int, 1: int, 2: int} [register, offset, width]
     */
    private function parseBitFieldExtension(): array
    {
        $iExtWord = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;
        $this->iProgramCounter &= ISize::MASK_LONG;

        $iRegister = ($iExtWord >> 12) & 0x7;

        // Parse offset (bit 11 = 0:immediate, 1:register)
        if (($iExtWord >> 11) & 1) {
            // Offset from data register
            $iOffsetReg = ($iExtWord >> 6) & 0x7;
            $iOffset = $this->oDataRegisters->aIndex[$iOffsetReg] & ISize::MASK_LONG;
        } else {
            // Immediate offset (5 bits, 0-31)
            $iOffset = ($iExtWord >> 6) & 0x1F;
        }

        // Parse width (bit 5 = 0:immediate, 1:register)
        if (($iExtWord >> 5) & 1) {
            // Width from data register
            $iWidthReg = $iExtWord & 0x7;
            $iWidth = $this->oDataRegisters->aIndex[$iWidthReg] & 0x1F;
        } else {
            // Immediate width (5 bits, 1-32 encoded as 0-31)
            $iWidth = $iExtWord & 0x1F;
        }

        // Width 0 means 32
        if ($iWidth === 0) {
            $iWidth = 32;
        }

        return [$iRegister, $iOffset, $iWidth];
    }

    /**
     * Read bit field from data register
     *
     * For data registers, offset wraps modulo 32
     */
    private function readBitFieldDirect(int $iRegValue, int $iOffset, int $iWidth): int
    {
        $iOffset &= 31; // Modulo 32 for data register

        // Extract the bit field
        $iShift = 32 - $iOffset - $iWidth;
        $iMask = (1 << $iWidth) - 1;

        return ($iRegValue >> $iShift) & $iMask;
    }

    /**
     * Read bit field from memory
     *
     * For memory, offset can be any value. Negative offsets move backward.
     * The bit field can span up to 5 consecutive bytes.
     */
    private function readBitFieldMemory(int $iBaseAddr, int $iOffset, int $iWidth): int
    {
        // Calculate byte offset and bit offset within byte
        $iByteOffset = $iOffset >> 3; // Divide by 8
        $iBitOffset = $iOffset & 7;   // Modulo 8

        // Calculate starting address
        $iAddr = ($iBaseAddr + $iByteOffset) & $this->iAddressMask;

        // Calculate how many bytes we need to read (max 5 bytes for 32-bit field)
        $iTotalBits = $iBitOffset + $iWidth;
        $iNumBytes = (($iTotalBits + 7) >> 3); // Ceiling division by 8

        // Read bytes and build the bit field value
        $iValue = 0;
        for ($i = 0; $i < $iNumBytes; $i++) {
            $iByte = $this->oOutside->readByte($iAddr + $i);
            $iValue = ($iValue << 8) | $iByte;
        }

        // Extract the bit field
        $iShift = ($iNumBytes * 8) - $iBitOffset - $iWidth;
        $iMask = (1 << $iWidth) - 1;

        return ($iValue >> $iShift) & $iMask;
    }

    /**
     * Write bit field to data register
     */
    private function writeBitFieldDirect(int &$iRegValue, int $iOffset, int $iWidth, int $iFieldValue): void
    {
        $iOffset &= 31; // Modulo 32 for data register

        // Create mask for the bit field
        $iShift = 32 - $iOffset - $iWidth;
        $iMask = ((1 << $iWidth) - 1) << $iShift;

        // Clear the bit field and insert new value
        $iRegValue = ($iRegValue & ~$iMask) | (($iFieldValue << $iShift) & $iMask);
    }

    /**
     * Write bit field to memory
     */
    private function writeBitFieldMemory(int $iBaseAddr, int $iOffset, int $iWidth, int $iFieldValue): void
    {
        // Calculate byte offset and bit offset within byte
        $iByteOffset = $iOffset >> 3; // Divide by 8
        $iBitOffset = $iOffset & 7;   // Modulo 8

        // Calculate starting address
        $iAddr = ($iBaseAddr + $iByteOffset) & $this->iAddressMask;

        // Calculate how many bytes we need to modify
        $iTotalBits = $iBitOffset + $iWidth;
        $iNumBytes = (($iTotalBits + 7) >> 3); // Ceiling division by 8

        // Read existing bytes
        $iValue = 0;
        for ($i = 0; $i < $iNumBytes; $i++) {
            $iByte = $this->oOutside->readByte($iAddr + $i);
            $iValue = ($iValue << 8) | $iByte;
        }

        // Modify the bit field
        $iShift = ($iNumBytes * 8) - $iBitOffset - $iWidth;
        $iMask = ((1 << $iWidth) - 1) << $iShift;
        $iValue = ($iValue & ~$iMask) | (($iFieldValue << $iShift) & $iMask);

        // Write bytes back
        for ($i = $iNumBytes - 1; $i >= 0; $i--) {
            $iByte = $iValue & 0xFF;
            $this->oOutside->writeByte($iAddr + $i, $iByte);
            $iValue >>= 8;
        }
    }

    /**
     * Update condition codes for bit field operations
     */
    private function updateBitFieldCC(int $iValue, int $iWidth): void
    {
        // Clear N, Z, V, C flags
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZVC;

        // Set Z if value is zero
        if ($iValue === 0) {
            $this->iConditionRegister |= IRegister::CCR_ZERO;
        }

        // Set N if MSB of bit field is set
        if ($iValue & (1 << ($iWidth - 1))) {
            $this->iConditionRegister |= IRegister::CCR_NEGATIVE;
        }

        // V and C are always cleared
    }

    /**
     * BFTST - Test bit field
     */
    private function executeBFTST(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;
        $oEAMode = $this->aSrcEAModes[$iEAMode];

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
        } else {
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
        }

        $this->updateBitFieldCC($iValue, $iWidth);
    }

    /**
     * BFEXTU - Extract bit field unsigned
     */
    private function executeBFEXTU(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;
        $oEAMode = $this->aSrcEAModes[$iEAMode];

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
        } else {
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
        }

        $this->updateBitFieldCC($iValue, $iWidth);

        // Store unsigned value in destination register
        $this->oDataRegisters->aIndex[$iRegister] = $iValue;
    }

    /**
     * BFEXTS - Extract bit field signed
     */
    private function executeBFEXTS(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;
        $oEAMode = $this->aSrcEAModes[$iEAMode];

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
        } else {
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
        }

        $this->updateBitFieldCC($iValue, $iWidth);

        // Sign extend the value
        if ($iValue & (1 << ($iWidth - 1))) {
            $iValue |= (0xFFFFFFFF << $iWidth);
        }

        // Store signed value in destination register
        $this->oDataRegisters->aIndex[$iRegister] = $iValue;
    }

    /**
     * BFCLR - Clear bit field
     */
    private function executeBFCLR(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $this->writeBitFieldDirect($this->oDataRegisters->aIndex[$iRegNum], $iOffset, $iWidth, 0);
        } else {
            $oEAMode = $this->aDstEAModes[$iEAMode];
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $this->writeBitFieldMemory($iAddr, $iOffset, $iWidth, 0);
        }
    }

    /**
     * BFSET - Set bit field
     */
    private function executeBFSET(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;
        $iSetValue = (1 << $iWidth) - 1; // All bits set

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $this->writeBitFieldDirect($this->oDataRegisters->aIndex[$iRegNum], $iOffset, $iWidth, $iSetValue);
        } else {
            $oEAMode = $this->aDstEAModes[$iEAMode];
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $this->writeBitFieldMemory($iAddr, $iOffset, $iWidth, $iSetValue);
        }
    }

    /**
     * BFCHG - Change (toggle) bit field
     */
    private function executeBFCHG(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $iToggled = ((1 << $iWidth) - 1) & ~$iValue; // Invert all bits
            $this->writeBitFieldDirect($this->oDataRegisters->aIndex[$iRegNum], $iOffset, $iWidth, $iToggled);
        } else {
            $oEAMode = $this->aDstEAModes[$iEAMode];
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
            $this->updateBitFieldCC($iValue, $iWidth);
            $iToggled = ((1 << $iWidth) - 1) & ~$iValue; // Invert all bits
            $this->writeBitFieldMemory($iAddr, $iOffset, $iWidth, $iToggled);
        }
    }

    /**
     * BFFFO - Find first one in bit field
     */
    private function executeBFFFO(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;
        $oEAMode = $this->aSrcEAModes[$iEAMode];

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $iRegValue = $this->oDataRegisters->aIndex[$iRegNum];
            $iValue = $this->readBitFieldDirect($iRegValue, $iOffset, $iWidth);
        } else {
            $iAddr = $oEAMode->getEffectiveAddress();
            $iValue = $this->readBitFieldMemory($iAddr, $iOffset, $iWidth);
        }

        $this->updateBitFieldCC($iValue, $iWidth);

        // Find first one bit (scan from MSB to LSB)
        $iBitPos = $iOffset;
        if ($iValue !== 0) {
            for ($i = $iWidth - 1; $i >= 0; $i--) {
                if ($iValue & (1 << $i)) {
                    $iBitPos = $iOffset + ($iWidth - 1 - $i);
                    break;
                }
            }
        } else {
            // No one bit found, return offset + width
            $iBitPos = $iOffset + $iWidth;
        }

        // Store bit position in destination register
        $this->oDataRegisters->aIndex[$iRegister] = $iBitPos;
    }

    /**
     * BFINS - Insert bit field
     */
    private function executeBFINS(int $iOpcode): void
    {
        [$iRegister, $iOffset, $iWidth] = $this->parseBitFieldExtension();

        $iEAMode = $iOpcode & IOpcode::MASK_OP_STD_EA;

        // Get value to insert from source register (low bits)
        $iInsertValue = $this->oDataRegisters->aIndex[$iRegister] & ((1 << $iWidth) - 1);

        $this->updateBitFieldCC($iInsertValue, $iWidth);

        // Check if EA is data register direct
        if (($iEAMode & IOpcode::MASK_EA_MODE) === IOpcode::LSB_EA_DREG) {
            $iRegNum = $iEAMode & IOpcode::MASK_EA_REG;
            $this->writeBitFieldDirect($this->oDataRegisters->aIndex[$iRegNum], $iOffset, $iWidth, $iInsertValue);
        } else {
            $oEAMode = $this->aDstEAModes[$iEAMode];
            $iAddr = $oEAMode->getEffectiveAddress();
            $this->writeBitFieldMemory($iAddr, $iOffset, $iWidth, $iInsertValue);
        }
    }
}
