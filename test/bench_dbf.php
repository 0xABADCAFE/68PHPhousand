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

$oMemory = new Device\Memory\CodeROM($oObjectCode->sCode, $oObjectCode->iBaseAddress);

$oProcessor = new class($oMemory, true) extends Processor\Base
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

        printf(
            "Executed %d instructions in %.6f seconds: %.3f IPS\n",
            $iCount,
            $fTime,
            $iCount / $fTime
        );

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

        printf(
            "Executed %d instructions in %.6f seconds: %.3f IPS\n",
            $iCount,
            $fTime,
            $iCount / $fTime
        );

        return $iCount / $fTime;
    }
};

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

