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
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;

trait TLogical
{
    use Processor\TOpcode;

    protected function initLogicalHandlers()
    {
        $this->addExactHandlers([
            IPrefix::OP_ORI_CCR => function() {
                // TODO - confirm which bits
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister |= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            IPrefix::OP_ORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister |= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister |= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            IPrefix::OP_ANDI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister &= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            IPrefix::OP_ANDI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister &= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister &= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            IPrefix::OP_EORI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister ^= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            IPrefix::OP_EORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister ^= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister ^= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },
        ]);

        $this->addPrefixHandlers([

            // OR Immediate
            IPrefix::OP_ORI_B => function(int $iOpcode) {
                $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue |= $oEAMode->readByte();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZByte($iValue);
                $oEAMode->writeByte($iValue);
            },
            IPrefix::OP_ORI_W => function(int $iOpcode) {
                $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue |= $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            },
            IPrefix::OP_ORI_L => function(int $iOpcode) {
                $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::LONG;
                $iValue |= $oEAMode->readLong();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZLong($iValue);
                $oEAMode->writeLong($iValue);
            },

            // AND Immediate
            IPrefix::OP_ANDI_B => function(int $iOpcode) {
                $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue &= $oEAMode->readByte();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZByte($iValue);
                $oEAMode->writeByte($iValue);
            },
            IPrefix::OP_ANDI_W => function(int $iOpcode) {
                $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue &= $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            },
            IPrefix::OP_ANDI_L => function(int $iOpcode) {
                $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::LONG;
                $iValue &= $oEAMode->readLong();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZLong($iValue);
                $oEAMode->writeLong($iValue);
            },

            // EOR Immediate
            IPrefix::OP_EORI_B => function(int $iOpcode) {
                $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue ^= $oEAMode->readByte();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZByte($iValue);
                $oEAMode->writeByte($iValue);
            },
            IPrefix::OP_EORI_W => function(int $iOpcode) {
                $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue ^= $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            },
            IPrefix::OP_EORI_L => function(int $iOpcode) {
                $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::LONG;
                $iValue ^= $oEAMode->readLong();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZLong($iValue);
                $oEAMode->writeLong($iValue);
            },
        ]);
    }
}
