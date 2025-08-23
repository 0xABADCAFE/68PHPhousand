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

trait TArithmetic
{
    use Processor\TOpcode;

    protected function initArithmeticHandlers()
    {
        $this->addPrefixHandlers([

            // SUB Immediate
            IPrefix::OP_SUBI_B => function(int $iOpcode) {
                $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue -= $oEAMode->readByte();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZByte($iValue);
                $oEAMode->writeByte($iValue);
            },

            IPrefix::OP_SUBI_W => function(int $iOpcode) {
                $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue -= $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            },

            IPrefix::OP_SUBI_L => function(int $iOpcode) {
                $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::LONG;
                $iValue -= $oEAMode->readLong();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZLong($iValue);
                $oEAMode->writeLong($iValue);
            },


            // ADD Immediate

            IPrefix::OP_ADDI_B => function(int $iOpcode) {
                $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue += $oEAMode->readByte();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZByte($iValue);
                $oEAMode->writeByte($iValue);
            },

            IPrefix::OP_ADDI_W => function(int $iOpcode) {
                $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::WORD;
                $iValue += $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            },

            IPrefix::OP_ADDI_L => function(int $iOpcode) {
                $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $this->iProgramCounter += ISize::LONG;
                $iValue += $oEAMode->readLong();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;

                // TODO - C and V flags

                $this->updateNZLong($iValue);
                $oEAMode->writeLong($iValue);
            },
        ]);
    }
}
