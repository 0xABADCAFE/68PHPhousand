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


//$oMemory = new Device\Memory(64, 0);

$oMemory = new Device\SparseRAM(64);

$oProcessor = new class($oMemory) extends Processor\Base
{
    public function getName(): string
    {
        return 'Benchmark CPU';
    }

    public function getMemory(): Device\Memory
    {
        return $this->oOutside;
    }

    /** Expose the indexed data regs for testing */
    public function getDataRegs(): Processor\RegisterSet
    {
        return $this->oDataRegisters;
    }

    /** Expose the indexed addr regs for testing */
    public function getAddrRegs(): Processor\RegisterSet
    {
        return $this->oAddressRegisters;
    }

    public function executeUncached(int $iAddress): float
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        $tStart = microtime(true);

        try {
            while(true) {
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        $fTime = microtime(true) - $tStart;

//         printf(
//             "Executed %d dbf d0,-2 instructions in %.6f seconds: %.3f IPS\n",
//             $iCount,
//             $fTime,
//             $iCount / $fTime
//         );

        return $iCount / $fTime;
    }

    public function executeCached(int $iAddress): float
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        $tStart = microtime(true);

        // Experimental opcode cache
        $aInstCache = [];
        try {
            while(true) {
                $iOpcode = $aInstCache[$this->iProgramCounter] ?? (
                    $aInstCache[$this->iProgramCounter] = $this->oOutside->readWord(
                        $this->iProgramCounter
                    )
                );
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        $fTime = microtime(true) - $tStart;

//         printf(
//             "Executed %d dbf d0,-2 instructions in %.6f seconds: %.3f IPS\n",
//             $iCount,
//             $fTime,
//             $iCount / $fTime
//         );

        return $iCount / $fTime;
    }
};

$oMemory->writeWord(0x4, Processor\Opcode\IFlow::OP_DBF);
$oMemory->writeWord(0x6, -2);
$oMemory->writeWord(0x8, Processor\Opcode\IPrefix::OP_STOP);

$fTotal = 0;
for ($i = 0; $i < 100; ++$i) {
    //printf("Run %3d: ", $i + 1);
    $oProcessor->getDataRegs()->iReg0 = 65535;
    $fTotal += $oProcessor->executeUncached(0x4);
}

printf("Average (nocache) over 100 runs: %.3f IPS\n", 0.01 * $fTotal);

$fTotal = 0;
for ($i = 0; $i < 100; ++$i) {
    //printf("Run %3d: ", $i + 1);
    $oProcessor->getDataRegs()->iReg0 = 65535;
    $fTotal += $oProcessor->executeCached(0x4);
}

printf("Average (opcode cache) over 100 runs: %.3f IPS\n", 0.01 * $fTotal);

