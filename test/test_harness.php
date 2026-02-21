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

    dc.l    $800        ; Initial Supervisor Stack Pointer
    dc.l    BootCode    ; Initial Program Counter
    dc.l    AccessFault ; Bus access fault
    ds.l    253         ; (rest of) default vector table

BootCode:
    lea     hello_string,a0
    bsr     WriteString

    tst.w   $abadcafe ; this should trigger an access fault

    lea     revovery_string,a0
    bsr     WriteString

    stop #0

WriteString:
    move.l #SERIAL_OUT,a1
.write_serial:
    move.b  (a0)+,(a1)
    bne.b   .write_serial
    rts

AccessFault:
    lea access_fault_string,a0
    bsr WriteString

    ; Brute force stack correction
    ori.w  #$0700,sr    ; Disable all interrupts to prevent manipulation mishap.
    addq.l #8,a7        ; Fix the stack offset to drop the extra data
    addq.l #2,2(a7)     ; Step over the busted instruction
    rte                 ; Fixes SR/CCR

hello_string:
    dc.b $a,"Hello 68K World!!",$a,0

access_fault_string:
    dc.b $a,"Guru Meditation: Access Fault",$a,0

revovery_string:
    dc.b $a,"Recovered. Well, as much as you can do after something that silly.",$a,0

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
