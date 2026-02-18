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

SERIAL_OUT EQU $10000

    dc.l    $800     ; Initial Supervisor Stack Pointer
    dc.l    BootCode ; Initial Program Counter
    ds.l    254      ; (rest of) default vector table

BootCode:
    lea     hello,a0
    move.l  #SERIAL_OUT,a1

.write_serial:
    addq    #1,d1
    move.b  (a0)+,(a1)
    bne.b   .write_serial

    stop #0

hello:
    dc.b $a,"hello 68K world",$a,0

    ',
    BASE_ADDRESS
);

$oDeviceMap = new Device\PagedMap(8); // 256 bytes

$oDeviceMap->add(new Device\Memory\CodeROM($oObjectCode->sCode, $oObjectCode->iBaseAddress));
$oDeviceMap->add(new Device\Memory\SparseWordRAM(1024, 1024+256));
$oDeviceMap->add(new Device\SerialConsoleOutput(0x10000));

$oTestCPU = new TestHarness\CPU($oDeviceMap);

echo "Reset and execute. Emulation output follows\n";

$oTestCPU->softReset();
$oTestCPU->execute();

echo "\nEmulation ended\n";

$oTestCPU->dumpMachineState(null);
