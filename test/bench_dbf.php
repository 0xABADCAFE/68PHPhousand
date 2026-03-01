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

class DBFBenchmark {

    public const DEF_UNROLL   = 20;
    public const DEF_LOOPS    = 65536;
    public const DEF_SAMPLES  = 100;

    public const BASE_ADDRESS = 0x400;

    private array $aInstructions;
    private int $iCountReg;
    private int $iUnroll;
    private int $iSamples;

    private float $fLoopTime;

    private TestHarness\Assembler\Vasmm68k $oAssembler;

    private TestHarness\CPU $oCPU;

    public function __construct(
        array $aInstructions,
        int $iCountReg = 0,
        int $iUnroll   = self::DEF_UNROLL,
        int $iSamples  = self::DEF_SAMPLES
    ) {
        $this->aInstructions = $aInstructions;
        $this->iCountReg     = $iCountReg;
        $this->iUnroll       = $iUnroll;
        $this->iSamples      = $iSamples;
        $this->oAssembler    = new TestHarness\Assembler\Vasmm68k();

        $this->oCPU = new TestHarness\CPU($this->generateROM(null));
        $this->fLoopTime = $this->runSamples();
    }


    private function runSamples(): float
    {
        echo "Timing samples\n";
        $aSamples = [];
        $i = $this->iSamples;
        while ($i--) {
            $this->oCPU
                ->getDataRegisters()
                ->aIndex[$this->iCountReg] = self::DEF_LOOPS - 1;
            $aSamples[] = $this->oCPU->benchmark(self::BASE_ADDRESS, false);
        }
        $oStats = $this->computeStandardDeviation($aSamples);
        echo "\tRemoving outliers...\n";
        $aSamples = array_filter(
            $aSamples,
            function (float $fTime) use ($oStats) {
                return abs($fTime - $oStats->fMean) < $oStats->fStdDeviation;
            }
        );
        $oStats = $this->computeStandardDeviation($aSamples);
        return $oStats->fMean;
    }


    private function computeStandardDeviation(array $aSamples): \stdClass
    {
        $iCount = count($aSamples);
        $fTotal = array_sum($aSamples);
        $fMean  = $fTotal / count($aSamples);
        $fCarry = 0.0;
        foreach ($aSamples as $fTime) {
            $fDiff = $fTime - $fMean;
            $fCarry += $fDiff * $fDiff;
        }
        $fStdDeviation = sqrt($fCarry / $iCount);
        printf(
            "\t%3d Samples, min: %.f, max %.f: mean: %.f, std dev: %.f\n",
            $iCount,
            min($aSamples),
            max($aSamples),
            $fMean,
            $fStdDeviation
        );
        return (object)[
            'fMean' => $fMean,
            'fStdDeviation' => $fStdDeviation
        ];
    }

    public function generateROM(?string $sOperation): Device\Memory\CodeROM
    {
        $sSourceCode = sprintf(
            "\n.loop:\n%s\n\tdbra d%d,.loop\n\tstop #0\n",
            $sOperation ? str_repeat("\t" . $sOperation . "\n", $this->iUnroll) : '',
            $this->iCountReg
        );
        return new Device\Memory\CodeROM(
            $this->oAssembler->assemble($sSourceCode, self::BASE_ADDRESS)->sCode,
            self::BASE_ADDRESS
        );
    }
}

$oBenchmark = new DBFBenchmark([]);

exit;

const BASE_ADDRESS = 0x4;

$oObjectCode = (new TestHarness\Assembler\Vasmm68k())->assemble("
	move.w #-1,d0
.loop:

	dbra d0,.loop
	stop #0

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
            fetch:
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            goto fetch;
        } catch (Processor\Halted $oError) {

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
            fetch:
                $iOpcode = $aInstCache[$this->iProgramCounter] ?? (
                    $aInstCache[$this->iProgramCounter] = $this->oOutside->readWord(
                        $this->iProgramCounter
                    )
                );
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            goto fetch;
        } catch (Processor\Halted $oError) {

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

