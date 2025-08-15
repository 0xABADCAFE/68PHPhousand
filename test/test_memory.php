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

use ABadCafe\G8PHPhousand\Device;
use Throwable;
use LogicException;
use ValueError;

require 'bootstrap.php';

// Simple assertion based tests for constructor
assertThrown(
    'Constructor length assertion thrown',
    function() {
        $oMemory = new Device\Memory(1, 0);
    },
    ValueError::class
);

assertThrown(
    'Constructor length assertion thrown',
    function() {
        $oMemory = new Device\Memory(-4, 0);
    },
    ValueError::class
);

assertThrown(
    'Constructor location alignment assertion thrown',
    function() {
        $oMemory = new Device\Memory(0, 1);
    },
    ValueError::class
);

assertThrown(
    'Constructor location alignment assertion thrown',
    function() {
        $oMemory = new Device\Memory(0, -4);
    },
    ValueError::class
);

// Test the read-write behaviours
$oMemory = new Device\Memory(16, 0);
assertSame(
    '00000000000000000000000000000000',
    $oMemory->getDump(0, 16),
    'Initial state'
);

$oMemory->writeByte(8, 0x69);
$oMemory->writeLong(4, 0xABADCAFE);
$oMemory->writeWord(2, 0x4545);
assertSame(
    '00004545abadcafe6900000000000000',
    $oMemory->getDump(0, 16),
    'After written'
);
assertSame(
    0x69,
    $oMemory->readByte(8),
    'readByte(8) value'
);
assertSame(
    0x4545,
    $oMemory->readWord(2),
    'readWord(2) value'
);
assertSame(
    0xABADCAFE,
    $oMemory->readLong(4),
    'readLong(4) value'
);

$oMemory->writeWord(6, 0x1234);
assertSame(
    '00004545abad12346900000000000000',
    $oMemory->getDump(0, 16),
    'overwritten state'
);
assertSame(
    0xABAD1234,
    $oMemory->readLong(4),
    'readLong(4) value'
);

$oMemory->softReset();
assertSame(
    '00004545abad12346900000000000000',
    $oMemory->getDump(0, 16),
    'soft reset state'
);
assertSame(
    $oMemory->readLong(4),
    0xABAD1234,
    'readLong(4) value'
);
$oMemory->hardReset();

assertSame(
    '00000000000000000000000000000000',
    $oMemory->getDump(0, 16),
    'hard reset state'
);
assertSame(
    $oMemory->readLong(4),
    0,
    'readLong(4) value'
);

echo "Memory Tests passed\n";
