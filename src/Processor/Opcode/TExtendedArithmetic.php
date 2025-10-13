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

trait TExtendedArithmetic
{
    private function buildNEGXHandlers(array $aEAModes)
    {
        // NEGx.b byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEGX_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readByte();
                    $iRes    = -$iDst - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

                    // The Z flag should be cleared if the result is nonzero, othherwise unchanged
                    $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $iRes &= ISize::MASK_BYTE;
                    $this->updateNZByte($iRes);
                    $this->iConditionRegister |= ($iRes && $iRes === $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $this->iConditionRegister &= $iZeroMask;
                    $oEAMode->writeByte($iRes);
                }
            )
        );

        // NEGX.w word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEGX_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readWord();
                    $iRes    = -$iDst - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

                    // The Z flag should be cleared if the result is nonzero, othherwise unchanged
                    $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $iRes &= ISize::MASK_WORD;
                    $this->updateNZWord($iRes);
                    $this->iConditionRegister |= ($iRes && $iRes === $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $this->iConditionRegister &= $iZeroMask;
                    $oEAMode->writeWord($iRes);
                }
            )
        );

        // NEGX.l long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEGX_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readLong();
                    $iRes    = -$iDst - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

                    // The Z flag should be cleared if the result is nonzero, othherwise unchanged
                    $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);

                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $iRes &= ISize::MASK_LONG;
                    $this->updateNZLong($iRes);
                    $this->iConditionRegister |= ($iRes && $iRes === $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $this->iConditionRegister &= $iZeroMask;
                    $oEAMode->writeLong($iRes);
                }
            )
        );
    }
    private function buildADDXHandlers(array $aRegComb)
    {
        // ADDX.b Dy,Dx
        $cADDXDyDx = function(int $iOpcode) {
            $iRegX = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc = $iRegX  & ISize::MASK_BYTE;
            $iDst = $this->oDataRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG] & ISize::MASK_BYTE;
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathByte($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;

            $iRegX &= ISize::MASK_INV_BYTE;
            $iRegX |= ($iSum & ISize::MASK_BYTE);
        };

        // ADDX.b -(Ay),-(Ax)
        $cADDXAyAx = function(int $iOpcode) {
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
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathByte($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeByte($iSum);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_ADDX_DyDx_B | $iRegPair
            ] = $cADDXDyDx;
            $aHandlers[
                IArithmetic::OP_ADDX_AyAx_B | $iRegPair
            ] = $cADDXAyAx;
        }

        $this->addExactHandlers($aHandlers);

        // ADDX.w Dy,Dx
        $cADDXDyDx = function(int $iOpcode) {
            $iRegX = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc = $iRegX  & ISize::MASK_WORD;
            $iDst = $this->oDataRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG] & ISize::MASK_WORD;
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathWord($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;

            $iRegX &= ISize::MASK_INV_WORD;
            $iRegX |= ($iSum & ISize::MASK_WORD);
        };

        // ADDX.w -(Ay),-(Ax)
        $cADDXAyAx = function(int $iOpcode) {
            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc = $oSrcEA->readWord();
            $iDst = $oDstEA->readWord();
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathWord($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeWord($iSum);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_ADDX_DyDx_W | $iRegPair
            ] = $cADDXDyDx;
            $aHandlers[
                IArithmetic::OP_ADDX_AyAx_W | $iRegPair
            ] = $cADDXAyAx;
        }

        $this->addExactHandlers($aHandlers);

        // ADDX.w Dy,Dx
        $cADDXDyDx = function(int $iOpcode) {
            $iRegX = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc = $iRegX  & ISize::MASK_LONG;
            $iDst = $this->oDataRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG] & ISize::MASK_LONG;
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathLong($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;

            $iRegX = ($iSum & ISize::MASK_LONG);
        };

        // ADDX.l -(Ay),-(Ax)
        $cADDXAyAx = function(int $iOpcode) {
            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc = $oSrcEA->readLong();
            $iDst = $oDstEA->readLong();
            $iSum = $iSrc + $iDst + (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathLong($iSrc, $iDst, $iSum, true);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeLong($iSum);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_ADDX_DyDx_L | $iRegPair
            ] = $cADDXDyDx;
            $aHandlers[
                IArithmetic::OP_ADDX_AyAx_L | $iRegPair
            ] = $cADDXAyAx;
        }

        $this->addExactHandlers($aHandlers);
    }

    private function buildSUBXHandlers(array $aRegComb)
    {
        // SUBX.b Dx,Dy
        $cSUBXDxDy = function(int $iOpcode) {
            $iRegX = $this->oDataRegisters->aIndex[
                $iOpcode & IOpcode::MASK_EA_REG
            ];

            $iRegY = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc  = $iRegX & ISize::MASK_BYTE;
            $iDst  = $iRegY & ISize::MASK_BYTE;
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            // The Z flag should be cleared if the result is nonzero, otherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathByte($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;

            $iRegY &= ISize::MASK_INV_BYTE;
            $iRegY |= ($iDiff & ISize::MASK_BYTE);
        };

        // SUBX.b -(Ax),-(Ay)
        $cSUBXAxAy = function(int $iOpcode) {
            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc  = $oSrcEA->readByte();
            $iDst  = $oDstEA->readByte();
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathByte($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeByte($iDiff);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_SUBX_DxDy_B | $iRegPair
            ] = $cSUBXDxDy;
            $aHandlers[
               IArithmetic::OP_SUBX_AxAy_B | $iRegPair
            ] = $cSUBXAxAy;
        }

        $this->addExactHandlers($aHandlers);

        // SUBX.w Dy,Dx
        $cSUBXDxDy = function(int $iOpcode) {
            $iRegX = $this->oDataRegisters->aIndex[
                $iOpcode & IOpcode::MASK_EA_REG
            ];

            $iRegY = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc  = $iRegX & ISize::MASK_WORD;
            $iDst  = $iRegY & ISize::MASK_WORD;
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            // The Z flag should be cleared if the result is nonzero, otherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathWord($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;

            $iRegY &= ISize::MASK_INV_WORD;
            $iRegY |= ($iDiff & ISize::MASK_WORD);
        };

        // SUBX.w -(Ax),-(Ay)
        $cSUBXAxAy = function(int $iOpcode) {
            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc  = $oSrcEA->readWord();
            $iDst  = $oDstEA->readWord();
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, othherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathWord($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeWord($iDiff);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_SUBX_DxDy_W | $iRegPair
            ] = $cSUBXDxDy;
            $aHandlers[
                IArithmetic::OP_SUBX_AxAy_W | $iRegPair
            ] = $cSUBXAxAy;
        }

        $this->addExactHandlers($aHandlers);

        // SUBX.l Dx,Dy
        $cSUBXDxDy = function(int $iOpcode) {
            $iRegX = $this->oDataRegisters->aIndex[
                $iOpcode & IOpcode::MASK_EA_REG
            ];

            $iRegY = &$this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ];

            $iSrc  = $iRegX & ISize::MASK_LONG;
            $iDst  = $iRegY & ISize::MASK_LONG;
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);

            printf(
                ">>>>>>>>>>>>>>>> SUBX.l %d - %d - %d => %d\n",
                $iSrc,
                $iDst,
                (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4),
                $iDiff
            );


            // The Z flag should be cleared if the result is nonzero, otherwise unchanged
            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathLong($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;

            $iRegY = $iDiff & ISize::MASK_LONG;
        };

        // SUBX.l -(Ax),-(Ay)
        $cSUBXAxAy = function(int $iOpcode) {
            $oSrcEA = $this->aSrcEAModes[
                IOpcode::LSB_EA_AIPD |
                ($iOpcode & IOpcode::MASK_EA_REG)
            ];
            $oDstEA = $this->aDstEAModes[
                IOpcode::LSB_EA_AIPD |
                (($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT)
            ];

            $iSrc  = $oSrcEA->readLong();
            $iDst  = $oDstEA->readLong();
            $iDiff = $iDst - $iSrc - (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
            // The Z flag should be cleared if the result is nonzero, otherwise unchanged

            $iZeroMask = IRegister::CCR_CLEAR_Z | ($this->iConditionRegister & IRegister::CCR_ZERO);
            $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
            $this->updateCCRMathLong($iSrc, $iDst, $iDiff, false);
            $this->iConditionRegister &= $iZeroMask;
            $oDstEA->writeLong($iDiff);
        };

        $aHandlers = [];
        foreach ($aRegComb as $iRegPair) {
            $aHandlers[
                IArithmetic::OP_SUBX_DxDy_L | $iRegPair
            ] = $cSUBXDxDy;
            $aHandlers[
                IArithmetic::OP_SUBX_AxAy_L | $iRegPair
            ] = $cSUBXAxAy;
        }
        $this->addExactHandlers($aHandlers);
    }
}
