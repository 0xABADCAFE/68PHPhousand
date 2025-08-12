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

use ABadCafe\G8PHPhousand\I68KProcessor;

use ABadCafe\G8PHPhousand\Device;

use LogicException;

/**
 * Base class implementation
 */
abstract class Base implements I68KProcessor, IOpcode, Opcode\IPrefix {

    protected Device\IBus $oOutside;

    use TRegisterUnit;
    use TAddressUnit;

    public function __construct(Device\IBus $oOutside) {
        $this->oOutside  = $oOutside;
        $this->initRegIndexes();
        $this->initEAModes();
        $this->softReset();
    }

    public function softReset(): self {
        $this->registerReset();
        $this->oOutside->softReset();
        return $this;
    }

    public function hardReset(): self {
        $this->registerReset();
        $this->oOutside->hardReset();
        return $this;
    }


    protected function execute() {
        $iOpcode =  $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;

        // Attempt exact match opcodes first
        switch ($iOpcode) {
            case self::OP_ORI_CCR:
            case self::OP_ANDI_CCR:
            case self::OP_EORI_CCR:
            // Privileged
            case self::OP_ORI_SR:
            case self::OP_ANDI_SR:
            case self::OP_EORI_SR:
                throw new LogicException('Unimplemented exact opcode');
        }
        $iPrefix = $iOpcode & self::MASK_OP_PREFIX;
        switch ($iPrefix) {
            case self::OP_ORI_B:
            case self::OP_ORI_W:
            case self::OP_ORI_L:

            case self::OP_ANDI_B:
            case self::OP_ANDI_W:
            case self::OP_ANDI_L:

            case self::OP_EORI_B:
            case self::OP_EORI_W:
            case self::OP_EORI_L:

            case self::OP_SUBI_B:
            case self::OP_SUBI_W:
            case self::OP_SUBI_L:

            case self::OP_ADDI_B:
            case self::OP_ADDI_W:
            case self::OP_ADDI_L:

            case self::OP_CMPI_B:
            case self::OP_CMPI_W:
            case self::OP_CMPI_L:
                throw new LogicException('Unimplemented prefix opcode');
        }
    }
}
