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
assert('00000000000000000000000000000000' === $oMemory->getDump(0, 16), new LogicException('Failed asserting initial state'));

$oMemory->writeByte(8, 0x69);
$oMemory->writeLong(4, 0xABADCAFE);
$oMemory->writeWord(2, 0x4545);
assert('00004545abadcafe6900000000000000' === $oMemory->getDump(0, 16), new LogicException('Failed asserting written state'));
assert($oMemory->readByte(8) === 0x69,       new LogicException('Failed asserting readByte() value'));
assert($oMemory->readWord(2) === 0x4545,     new LogicException('Failed asserting readWord() value'));
assert($oMemory->readLong(4) === 0xABADCAFE, new LogicException('Failed asserting readLong() value'));

$oMemory->writeWord(6, 0x1234);
assert('00004545abad12346900000000000000' === $oMemory->getDump(0, 16), new LogicException('Failed asserting overwritten state'));
assert($oMemory->readLong(4) === 0xABAD1234, new LogicException('Failed asserting readLong() value'));

$oMemory->softReset();
assert('00004545abad12346900000000000000' === $oMemory->getDump(0, 16), new LogicException('Failed asserting soft reset state'));
assert($oMemory->readLong(4) === 0xABAD1234, new LogicException('Failed asserting readLong() value'));
$oMemory->hardReset();

assert('00000000000000000000000000000000' === $oMemory->getDump(0, 16),  new LogicException('Failed asserting hard reset state'));
assert($oMemory->readLong(4) === 0, new LogicException('Failed asserting readLong() value'));
echo "All Tests passed\n";
