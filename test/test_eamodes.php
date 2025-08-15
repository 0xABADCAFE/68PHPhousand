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
$oEAModeDataRegister = new Processor\EAMode\Direct\DataRegister(
    $oDataRegisters,
    Processor\IRegister::D0
);

// Test 1 - Write/Read Long
$oEAModeDataRegister->writeLong(0x11111111);
assertSame(
    0x11111111,
    $oEAModeDataRegister->readLong(),
    'readLong() from d0'
);

// Test 2 - Write/Read Word
$oEAModeDataRegister->writeWord(0x2222); // Fill lower 16 bits
assertSame(
    0x2222,
    $oEAModeDataRegister->readWord(),
    'readWord() from d0'
);
assertSame(
    0x11112222,
    $oEAModeDataRegister->readLong(),
    'readLong() from d0'
);

// Test 3 - Write/Read Byte
$oEAModeDataRegister->writeByte(0x33); // Fill lowest 8 bits
assertSame(
    0x33,
    $oEAModeDataRegister->readByte(),
    'readByte() from d0'
);
assertSame(
    0x11112233,
    $oEAModeDataRegister->readLong(),
    'readLong() from d0'
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
assertSame(
    0x12345678,
    $oEAModeAddressRegister->readLong(),
    'readLong() from a0'
);
assertSame(
    0x5678,
    $oEAModeAddressRegister->readWord(),
    'readWord() from a0'
);

$oEAModeAddressRegister->writeWord(0x4321);
assertSame(
    0x4321,
    $oEAModeAddressRegister->readWord(),
    'readWord() from a0'
);
assertSame(
    0x4321,
    $oEAModeAddressRegister->readLong(),
    'readLong() from a0'
);

$oEAModeAddressRegister->writeWord(0xFFFE);
assertSame(
    0xFFFE,
    $oEAModeAddressRegister->readWord(),
    'readWord() from a0'
);
assertSame(
    0xFFFFFFFE,
    $oEAModeAddressRegister->readLong(),
    'readLong() from a0'
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
assertSame(
    0xABADCAFE,
    $oEAModeImmediate->readLong(),
    'readLong() for immediate'
);
assertSame(
    8,
    $iProgramCounter,
    'PC after readLong() immediate'
);

$iProgramCounter = 4;
assertSame(
    0xABAD,
    $oEAModeImmediate->readWord(),
    'readWord() for immediate'
);
assertSame(
    6,
    $iProgramCounter,
    'PC after readWord() immediate'
);

// Byte accesses should be the least significant bit of the instruction word
$iProgramCounter = 4;
assertSame(
    0xAD,
    $oEAModeImmediate->readByte(),
    'readByte() for immediate'
);
assertSame(
    6,
    $iProgramCounter,
    'PC after readByte() immediate'
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect...";

// Basic Indirect
$oMemory->writeLong(0x10, 0x11223344);
$oAddressRegisters->iReg0 = 0x10;
$oEAModeIndirect = new Processor\EAMode\Indirect\Basic($oAddressRegisters, Processor\IRegister::A0, $oMemory);
assertSame(
    0x11223344,
    $oEAModeIndirect->readLong(),
    'readLong() for indirect'
);
assertSame(
    0x10,
    $oAddressRegisters->iReg0,
    'Register unchanged for indirect'
);
assertSame(
    0x1122,
    $oEAModeIndirect->readWord(),
    'readWord() for indirect'
);
assertSame(
    0x10,
    $oAddressRegisters->iReg0,
    'Register unchanged for indirect'
);
assertSame(
    0x11,
    $oEAModeIndirect->readByte(),
    'readByte() for indirect'
);
assertSame(
    0x10,
    $oAddressRegisters->iReg0,
    'register unchanged for indirect'
);

$oEAModeIndirect->writeLong(0x55667788);
assertSame(
    0x55667788,
    $oMemory->readLong(0x10),
    'memory readLong() after indirect writeLong()'
);

$oEAModeIndirect->writeWord(0x99AA);
assertSame(
    0x99AA7788,
    $oMemory->readLong(0x10),
    'Memory readLong() after indirect writeWord()'
);

$oEAModeIndirect->writeByte(0xBB);
assertSame(
    0xBBAA7788,
    $oMemory->readLong(0x10),
    'Memory readLong() after indirect writeByte()'
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

assertSame(
    0x11223344,
    $oEAModeIndirect->readLong(),
    'readLong() for indirect'
);

assertSame(
    2,
    $iProgramCounter,
    'PC after readLong() with displacement'
);

assertSame(
    0x55667788,
    $oEAModeIndirect->readLong(),
    'readLong() for indirect'
);

assertSame(
    4,
    $iProgramCounter,
    'PC after readLong() with displacement'
);

assertSame(
    0x87654321,
    $oEAModeIndirect->readLong(),
    'readLong() for indirect'
);

assertSame(
    6,
    $iProgramCounter,
    'PC after readLong() with displacement'
);

$iProgramCounter = 0;

assertSame(
    0x1122,
    $oEAModeIndirect->readWord(),
    'readWord() for indirect'
);

assertSame(
    2,
    $iProgramCounter,
    'PC after readWord() with displacement'
);

assertSame(
    0x5566,
    $oEAModeIndirect->readWord(),
    'readWord() from (a0)'
);

assertSame(
    4,
    $iProgramCounter,
    'PC after readWord() with displacement'
);

assertSame(
    0x8765,
    $oEAModeIndirect->readWord(),
    'readWord() for indirect'
);

assertSame(
    6,
    $iProgramCounter,
    'PC after readWord() with displacement'
);


$oAddressRegisters->iReg0 = 0x16;
$iProgramCounter = 0;

assertSame(
    0x3344,
    $oEAModeIndirect->readWord(),
    'readWord() for indirect'
);

assertSame(
    2,
    $iProgramCounter,
    'PC after readWord() with displacement'
);

assert(
    0x7788 === $oEAModeIndirect->readWord(),
    new AssertionFailureException('Incorrect readWord() for indirect')
);

assertSame(
    4,
    $iProgramCounter,
    'PC after readWord() with displacement'
);

assertSame(
    0x4321,
    $oEAModeIndirect->readWord(),
    'readWord() for indirect'
);

assertSame(
    6,
    $iProgramCounter,
    'PC after readWord() with displacement'
);


echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect Post Increment...";

$oAddressRegisters->iReg0 = 0;
$oEAModeIndirect = new Processor\EAMode\Indirect\PostIncrement($oAddressRegisters, Processor\IRegister::A0, $oMemory);

$oMemory->writeLong(0x0, 0x11223344);
$oMemory->writeLong(0x4, 0x55667788);

assertSame(
    0x11223344,
    $oEAModeIndirect->readLong(),
    'readLong() for Post Increment'
);

assertSame(
    0x4,
    $oAddressRegisters->iReg0,
    'register value Post Increment'
);

assertSame(
    0x5566,
    $oEAModeIndirect->readWord(),
    'readWord() for Post Increment'
);

assertSame(
    0x6,
    $oAddressRegisters->iReg0,
    'register value Post Increment'
);

assertSame(
    0x77,
    $oEAModeIndirect->readByte(),
    'readByte() for Post Increment'
);

assertSame(
    0x7,
    $oAddressRegisters->iReg0,
    'register value Post Increment'
);

echo "OK\n";

/////////////////////////////////////////////////////////////////////////////////////////`

echo "Testing Address Register Indirect Pre Decrement...";

$oEAModeIndirect = new Processor\EAMode\Indirect\PreDecrement($oAddressRegisters, Processor\IRegister::A0, $oMemory);

$oMemory->writeLong(0x0, 0x76543210);
$oMemory->writeLong(0x4, 0xFEDCBA98);
$oAddressRegisters->iReg0 = 0x8;

assertSame(
    0xFEDCBA98,
    $oEAModeIndirect->readLong(),
    'readLong() for Pre Decrement'
);

assertSame(
    0x4,
    $oAddressRegisters->iReg0,
    'register value Pre Decrement'
);

assertSame(
    0x3210,
    $oEAModeIndirect->readWord(),
    'readWord() for Pre Decrement'
);

assertSame(
    0x2,
    $oAddressRegisters->iReg0,
    'register value Pre Decrement'
);

assertSame(
    0x54,
    $oEAModeIndirect->readByte(),
    'readByte() for Pre Decrement'
);

assertSame(
    0x1,
    $oAddressRegisters->iReg0,
    'register value Pre Decrement'
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

assertSame(
    0x2357,
    $oEAModeIndirect->readWord(),
    'readWord() for Pre Decrement'
);

// TODO we need a few more tests here

echo "OK\n";
