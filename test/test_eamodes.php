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

$oEAModeData = new Processor\EAMode\Direct\DataRegister($oProcessor->getDataRegs());

$oEAModeData->bind(0);
$oEAModeData->writeLong(0x11111111);
$oEAModeData->writeWord(0x2222);
$oEAModeData->writeByte(0x33);
assert(
    0x11112233 === $oEAModeData->readLong(),
    new LogicException('Invalid EA data register result')
);

$oEAModeAddr = new Processor\EAMode\Direct\AddressRegister($oProcessor->getAddrRegs());
$oEAModeAddr->bind(1);
$oEAModeAddr->writeLong(0x12345678);
assert(
    0x12345678 === $oEAModeAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

$oEAModeAddr->writeWord(0x4321);
assert(
    0x00004321 === $oEAModeAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

$oEAModeAddr->writeWord(0xFFFE);
assert(
    0xFFFFFFFE === $oEAModeAddr->readLong(),
    new LogicException('Invalid EA address register result')
);

assertThrown(
    'Address Register byte write',
    function() use ($oEAModeAddr) {
        $oEAModeAddr->writeByte(0);
    },
    LogicException::class
);

$oEAModeIndirect = new Processor\EAMode\Indirect\Basic(
    $oProcessor->getAddrRegs(),
    $oProcessor->getMemory()
);

$oEAModeIndirect->bind(2); // Address Register

$oProcessor->setRegister('a2', 4);

// Note big endian memory
$oEAModeIndirect->writeLong(0x11111111);
$oEAModeIndirect->writeWord(0x2222);
$oEAModeIndirect->writeByte(0x33);

assert(
    4 === $oProcessor->getRegister('a2'),
    new LogicException('Incorrect address in a2 after predecrement')
);

assert(
    '33221111' === $oProcessor->getMemory()->getDump(4, 4),
    new LogicException('Memory contents incorrect after EA write')
);

assert(
    0x33221111 === $oEAModeIndirect->readLong(),
    new LogicException('EA memory read incorrect')
);

$oProcessor->getMemory()->writeLong(0, 0xABADCAFE);

$oEAModeIndirectPreDecrement = new Processor\EAMode\Indirect\PreDecrement(
    $oProcessor->getAddrRegs(),
    $oProcessor->getMemory()
);
$oEAModeIndirectPreDecrement->bind(2);

assert(
    0xCAFE === $oEAModeIndirectPreDecrement->readWord(),
    new LogicException('EA memory read incorrect')
);

assert(
    2 === $oProcessor->getRegister('a2'),
    new LogicException('Incorrect address in a2 after predecrement')
);

$oEAModeIndirectPreDecrement = new Processor\EAMode\Indirect\PostIncrement(
    $oProcessor->getAddrRegs(),
    $oProcessor->getMemory()
);
$oEAModeIndirectPreDecrement->bind(2);

assert(
    0xCAFE === $oEAModeIndirectPreDecrement->readWord(),
    new LogicException('EA memory read incorrect')
);

assert(
    4 === $oProcessor->getRegister('a2'),
    new LogicException('Incorrect address in a2 post increment')
);
echo "EA Mode Tests Passed\n";
