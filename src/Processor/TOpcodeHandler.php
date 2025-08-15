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
 * Trait for opcode handler
 */
trait TOpcodeHandler {

    use TRegisterUnit;

    /** @var array<int, callable> */
    protected array $aExactHandler = [];

    /** @var array<int, callable> */
    protected array $aPrefixHandler = [];

    /**
     * Populates the aExactHandler array with callables for each of the opcode bit patterns
     * that are unique, i.e. all bits encode only the operation and not any parameters.
     */
    protected function initExactMatchHandlers(): void
    {
        $cUnhandled = function() {
            throw new LogicException('Unhandled operation (TODO)');
        };

        $this->aExactHandler = [
            Opcode\IPrefix::OP_ORI_CCR => function() {
                // TODO - confirm which bits
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister |= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_ORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister |= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister |= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_ANDI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister &= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_ANDI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister &= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister &= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_EORI_CCR => function() {
                $iByte = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iConditionRegister ^= ($iByte & IRegister::CCR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_EORI_SR => function() {
                // TODO - Privilege checks, etc.
                $iWord = $this->oOutside->readWord($this->iProgramCounter);
                $this->iConditionRegister ^= ($iWord & IRegister::CCR_MASK);
                $this->iStatusRegister ^= (($iWord >> 8) & IRegister::SR_MASK);
                $this->iProgramCounter += ISize::WORD;
            },

            Opcode\IPrefix::OP_ILLEGAL  => $cUnhandled,

            Opcode\IPrefix::OP_RESET    => function() {
                // TODO - probably needs to be a bit more specific than this
                $this->reset();
            },

            Opcode\IPrefix::OP_NOP      => function() {
                // Nothing yet
            },

            Opcode\IPrefix::OP_STOP     => $cUnhandled,
            Opcode\IPrefix::OP_RTE      => $cUnhandled,
            Opcode\IPrefix::OP_RTS      => $cUnhandled,
            Opcode\IPrefix::OP_TRAPV    => $cUnhandled,
            Opcode\IPrefix::OP_RTR      => $cUnhandled,
        ];
    }

    protected function initPrefixMatchHandlers()
    {
        $this->aPrefixHandler = [
            Opcode\IPrefix::OP_ORI_B => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeByte($iImmediate | $oEAMode->readByte());
            },

            Opcode\IPrefix::OP_ORI_W => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeWord($iImmediate | $oEAMode->readWord());
            },

            Opcode\IPrefix::OP_ORI_L => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readLong($this->iProgramCounter);
                $this->iProgramCounter += ISize::LONG;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeLong($iImmediate | $oEAMode->readLong());
            },

            Opcode\IPrefix::OP_ANDI_B => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeByte($iImmediate & $oEAMode->readByte());
            },

            Opcode\IPrefix::OP_ANDI_W => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeWord($iImmediate & $oEAMode->readWord());
            },

            Opcode\IPrefix::OP_ANDI_L => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readLong($this->iProgramCounter);
                $this->iProgramCounter += ISize::LONG;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeLong($iImmediate & $oEAMode->readLong());
            },

            Opcode\IPrefix::OP_EORI_B => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeByte($iImmediate ^ $oEAMode->readByte());
            },

            Opcode\IPrefix::OP_EORI_W => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += ISize::WORD;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeWord($iImmediate ^ $oEAMode->readWord());
            },

            Opcode\IPrefix::OP_EORI_L => function(int $iOpcode) {
                $iImmediate = $this->oOutside->readLong($this->iProgramCounter);
                $this->iProgramCounter += ISize::LONG;
                $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                $oEAMode->writeLong($iImmediate ^ $oEAMode->readLong());
            },
        ];
    }

}
