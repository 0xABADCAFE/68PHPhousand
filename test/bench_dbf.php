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

    public const DEF_UNROLL   = 10;
    public const DEF_LOOPS    = 65536;
    public const DEF_SAMPLES  = 25;

    public const BASE_ADDRESS = 0x400;

    private int $iCountReg;
    private int $iUnroll;
    private int $iSamples;

    private float $fLoopTime;
    private float $fUnitNOP;

    private TestHarness\Assembler\Vasmm68k $oAssembler;

    private TestHarness\CPU $oCPU;

    public function __construct(
        int $iCountReg = 0,
        int $iUnroll   = self::DEF_UNROLL,
        int $iSamples  = self::DEF_SAMPLES
    ) {
        $this->iCountReg     = $iCountReg;
        $this->iUnroll       = $iUnroll;
        $this->iSamples      = $iSamples;
        $this->oAssembler    = new TestHarness\Assembler\Vasmm68k();
        $this->oCPU = new TestHarness\CPU($this->generateROM(null));
        $this->calibrate();
    }

    public function run(string $sOperation)
    {
        $this->oCPU->replaceOutside($this->generateROM($sOperation));
        $fOperationTime = $this->runSamples($sOperation) - $this->fLoopTime;
        $fUnitOperation = $fOperationTime / (self::DEF_LOOPS * $this->iUnroll);
        printf(
            "\tUnit %s %.3f ns, %.3f NOP equivalent\n",
            $sOperation,
            $fUnitOperation * 1e9,
            $fUnitOperation/$this->fUnitNOP
        );
    }

    private function calibrate()
    {
        echo "Calibrating...\n";
        $this->fLoopTime = $this->runSamples('<loop>');
        $fUnitDBF = $this->fLoopTime / self::DEF_LOOPS;
        $this->oCPU->replaceOutside($this->generateROM('nop'));
        $fNopTime = $this->runSamples('nop') - $this->fLoopTime;
        $this->fUnitNOP = $fNopTime / (self::DEF_LOOPS * $this->iUnroll);
        printf(
            "Calibration complete:\n\tUnit NOP %.3f ns\n\tUnit DBF %.3f ns, %.3f NOP equivalent\n",
            $this->fUnitNOP * 1e9,
            $fUnitDBF * 1e9,
            $fUnitDBF/$this->fUnitNOP
        );
    }

    private function runSamples(string $sWhat): float
    {
        echo "Running ", $sWhat, " samples";
        $aSamples = [];
        $i = $this->iSamples;
        while ($i--) {
            $this->oCPU
                ->getDataRegisters()
                ->aIndex[$this->iCountReg] = self::DEF_LOOPS - 1;
            $aSamples[] = $this->oCPU->benchmark(self::BASE_ADDRESS, false);
            echo ".";
        }
        echo "\n";
        $oStats = $this->computeStandardDeviation($aSamples);
        echo "Removing outliers...\n";
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
            "\ndata: ds.l 256\n.loop:\n%s\n\tdbra d%d,.loop\n\tstop #0\n",
            $sOperation ? str_repeat("\t" . $sOperation . "\n", $this->iUnroll) : '',
            $this->iCountReg
        );
        return new Device\Memory\CodeROM(
            $this->oAssembler->assemble($sSourceCode, self::BASE_ADDRESS)->sCode,
            self::BASE_ADDRESS
        );
    }
}

$oBenchmark = new DBFBenchmark();

$oBenchmark->run('move.l $0,d2');

