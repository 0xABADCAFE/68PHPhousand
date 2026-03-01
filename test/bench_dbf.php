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

$aOpcacheStatus = opcache_get_status();
if (isset($aOpcacheStatus['jit'])) {
    echo "JIT parameters: ";
    print_r($aOpcacheStatus['jit']);
} else {
    echo "JIT mode disabled\n";
}

$oBenchmark = new TestHarness\SingleOperationBenchmark(false);

$oBenchmark->run([
    'move.l d1,d2',
    'move.l d1,a0',
    'move.w $0,d1',
    'move.w (a0),d1',
    'moveq  #0,d1'
]);

