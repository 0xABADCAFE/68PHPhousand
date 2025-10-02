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

$oObjectCode = (new TestHarness\Assembler\Vasmm68k())->assemble('
    move.l data,d0
    ror.l #4,d0
    stop #0
data:
    dc.l $ABADCAFE
',
    BASE_ADDRESS
);

printf(
    "Source:\n%s\nAssembled: %s\n",
    $oObjectCode->sSource,
    bin2hex($oObjectCode->sCode)
);

$oMemory = new Device\Memory\SparseWordRAM();
TestHarness\Memory::loadObjectCode($oMemory, $oObjectCode);

$oTestCPU = new TestHarness\CPU($oMemory);
$oTestCPU->executeVerbose(BASE_ADDRESS);
