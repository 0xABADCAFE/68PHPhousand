<?php

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Test;

require_once 'bootstrap.php';

use ABadCafe\G8PHPhousand\TestHarness\CPU;
use ABadCafe\G8PHPhousand\Device\Memory\SparseWordRAM;
use ABadCafe\G8PHPhousand\Processor\IRegister;

// Create a CPU
$oCPU = new CPU(new SparseWordRAM());
$oBus = $oCPU->getOutside();

// Set and get the user stack pointer
$oCPU->setRegister('a7', 0x12345678);
assertSame(0x12345678, $oCPU->getRegister('a7'), 'Set/Get USP');

// Switch to supervisor mode
$oCPU->setSupervisorMode(true);

// Check that the supervisor bit is set
assertSame(IRegister::SR_MASK_SUPER, $oCPU->getRegister('sr') & IRegister::SR_MASK_SUPER, 'Supervisor bit is set');

// Set and get the supervisor stack pointer
$oCPU->setRegister('a7', 0x87654321);
assertSame(0x87654321, $oCPU->getRegister('a7'), 'Set/Get SSP');

// Switch back to user mode and check USP is unchanged
$oCPU->setSupervisorMode(false);
assertSame(0x12345678, $oCPU->getRegister('a7'), 'USP is unchanged');

// Switch back to supervisor mode to test MOVE to SR
$oCPU->setSupervisorMode(true);

// Now use MOVE to SR to switch back to user mode
$oCPU->setRegister('d0', 0x0000);
$oBus->writeWord(0, 0x46C0); // move.w d0, sr
$oCPU->executeAt(0);

// Check that the supervisor bit is clear
assertSame(0, $oCPU->getRegister('sr') & IRegister::SR_MASK_SUPER, 'Supervisor bit is clear');

// Check that we are back to the user stack pointer
assertSame(0x12345678, $oCPU->getRegister('a7'), 'Back to USP');

echo "All Supervisor Tests Passed!\n";
