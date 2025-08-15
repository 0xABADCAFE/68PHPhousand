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
$oEAModeDataRegister = new Processor\EAMode\Direct\DataRegister($oDataRegisters, Processor\IRegister::D0);

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
$oEAModeAddressRegister = new Processor\EAMode\Direct\AddressRegister(
    $oAddressRegisters,
    Processor\IRegister::A0
);

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
$oEAModeIndirect = new Processor\EAMode\Indirect\Basic($oAddressRegisters, Processor\IRegister::A0, $oMemory);
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

echo "Testing Address Register Indirect with displacement...";

// Basic Indirect
$oMemory->writeLong(0x10, 0x11223344);
$oMemory->writeLong(0x14, 0x55667788);
$oMemory->writeLong(0x18, 0x87654321);

// extension words
$oMemory->writeWord(0x0, (-4 & 0xFFFF));
$oMemory->writeWord(0x2, 0);
$oMemory->writeWord(0x4, 4);

$oAddressRegisters->iReg0 = 0x14;
$iProgramCounter = 0;
$oEAModeIndirect = new Processor\EAMode\Indirect\Displacement($iProgramCounter, $oAddressRegisters, Processor\IRegister::A0, $oMemory);

assert(
    0x11223344 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for indirect')
);

assert(
    2 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() with displacement')
);

assert(
    0x55667788 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for indirect')
);

assert(
    4 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() with displacement')
);

assert(
    0x87654321 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for indirect')
);

assert(
    6 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readLong() with displacement')
);

$iProgramCounter = 0;

assert(
    0x1122 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    2 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);

assert(
    0x5566 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    4 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);

assert(
    0x8765 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    6 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);


$oAddressRegisters->iReg0 = 0x16;
$iProgramCounter = 0;


assert(
    0x3344 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    2 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);

assert(
    0x7788 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    4 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);

assert(
    0x4321 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assert(
    6 === $iProgramCounter,
    new AssertionFailureException('Incorrect PC after readWord() with displacement')
);


echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect Post Increment...";

$oAddressRegisters->iReg0 = 0;
$oEAModeIndirect = new Processor\EAMode\Indirect\PostIncrement($oAddressRegisters, Processor\IRegister::A0, $oMemory);

$oMemory->writeLong(0x0, 0x11223344);
$oMemory->writeLong(0x4, 0x55667788);

assert(
    0x11223344 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for Post Increment')
);

assert(
    0x4 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Post Increment')
);

assert(
    0x5566 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for Post Increment')
);

assert(
    0x6 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Post Increment')
);

assert(
    0x77 === $oEAModeIndirect->readByte(),
    new AssertionFailureException('Incorrect readByte() for Post Increment')
);

assert(
    0x7 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Post Increment')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect Pre Decrement...";

$oEAModeIndirect = new Processor\EAMode\Indirect\PreDecrement($oAddressRegisters, Processor\IRegister::A0, $oMemory);

$oMemory->writeLong(0x0, 0x76543210);
$oMemory->writeLong(0x4, 0xFEDCBA98);
$oAddressRegisters->iReg0 = 0x8;

assert(
    0xFEDCBA98 === $oEAModeIndirect->readLong(),
    new AssertionFailureException('Incorrect readLong() for Pre Decrement')
);

assert(
    0x4 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Pre Decrement')
);

assert(
    0x3210 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for Pre Decrement')
);

assert(
    0x2 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Pre Decrement')
);

assert(
    0x54 === $oEAModeIndirect->readByte(),
    new AssertionFailureException('Incorrect readByte() for Pre Decrement')
);

assert(
    0x1 === $oAddressRegisters->iReg0,
    new AssertionFailureException('Incorrect register value Pre Decrement')
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect Indexed...";

$oEAModeIndirect = new Processor\EAMode\Indirect\Indexed(
    $iProgramCounter,
    $oAddressRegisters,
    $oDataRegisters,
    Processor\IRegister::A0,
    $oMemory
);

$oAddressRegisters->iReg0 = 0x8; // Base Address
$oDataRegisters->iReg5    = 6;   // Index value

$iExtensionWord = Processor\IOpcode::BXW_REG_D5|0x02;
$oMemory->writeWord(0x0, $iExtensionWord);
$oMemory->writeWord(0x10, 0x2357);
$iProgramCounter = 0;

assert(
    0x2357 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for Pre Decrement')
);

// TODO we need a few more tests here

echo "OK\n";
