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
use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

require 'bootstrap.php';

$oProcessor = new class(new Device\Memory\SparseRAM()) extends TestHarness\CPU
{
    public function testCCRBit(
        string $sDescription,
        int    $iOpcode,
        int    $iCCRBit,
        bool   $bClearResult,
        bool   $bSetResult
    ) {
        $this->oOutside->writeWord(0x04, $iOpcode);
        // Go through all the bit combinations

        $iClearResult = $bClearResult ? 0xFF : 0;
        $iSetResult   = $bSetResult   ? 0xFF : 0;

        $iValueInit   = 0x12345678;

        for ($i = 0; $i < 32; ++$i) {
            $this->iConditionRegister    = $i;
            $this->oDataRegisters->iReg0 = $iValueInit;

            $iExpect = ($iValueInit & ~0xFF) | (($i & $iCCRBit) ? $iSetResult : $iClearResult);

            $this->executeAt(0x04);
            printf("\t CCR<$%02X> : %s - d0: $%08X -> $%08X ", $i, $sDescription, $iValueInit, $iExpect);
            assertSame(
                $iExpect,
                $this->oDataRegisters->iReg0,
                'Expected value'
            );
            echo "OK\n";
        }
    }
};


echo "Testing Set if Carry Clear\n";
$oProcessor->testCCRBit(
    'scc',
    Processor\Opcode\IConditional::OP_SCC,
    Processor\IRegister::CCR_CARRY,
    true,
    false
);

echo "Testing Set if Carry Set\n";
$oProcessor->testCCRBit(
    'scs',
    Processor\Opcode\IConditional::OP_SCS,
    Processor\IRegister::CCR_CARRY,
    false,
    true
);

///////////////////////////////////////////////////////////////////////////////////////////////////

echo "Testing Set if Overflow Clear\n";
$oProcessor->testCCRBit(
    'svc',
    Processor\Opcode\IConditional::OP_SVC,
    Processor\IRegister::CCR_OVERFLOW,
    true,
    false
);

echo "Testing Set if Overflow Set\n";
$oProcessor->testCCRBit(
    'svs',
    Processor\Opcode\IConditional::OP_SVS,
    Processor\IRegister::CCR_OVERFLOW,
    false,
    true
);


echo "Testing Set if Zero Clear (Not Equal)\n";
$oProcessor->testCCRBit(
    'sne',
    Processor\Opcode\IConditional::OP_SNE,
    Processor\IRegister::CCR_ZERO,
    true,
    false
);

echo "Testing Set if Zero Set (Equal)\n";
$oProcessor->testCCRBit(
    'seq',
    Processor\Opcode\IConditional::OP_SEQ,
    Processor\IRegister::CCR_ZERO,
    false,
    true
);

echo "Testing Set if Minus Set (Negative)\n";
$oProcessor->testCCRBit(
    'smi',
    Processor\Opcode\IConditional::OP_SMI,
    Processor\IRegister::CCR_NEGATIVE,
    false,
    true
);
