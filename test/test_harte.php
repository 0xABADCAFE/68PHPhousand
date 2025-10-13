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

use ABadCafe\G8PHPhousand\Processor\IRegister;


require 'bootstrap.php';

$oTomHarte = (new TestHarness\TomHarte('TomHarte/680x0'))
    ->declareBroken('e502 [ASL.b Q, D2] 1583')
    ->declareBroken('e502 [ASL.b Q, D2] 1761')
    ->declareUndefinedCCR('ABCD', IRegister::CCR_OVERFLOW);

//print_r($oTomHarte->loadSuite('SUBX.b')->run());
//print_r($oTomHarte->loadSuite('SUBX.w')->run());
//print_r($oTomHarte->loadSuite('SUBX.l')->run());

//print_r($oTomHarte->loadSuite('Scc')->run());
//exit;

$oTomHarte->runAllExcept(
    [
        // Not implemented yet
        'CHK',
        'MOVEM.w',
        'MOVEM.l',
        'MOVEP.w',
        'MOVEP.l',
        'MOVEtoUSP',   // needs supervisor
        'MOVEfromSR',  // needs supervisor
        'MOVEtoSR',    // needs supervisor
        'MOVEfromUSP', // needs supervisors
        'NBCD',
        'RESET',
        'RTE',
        'SBCD',
        'TRAP',
        'TRAPV',
    ]
);
