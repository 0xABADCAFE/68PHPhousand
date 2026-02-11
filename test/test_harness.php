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

    mc68010

    move.l #$800,sp

    move.l data,d0
    movec d0,vbr
    movec vbr,d1
    move.l #$FFFFFFFF,d2
    movec usp,d2
    stop #0
data:
    dc.l $ABADCAFE
',
    BASE_ADDRESS
);


$oMemory = new Device\Memory\SparseWordRAM();

$oTestCPU = new TestHarness\CPU($oMemory);
$oTestCPU
    ->asSupervisor()
    ->setRegister('usp', 0x12345678)
    ->executeVerbose($oObjectCode);
