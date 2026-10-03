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

namespace ABadCafe\G8PHPhousand\TestHarness;

use ABadCafe\G8PHPhousand\Device;

class SingleOperationBenchmark {

    public const DEF_UNROLL   = 10;
    public const DEF_LOOPS    = 65536;
    public const DEF_SAMPLES  = 50;

    public const BASE_ADDRESS = 0x400;

    private bool  $bWithICache;
    private int   $iCountReg;
    private int   $iUnroll;
    private int   $iSamples;
    private float $fLoopTime;
    private float $fUnitNOP;
    private float $fFilterLimit;
    private array $aResults = [];

    private Assembler\Vasmm68k $oAssembler;

    private CPU $oCPU;

    public function __construct(
        bool  $bWithICache,
        float $fFilterLimit = 0.9,
        int   $iCountReg = 0,
        int   $iUnroll   = self::DEF_UNROLL,
        int   $iSamples  = self::DEF_SAMPLES
    ) {
        $this->bWithICache = $bWithICache;
        $this->iCountReg     = $iCountReg;
        $this->iUnroll       = $iUnroll;
        $this->iSamples      = $iSamples;
        $this->fFilterLimit  = $fFilterLimit;
        $this->oAssembler    = new Assembler\Vasmm68k();
        $this->oCPU          = new CPU($this->generateROM(null), $bWithICache);
        $this->calibrate();
    }

    public function run(array $aOperations): array
    {
        foreach ($aOperations as $sOperation) {
            $this->oCPU->replaceOutside($this->generateROM($sOperation));
            $fOperationTime = $this->runSamples($sOperation) - $this->fLoopTime;
            $fUnitOperation = $fOperationTime / (self::DEF_LOOPS * $this->iUnroll);
            $fOperationNops = $fUnitOperation/$this->fUnitNOP;
            $fMIPS = 1e-6 / $fUnitOperation;
            printf(
                "\tUnit %s %.3f ns [%.2f MIPS], %.3f NOP equivalent\n\n",
                $sOperation,
                $fUnitOperation * 1e9,
                $fMIPS,
                $fOperationNops
            );
            $this->aResults[$sOperation] = [$fUnitOperation, $fOperationNops, $fMIPS];
        }
        return $this->aResults;
    }

    private function calibrate()
    {
        printf(
            "Calibrating [ICache %s]\n" .
            "\tSample Runs:              %d\n" .
            "\tIterations per sample:    %d\n" .
            "\tOperations per Iteration: %d\n" .
            "\tSample rejection limit:   %.2f (%d required to pass)\n",
            $this->bWithICache ? 'Enabled' : 'Disabled',
            $this->iSamples,
            self::DEF_LOOPS,
            $this->iUnroll,
            $this->fFilterLimit,
            $this->iSamples * $this->fFilterLimit
        );

        $this->fLoopTime = $this->runSamples('<loop>');
        $fUnitDBF = $this->fLoopTime / self::DEF_LOOPS;
        $this->oCPU->replaceOutside($this->generateROM('nop'));
        $fNopTime = $this->runSamples('nop') - $this->fLoopTime;
        $this->fUnitNOP = $fNopTime / (self::DEF_LOOPS * $this->iUnroll);
        printf(
            "Calibration complete:\n\tUnit NOP %.3f ns [%.2f MIPS]\n\tUnit DBF %.3f ns [%.2f MIPS], %.3f NOP equivalent\n\n",
            $this->fUnitNOP * 1e9,
            1e-6 / $this->fUnitNOP,
            $fUnitDBF * 1e9,
            1e-6 / $fUnitDBF,
            $fUnitDBF / $this->fUnitNOP
        );
        $this->aResults['nop'] = [$this->fUnitNOP, 1.0, 1e-6 / $this->fUnitNOP];
        $this->aResults['dbf d0,.label'] = [$fUnitDBF, $fUnitDBF/$this->fUnitNOP, 1e-6 / $fUnitDBF];
    }

    private function runSamples(string $sWhat): float
    {
        $aSamples = [];
        $iFiltered = 0;
        do {
            echo "Running ", $sWhat, " samples";
            $aSamples = [];
            $i = $this->iSamples;
            while ($i--) {
                $this->oCPU
                    ->getDataRegisters()
                    ->aIndex[$this->iCountReg] = self::DEF_LOOPS - 1;
                $aSamples[] = $this->oCPU->benchmark(self::BASE_ADDRESS, $this->bWithICache);
                echo ".";
            }
            echo "\n";
            $oStats = $this->computeStandardDeviation($aSamples);
            echo "Removing outliers...";
            $aSamples = array_filter(
                $aSamples,
                function (float $fTime) use ($oStats) {
                    return abs($fTime - $oStats->fMean) < $oStats->fStdDeviation;
                }
            );
            $iFiltered = count($aSamples);
            echo $iFiltered, "\n";
        } while (($iFiltered / $this->iSamples) < $this->fFilterLimit);

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
