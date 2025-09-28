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

namespace ABadCafe\G8PHPhousand;

use LogicException;

error_reporting(-1);
require  __DIR__ . '/../src/bootstrap.php';

echo "Benchmarking DBF loop\n";

$aOpcacheStatus = opcache_get_status();
if (isset($aOpcacheStatus['jit'])) {
    echo "JIT parameters: ";
    print_r($aOpcacheStatus['jit']);
} else {
    echo "JIT mode disabled\n";
}

const BASE_ADDRESS = 0x4;

$oObjectCode = (new TestHarness\Assembler\Vasmm68k())->assemble("
	move.w #-1,d0
.loop:
	dbra d0,.loop
	rts
",
    BASE_ADDRESS
);


