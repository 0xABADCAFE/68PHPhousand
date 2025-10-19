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

use ABadCafe\G8PHPhousand\Processor\TOpcode;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\Sign;

trait TBCDArithmetic
{

    private function addBCDBytes(int $iSrc, int $iDst): int
    {
        $iLo =
            ($iSrc & 0xF) +
            ($iDst & 0xF) +
            (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;

        $iHi = ($iSrc & 0xF0) + ($iDst & 0xF0);

        $iSum = $iHi + $iLo;
        if ($iLo > 0x9) {
            $iSum += 0x6; // Carry up
        }
        if (($iSum & 0x3F0) > 0x90) {
            $iSum += 0x60;
            $this->iConditionRegister |= IRegister::CCR_MASK_XC; // Carry up
        }

        // Mask off the byte
        $iSum &= 0xFF;

        // The Z flag should be cleared if the result is nonzero, othherwise unchanged
        $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

        // Documentation says that N and V flags are undefined but microcode
        // emulation sets the N flag predicably. The V flag does not follow
        // an obvious pattern though.
        $this->updateNZByte($iSum);
        $this->iConditionRegister &= $iZeroMask;

        return $iSum;
    }

    private function subBCDBytes(int $iSrc, int $iDst): int
    {
        $iExtend = ($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4;
        $iLo  = (($iDst & 0x0F) - ($iSrc & 0x0F) - $iExtend) & ISize::MASK_WORD;
        $iHi  = (($iDst & 0xF0) - ($iSrc & 0xF0)) & ISize::MASK_WORD;

        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;

        $iDiff   = $iHi + $iLo;
        $iBorrow = 0;
        if ($iLo & 0xF0) {
            $iDiff -= 6;
            $iBorrow = 6;
        }

        $iTest = ($iDst & ISize::MASK_BYTE) - ($iSrc & ISize::MASK_BYTE) - $iExtend;

        if ($iTest & 0x100) {
            $iDiff -= 0x60;
        }
        if (($iTest - $iBorrow) & 0x300) {
            $this->iConditionRegister |= IRegister::CCR_MASK_XC;
        }
        // Mask off the byte
        $iDiff &= 0xFF;

        // The Z flag should be cleared if the result is nonzero, othherwise unchanged
        $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

        // Documentation says that N and V flags are undefined but microcode
        // emulation sets the N flag predicably. The V flag does not follow
        // an obvious pattern though.
        $this->updateNZByte($iDiff);
        $this->iConditionRegister &= $iZeroMask;

        return $iDiff;
    }

    private function buildNBCDHandlers(array $aEAModes)
    {
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NBCD,
                    $aEAModes
                ),
                function (int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc = $oEAMode->readByte();
                    $iLo  = (-($iSrc & 0x0F) - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4)) & ISize::MASK_WORD;
                    $iHi  = (-($iSrc & 0xF0)) & ISize::MASK_WORD;

                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;

                    if ($iLo > 9) {
                        $iLo -= 6;
                    }
                    $iNeg = $iHi + $iLo;
                    if (($iNeg & 0x1F0) > 0x90) {
                        $iNeg -= 0x60;
                        $this->iConditionRegister |= IRegister::CCR_MASK_XC; // Carry up
                    }
                    // Mask off the byte
                    $iNeg &= 0xFF;

                    // The Z flag should be cleared if the result is nonzero, othherwise unchanged
                    $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

                    // Documentation says that N and V flags are undefined but microcode
                    // emulation sets the N flag predicably. The V flag does not follow
                    // an obvious pattern though.
                    $this->updateNZByte($iNeg);
                    $this->iConditionRegister &= $iZeroMask;

                    $oEAMode->writeByte($iNeg);
                }
            )
        );
    }

    private function buildABCDHandlers(array $aRegComb)
    {
        // ABCD Dy,Dx
        $cABCDDyDx = function(int $iOpcode) {
            $iRegX = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];
            $iRegY = $this->oDataRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG];
            $iSum  = $this->addBCDBytes($iRegX, $iRegY);

            $iRegX &= ISize::MASK_INV_BYTE;
            $iRegX |= $iSum;
        };

        // ABCD -(Ay),-(Ax)
        $cABCDAyAx = function (int $iOpcode) {

            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc = $oSrcEA->readByte();
            $iDst = $oDstEA->readByte();

            $oDstEA->writeByte($this->addBCDBytes($iSrc, $iDst));
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_ABCD_R | $iRegPair
            ] = $cABCDDyDx;
            $aHandlers[
                IArithmetic::OP_ABCD_M | $iRegPair
            ] = $cABCDAyAx;
        }

        $this->addExactHandlers($aHandlers);
    }

    private function buildSBCDHandlers(array $aRegComb)
    {
        // SBCD Dx,Dy
        $cSBCDDxDy = function(int $iOpcode) {
            $iRegY = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];
            $iRegX = $this->oDataRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG];
            $iDiff = $this->subBCDBytes($iRegX, $iRegY);

            $iRegY &= ISize::MASK_INV_BYTE;
            $iRegY |= $iDiff;
        };

        // SBCD -(Ax),-(Ay)
        $cSBCDAxAy = function (int $iOpcode) {

            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc = $oSrcEA->readByte();
            $iDst = $oDstEA->readByte();

            $oDstEA->writeByte($this->subBCDBytes($iSrc, $iDst));
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_SBCD_R | $iRegPair
            ] = $cSBCDDxDy;
            $aHandlers[
                IArithmetic::OP_SBCD_M | $iRegPair
            ] = $cSBCDAxAy;
        }

        $this->addExactHandlers($aHandlers);
    }

    /**
     * Build PACK instruction handlers (68020+)
     *
     * PACK -(Ax),-(Ay),#adjustment
     * Converts two unpacked BCD bytes to one packed BCD byte with adjustment.
     *
     * Operation:
     * 1. Read two bytes from -(Ax) (source, unpacked BCD)
     * 2. Pack into single byte: (high_nibble << 4) | low_nibble
     * 3. Add 16-bit adjustment constant
     * 4. Write result byte to -(Ay) (destination)
     */
    private function buildPACKHandlers(array $aRegComb): void
    {
        $cPACK = function(int $iOpcode) {
            // Extract register numbers
            $iRegX = ($iOpcode & IOpcode::MASK_EA_REG);         // Source register (Ax)
            $iRegY = ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT; // Dest register (Ay)

            // Get predecrement EA modes for source and destination
            $oSrcEA = $this->aSrcEAModes[IOpcode::LSB_EA_AIPD | $iRegX];
            $oDstEA = $this->aDstEAModes[IOpcode::LSB_EA_AIPD | $iRegY];

            // Read adjustment constant (16-bit immediate word after opcode)
            $iAdjustment = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
            $this->iProgramCounter += ISize::WORD;
            $this->iProgramCounter &= ISize::MASK_LONG;

            // Read two unpacked BCD bytes from source (predecrement reads in reverse)
            // First read gets low digit, second read gets high digit
            $iLowDigit = $oSrcEA->readByte() & 0x0F;
            $iHighDigit = $oSrcEA->readByte() & 0x0F;

            // Pack: combine high and low nibbles
            $iPacked = (($iHighDigit << 4) | $iLowDigit);

            // Add adjustment (lower 8 bits only)
            $iPacked = ($iPacked + $iAdjustment) & ISize::MASK_BYTE;

            // Write packed result to destination
            $oDstEA->writeByte($iPacked);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[IArithmetic::OP_PACK | $iRegPair] = $cPACK;
        }

        $this->addExactHandlers($aHandlers);
    }

    /**
     * Build UNPK instruction handlers (68020+)
     *
     * UNPK -(Ax),-(Ay),#adjustment
     * Converts one packed BCD byte to two unpacked BCD bytes with adjustment.
     *
     * Operation:
     * 1. Read one packed byte from -(Ax) (source)
     * 2. Unpack into two bytes: high_nibble and low_nibble
     * 3. Add 16-bit adjustment constant (split across two bytes)
     * 4. Write two result bytes to -(Ay) (destination)
     */
    private function buildUNPKHandlers(array $aRegComb): void
    {
        $cUNPK = function(int $iOpcode) {
            // Extract register numbers
            $iRegX = ($iOpcode & IOpcode::MASK_EA_REG);         // Source register (Ax)
            $iRegY = ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT; // Dest register (Ay)

            // Get predecrement EA modes for source and destination
            $oSrcEA = $this->aSrcEAModes[IOpcode::LSB_EA_AIPD | $iRegX];
            $oDstEA = $this->aDstEAModes[IOpcode::LSB_EA_AIPD | $iRegY];

            // Read adjustment constant (16-bit immediate word after opcode)
            $iAdjustment = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += ISize::WORD;
            $this->iProgramCounter &= ISize::MASK_LONG;

            // Read one packed BCD byte from source
            $iPacked = $oSrcEA->readByte();

            // Unpack into two nibbles
            $iHighDigit = ($iPacked >> 4) & 0x0F;
            $iLowDigit = $iPacked & 0x0F;

            // Add adjustment (high byte to high digit, low byte to low digit)
            $iLowResult = ($iLowDigit + ($iAdjustment & 0xFF)) & ISize::MASK_BYTE;
            $iHighResult = ($iHighDigit + (($iAdjustment >> 8) & 0xFF)) & ISize::MASK_BYTE;

            // Write unpacked results to destination (predecrement writes in reverse)
            // First write is low digit, second write is high digit
            $oDstEA->writeByte($iLowResult);
            $oDstEA->writeByte($iHighResult);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[IArithmetic::OP_UNPK | $iRegPair] = $cUNPK;
        }

        $this->addExactHandlers($aHandlers);
    }

}
