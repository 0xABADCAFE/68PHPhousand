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
use ABadCafe\G8PHPhousand\Processor\IRegister;


require 'bootstrap.php';

$oTomHarte = (new TestHarness\TomHarte(
    'TomHarte/680x0',
    new Device\Adapter\WordAligned(
        new Device\Adapter\Address24Bit(
            new Device\Memory\SparseRAM()
        )
    )
))
    ->declareBroken('e502 [ASL.b Q, D2] 1583')
    ->declareBroken('e502 [ASL.b Q, D2] 1761')
    ->declareUndefinedCCR('ABCD', IRegister::CCR_OVERFLOW)
    ->declareUndefinedCCR('NBCD', IRegister::CCR_OVERFLOW)
    ->declareUndefinedCCR('SBCD', IRegister::CCR_OVERFLOW)
    ->includeSupervisorStateChangeCases()
    ->includeExceptionCases()
;

//print_r($oTomHarte->loadSuite('TRAP')->run());
//print_r($oTomHarte->loadSuite('TST.w')->run());

print_r($oTomHarte->loadSuite('TST.w')->run());
print_r($oTomHarte->loadSuite('TST.l')->run());
print_r($oTomHarte->loadSuite('CLR.w')->run());
print_r($oTomHarte->loadSuite('CLR.l')->run());
exit;

$oTomHarte->runAllExcept(
    [
        // Not implemented yet
        'CHK',
        'MOVEtoUSP',   // needs supervisor
        'MOVEfromSR',  // needs supervisor
        'MOVEtoSR',    // needs supervisor
        'MOVEfromUSP', // needs supervisors
        'RESET',
        'RTE',
        'TRAP',
        'TRAPV',
    ]
);
