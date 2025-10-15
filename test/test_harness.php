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

const BASE_ADDRESS = 0x400;

$oObjectCode = (new TestHarness\Assembler\Vasmm68k())->assemble('
    movem.l d0/d2/d4/d6,$1234(a0)

    nop

    movem.l d0/d2/d4/d6,($1234).w

    nop
;    move.b #127,d0
;    move.b #-2,d1
;    cmp.b d0,d1
    stop #0
data:
    dc.l $ABADCAFE
',
    BASE_ADDRESS
);


$oMemory = new Device\Memory\SparseWordRAM();

$oTestCPU = new TestHarness\CPU($oMemory);
$oTestCPU->executeVerbose($oObjectCode);
