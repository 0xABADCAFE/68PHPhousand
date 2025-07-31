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

namespace ABadCafe\G8PHPhousand\Test;

use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

require 'bootstrap.php';

$oProcessor = new class extends Processor\Base {
    public function __construct() {
        parent::__construct(new Device\Memory(64, 0));
    }

    public function getName(): string {
        return 'Test CPU';
    }

    public function getMemory(): Device\Memory {
        return $this->oOutside;
    }

    /** Expose the indexed data regs for testing */
    public function getDataRegs(): array {
        return $this->oDataRegisters->aIndex;
    }

    /** Expose the indexed addr regs for testing */
    public function getAddrRegs(): array {
        return $this->aAddressRegisters->aIndex;
    }

    public function readLongA0PostIncrement(): int {
        return $this->oOutside->readLong(self::generateLongPostInc($this->oAddressRegisters->iReg0));
    }

    public function readWordA0PreDecrement(): int {
        return $this->oOutside->readWord(self::generateWordPreDec($this->oAddressRegisters->iReg0));
    }
};

$oProcessor->getMemory()->writeLong(0, 0xABADCAFE);

assert(
    $oProcessor->readLongA0PostIncrement() === 0xABADCAFE &&
    $oProcessor->getRegister('a0') === 4,
    new LogicException('Invalid Pre Decrement Read')
);

assert(
    $oProcessor->readWordA0PreDecrement() === 0xCAFE &&
    $oProcessor->getRegister('a0') === 2,
    new LogicException('Invalid Pre Decrement Read')
);

echo "EA Mode Tests Passed\n";
