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


}
