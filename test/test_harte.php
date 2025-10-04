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

require 'bootstrap.php';

$oTomHarte = new TestHarness\TomHarte('TomHarte/680x0');
$oTomHarte->runAllExcept(
    [
        // Not implemented yet
        'ABCD',
        'ADDX.b',
        'ADDX.w',
        'ADDX.l',
        'CHK',
        'DIVS',
        'DIVU',
        'JMP',
        'JSR',
        'LINK',
        'MOVEM.w',
        'MOVEM.l',
        'MOVEP.w',
        'MOVEP.l',
        'MOVEtoUSP',
        'NBCD',
        'NEGX.b',
        'NEGX.w',
        'NEGX.l',
        'RESET',
        'RTE',
        'RTR',
        'SBCD',
        'SUBX.b',
        'SUBX.w',
        'SUBX.l',
        'TAS',
        'TRAP',
        'TRAPV',
        'UNLINK',
    ]
);
