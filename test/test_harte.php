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
    ->declareBroken('e502 [ASL.b Q, D2] 1583') // D2 register outcome invalid
    ->declareBroken('e502 [ASL.b Q, D2] 1761') // D2 register outcome invalid
    ->declareUndefinedCCR('ABCD', IRegister::CCR_OVERFLOW)
    ->declareUndefinedCCR('NBCD', IRegister::CCR_OVERFLOW)
    ->declareUndefinedCCR('SBCD', IRegister::CCR_OVERFLOW)
    ->declareUndefinedCCR('CHK',  IRegister::CCR_MASK_ZVC)
    ->requireUSPCheck('MoveToUSP')
    ->includeSupervisorStateChangeCases()
    ->includeExceptionCases()

    // For now, ignore changes to the special format word of the exception frame
    ->ignoreMemoryChanged(0x000007F2)
    ->ignoreMemoryChanged(0x000007F3)
    ->ignoreMemoryChanged(0x000007FF)

;

// Last one to fix. Currently half working, likely due to stack frame format issues.
$oTomHarte->loadSuite('RTE')->run();


//$oTomHarte->loadSuite('MOVEfromSR')->run();
//$oTomHarte->loadSuite('MOVEtoSR')->run();

exit;

$oTomHarte->runAllExcept(
    [
        'RTE'
    ]
);
