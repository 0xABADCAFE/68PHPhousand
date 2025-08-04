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

require 'bootstrap.php';

$oDataRegisters    = new Processor\RegisterSet();

echo "Testing Data Register Direct...";

// Test Data Register Direct mode.
$oEAModeDataRegister = new Processor\EAMode\Direct\DataRegister($oDataRegisters);
$oEAModeDataRegister->bind(Processor\IRegister::D0);

// Test 1 - Write/Read Long
$oEAModeDataRegister->writeLong(0x11111111);
assert(
    0x11111111 === $oEAModeDataRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from data register')
);

// Test 2 - Write/Read Word
$oEAModeDataRegister->writeWord(0x2222); // Fill lower 16 bits
assert(
    0x2222 === $oEAModeDataRegister->readWord(),
    new AssertionFailureException('Incorrect readWord() from data register')
);
assert(
    0x11112222 === $oEAModeDataRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from data register')
);

// Test 3 - Write/Read Byte
$oEAModeDataRegister->writeByte(0x33); // Fill lowest 8 bits
assert(
    0x33 === $oEAModeDataRegister->readByte(),
    new AssertionFailureException('Incorrect readByte() from data register')
);
assert(
    0x11112233 === $oEAModeDataRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from data register')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Direct...";

$oAddressRegisters = new Processor\RegisterSet();

// Test Address Register Direct mode.
$oEAModeAddressRegister = new Processor\EAMode\Direct\AddressRegister($oAddressRegisters);
$oEAModeAddressRegister->bind(Processor\IRegister::A0);

$oEAModeAddressRegister->writeLong(0x12345678);
assert(
    0x12345678 === $oEAModeAddressRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from address register')
);
assert(
    0x5678 === $oEAModeAddressRegister->readWord(),
    new AssertionFailureException('Incorrect readWord() from address register')
);

$oEAModeAddressRegister->writeWord(0x4321);
assert(
    0x4321 === $oEAModeAddressRegister->readWord(),
    new AssertionFailureException('Incorrect readWord() from address register')
);
assert(
    0x4321 === $oEAModeAddressRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from address register')
);

$oEAModeAddressRegister->writeWord(0xFFFE);
assert(
    0xFFFE === $oEAModeAddressRegister->readWord(),
    new AssertionFailureException('Incorrect readWord() from address register')
);
assert(
    0xFFFFFFFE === $oEAModeAddressRegister->readLong(),
    new AssertionFailureException('Incorrect readLong() from address register')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

// Test Immediate Mode
// After accessing the immediate by the appropriate size, the PC should advance by the
// size of the read.

echo "Testing Immediate...";

$oMemory           = new Device\Memory(64, 0);
$iProgramCounter   = 4;

$oEAModeImmediate = new Processor\EAMode\Direct\Immediate($iProgramCounter, $oMemory);
$oMemory->writeLong(4, 0xABADCAFE);
assert(
    0xABADCAFE === $oEAModeImmediate->readLong(),
    new AssertionFailureException('Incorrect readLong() for immediate')
);
assert(
    8 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() immediate')
);

$iProgramCounter = 4;
assert(
    0xABAD === $oEAModeImmediate->readWord(),
    new AssertionFailureException('Incorrect readLong() for immediate')
);
assert(
    6 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() immediate')
);

// Byte accesses should be the least significant bit of the instruction word
$iProgramCounter = 4;
assert(
    0xAD === $oEAModeImmediate->readByte(),
    new AssertionFailureException('Incorrect readByte() for immediate')
);
assert(
    6 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() immediate')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect...";

// Basic Indirect
$oMemory->writeLong(0x10, 0x11223344);
$oAddressRegisters->iReg0 = 0x10;
$oEAModeIndirect = new Processor\EAMode\Indirect\Basic($oAddressRegisters, $oMemory);
$oEAModeIndirect->bind(Processor\IRegister::A0);
assert(
    0x11223344 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for indirect')
);
assert(
    0x10 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register modification for indirect')
);
assert(
    0x1122 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);
assert(
    0x10 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register modification for indirect')
);
assert(
    0x11 === $oEAModeIndirect->readByte(),
    new AssertionFailureException('Incorrect readByte() for indirect')
);
assert(
    0x10 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register modification for indirect')
);

$oEAModeIndirect->writeLong(0x55667788);
assert(
    0x55667788 === $oMemory->readLong(0x10),
    new AssertionFailureException('Incorrect memory readLong() after indirect writeLong()')
);

$oEAModeIndirect->writeWord(0x99AA);
assert(
    0x99AA7788 === $oMemory->readLong(0x10),
    new AssertionFailureException('Incorrect memory readLong() after indirect writeWord()')
);

$oEAModeIndirect->writeByte(0xBB);
assert(
    0xBBAA7788 === $oMemory->readLong(0x10),
    new AssertionFailureException('Incorrect memory readLong() after indirect writeByte()')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`
