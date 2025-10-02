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

use ABadCafe\G8PHPhousand\TestHarness;
use ABadCafe\G8PHPhousand\Device;
use Throwable;
use LogicException;
use ValueError;

require 'bootstrap.php';

const BASE_ADDRESS = 0x4;

$oAssembler = new TestHarness\Assembler\Vasmm68k();

$oBinary = $oAssembler->assemble("
    clr.l d0
    moveq #1,d1
    ror.l #1,d1
    move.w #-32768,d2
    move.b #-128,d3
    move.l #65536,a7
    bsr .bsr_test
    stop #0

.bsr_test:
    move.l #-1,d0
    rts",
    BASE_ADDRESS
);


$oMemory = new Device\Memory\SparseWordRAM();
TestHarness\Memory::loadObjectCode($oMemory, $oBinary);

$oTestCPU = new TestHarness\CPU($oMemory);
$oTestCPU->executeVerbose(BASE_ADDRESS);
