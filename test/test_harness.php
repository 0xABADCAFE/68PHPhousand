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

require 'bootstrap.php';

const BASE_ADDRESS = 0;

$oObjectCode = (new TestHarness\Assembler\Vasmm68k())->assemble('

    mc68010
    ; org 0

    dc.l    $800     ; Initial Supervisor Stack Pointer
    dc.l    BootCode ; Initial Program Counter
    ds.l    254      ; (rest of) default vector table

Signature:
    dc.l    $abadcafe

BootCode:
    move.l  Signature,d0
    stop #0

    ',
    BASE_ADDRESS
);

$oTestCPU = new TestHarness\CPU(new Device\Memory\SparseWordRAM());
$oTestCPU->resetAndExecute($oObjectCode, true);
