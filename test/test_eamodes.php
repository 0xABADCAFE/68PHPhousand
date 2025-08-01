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
    public function getDataRegs(): Processor\RegisterSet {
        return $this->oDataRegisters;
    }

    /** Expose the indexed addr regs for testing */
    public function getAddrRegs(): Processor\RegisterSet {
        return $this->oAddressRegisters;
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

$oEATargetData = new Processor\EATarget\DataRegister($oProcessor->getDataRegs());

$oEATargetData->bind(0);
$oEATargetData->writeLong(0x11111111);
$oEATargetData->writeWord(0x2222);
$oEATargetData->writeByte(0x33);
assert(
    0x11112233 === $oEATargetData->readLong(),
    new LogicException('Invalid EA data register result')
);

$oEATargetAddr = new Processor\EATarget\AddressRegister($oProcessor->getAddrRegs());
$oEATargetAddr->bind(1);
$oEATargetAddr->writeLong(0x12345678);
assert(
    0x12345678 === $oEATargetAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

$oEATargetAddr->writeWord(0x4321);
assert(
    0x00004321 === $oEATargetAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

$oEATargetAddr->writeWord(0xFFFE);
assert(
    0xFFFFFFFE === $oEATargetAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

assertThrown(
    'Address Register byte write',
    function() use ($oEATargetAddr) {
        $oEATargetAddr->writeByte(0);
    },
    LogicException::class
);

$oEATargetBus = new Processor\EATarget\Bus($oProcessor->getMemory());

$oEATargetBus->bind(4); // Address

// Note big endian memory
$oEATargetBus->writeLong(0x11111111);
$oEATargetBus->writeWord(0x2222);
$oEATargetBus->writeByte(0x33);

assert(
    '33221111' === $oProcessor->getMemory()->getDump(4, 4),
    new LogicException('Memory contents incorrect after EA write')
);

assert(
    0x33221111 === $oEATargetBus->readLong(),
    new LogicException('EA memory read incorrect')
);


echo "EA Mode Tests Passed\n";
