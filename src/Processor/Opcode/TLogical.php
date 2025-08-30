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
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;

trait TLogical
{
    use Processor\TOpcode;

    protected function initLogicalHandlers()
    {
        $this->buildSRLogicHandlers();
        $this->buildORILogicHandlers();
        $this->buildANDILogicHandlers();
        $this->buildEORILogicHandlers();
    }

    private function buildORILogicHandlers()
    {
        // ORI Byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ORI_B
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue |= $oEAMode->readByte();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZByte($iValue);
                    $oEAMode->writeByte($iValue);
                }
            )
        );

        // ORI Word
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ORI_W
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue |= $oEAMode->readWord();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZWord($iValue);
                    $oEAMode->writeWord($iValue);
                }
            )
        );

        // ORI Long
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ORI_L
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::LONG;
                    $iValue |= $oEAMode->readLong();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong($iValue);
                    $oEAMode->writeLong($iValue);
                }
            )
        );
    }

    private function buildANDILogicHandlers()
    {
        // ANDI Byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ANDI_B
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue &= $oEAMode->readByte();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZByte($iValue);
                    $oEAMode->writeByte($iValue);
                }
            )
        );

        // ANDI Word
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ANDI_W
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue &= $oEAMode->readWord();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZWord($iValue);
                    $oEAMode->writeWord($iValue);
                }
            )
        );

        // ANDI Long
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_ANDI_L
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::LONG;
                    $iValue &= $oEAMode->readLong();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong($iValue);
                    $oEAMode->writeLong($iValue);
                }
            )
        );
    }

    private function buildEORILogicHandlers()
    {
        // EORI Byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_EORI_B
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue ^= $oEAMode->readByte();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZByte($iValue);
                    $oEAMode->writeByte($iValue);
                }
            )
        );

        // EORI Word
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_EORI_W
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue ^= $oEAMode->readWord();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZWord($iValue);
                    $oEAMode->writeWord($iValue);
                }
            )
        );

        // EORI Long
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ADDRESSABLE,
                    ILogical::OP_EORI_L
                ),
                function(int $iOpcode) {
                    $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::LONG;
                    $iValue ^= $oEAMode->readLong();
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong($iValue);
                    $oEAMode->writeLong($iValue);
                }
            )
        );
    }

    private function buildSRLogicHandlers()
    {
        $this->addExactHandlers([
            ILogical::OP_ORI_CCR => function() {
                // TODO - confirm which bits
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister |= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            ILogical::OP_ORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister |= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister |= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            ILogical::OP_ANDI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister &= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            ILogical::OP_ANDI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister &= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister &= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            ILogical::OP_EORI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister ^= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            ILogical::OP_EORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister ^= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister ^= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },
        ]);
    }
}
