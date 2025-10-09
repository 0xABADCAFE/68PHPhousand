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
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\Sign;

use \LogicException;
use \stdClass;

class TomHarte
{
    private string $sTestDir;
    private string $sSuite;

    private Device\IMemory $oMemory;
    private CPU $oCPU;

    public function __construct(string $sTestDir)
    {
        assert(is_readable($sTestDir) & is_dir($sTestDir), new LogicException());
        $this->sTestDir = $sTestDir;
        $this->oMemory = new Device\Memory\SparseRAM24();
        $this->oCPU    = new CPU($this->oMemory);
    }

    /** @var array<int, stdClass> */
    private array $aTestCases = [];

    public function runAllExcept(array $aExclude)
    {
        $aExclude = array_flip($aExclude);
        $aTests   = glob($this->sTestDir. '/*.json.gz');
        $aResults = [];
        foreach ($aTests as $sTest) {
            preg_match(
                '/([a-zA-Z0-9]+\.{0,1}[bwlq]{0,1})\.json\.gz/',
                $sTest,
                $aMatches
            );
            if (isset($aMatches[1]) && !isset($aExclude[$aMatches[1]])) {
                $aResults[] = $this
                    ->loadSuite($aMatches[1])
                    ->run();
            }
        }
        usort(
            $aResults,
            function (stdClass $oA, stdClass $oB): int {
                return (int)(1e6*($oB->fPassRate - $oA->fPassRate));
            }
        );
        foreach ($aResults as $oResult) {
            printf(
                "Result: %-10s Completed in %0.3f s - %4d total, %4d attempted, %4d skipped, %4d passed, %4d failed, %4d errored. Pass rate: %6.2f%%\n",
                $oResult->sSuite,
                $oResult->fTime,
                $oResult->iTests,
                $oResult->iAttempted,
                $oResult->iSkipped,
                $oResult->iPassed,
                $oResult->iFailed,
                $oResult->iErrored,
                $oResult->fPassRate
            );
        }
    }

    public function loadSuite(string $sSuite): self
    {
        $this->aTestCases = [];
        $sTestPath = sprintf(
            '%s/%s.json.gz',
            $this->sTestDir,
            $sSuite
        );
        $aTestCases = json_decode(
            gzdecode(file_get_contents($sTestPath))
        );
        assert(
            !empty($aTestCases) && is_array($aTestCases),
            new LogicException('Invalid Test Suite ' . $sSuite)
        );

        printf(
            "Loaded %s, containing %d test cases\n",
            $sTestPath,
            count($aTestCases)
        );
        $this->aTestCases = $aTestCases;
        $this->sSuite     = $sSuite;
        return $this;
    }

    public function run(): stdClass
    {
        $iErrored   = 0;
        $iSkipped   = 0;
        $iAttempted = 0;
        $iPassed    = 0;
        $iFailed    = 0;

        $fTime      = -microtime(true);
        foreach($this->aTestCases as $oTestCase) {

            if ($this->changesSupervisorState($oTestCase)){
                printf(
                    "Skipping %s - triggers supervisor state change\n",
                    $oTestCase->name
                );
                ++$iSkipped;
                continue;
            }
            if ($this->usesVectorTable($oTestCase)) {
                printf(
                    "Skipping %s - requires exception vector\n",
                    $oTestCase->name
                );
                ++$iSkipped;
                continue;
            }

            printf("\nTesting %s\n", $oTestCase->name);
            ob_start();
            try {
                $this->prepareTest($oTestCase);
                print("Initial State\n");
                $this->oCPU->dumpMachineState(null);
                $this->oCPU->executeAt($oTestCase->initial->pc);
                print("Final State\n");
                $this->oCPU->dumpMachineState(null);
                ++$iAttempted;

                $aFailures = $this->checkTestResult($oTestCase);
                if (empty($aFailures)) {
                    ++$iPassed;
                    ob_end_clean();
                    print("PASSED\n");
                } else {
                    ++$iFailed;
                    printf("FAILED: %s\n", $oTestCase->name);
                    foreach ($aFailures as $sMessage) {
                        printf("\t%s\n", $sMessage);
                    }
                    //print(json_encode($oTestCase, JSON_PRETTY_PRINT));
                    ob_end_flush();
                }
            } catch (\Throwable $oError) {
                printf("ERRORED:\n\t%s\n%s\n", $oError->getMessage(), $oError->getTraceAsString());
                ob_end_flush();
                ++$iAttempted;
                ++$iErrored;
            }
        }
        $fTime += microtime(true);

        return (object)[
            'sSuite'     => $this->sSuite,
            'fTime'      => $fTime,
            'iTests'     => count($this->aTestCases),
            'iAttempted' => $iAttempted,
            'iSkipped'   => $iSkipped,
            'iPassed'    => $iPassed,
            'iFailed'    => $iFailed,
            'iErrored'   => $iErrored,
            'fPassRate'  => $iAttempted ? (100.0 * $iPassed/$iAttempted) : 0.0
        ];
    }

    public function changesSupervisorState(stdClass $oTestCase): bool
    {
        $iSRState = ($oTestCase->initial->sr ^ $oTestCase->final->sr) >> 8;
        return (bool)(0 !== ($iSRState & IRegister::SR_MASK_SUPER));
    }

    /**
     * TODO implement a VBR, even if it's hardcoded to 0 for now
     */
    public function usesVectorTable(stdClass $oTestCase): bool
    {
        foreach ($oTestCase->initial->ram as $aPair) {
            if ($aPair[0] < 0x400) {
                return true;
            }
        }
        return false;
    }


    public function prepareTest(stdClass $oTestCase)
    {
        $this->oMemory->hardReset();
        $this->oCPU->hardReset();

        // Fill in the memory
        $iAddress = $oTestCase->initial->pc;
        foreach ($oTestCase->initial->prefetch as $iOpcode) {
            $this->oMemory->writeWord($iAddress, $iOpcode);
            $iAddress += ISize::WORD;
        }
        foreach ($oTestCase->initial->ram as $aPair) {
            printf(
                "\tSetting byte at 0x%08X to 0x%02X (%d)\n",
                $aPair[0],
                $aPair[1],
                Sign::extByte($aPair[1])
            );
            $this->oMemory->writeByte($aPair[0], $aPair[1]);
        }

        $iCCR   =  $oTestCase->initial->sr & 0xFF;
        $iSR    =  $oTestCase->initial->sr >> 8;

        // Choose the stack pointer depending on the state
        $iStack = ($iSR & IRegister::SR_MASK_SUPER) ?
            $oTestCase->initial->ssp :
            $oTestCase->initial->usp;

        // Set the registers
        $this->oCPU->setPC($oTestCase->initial->pc);
        $this->oCPU->setRegister('sr',  $iSR);
        $this->oCPU->setRegister('ccr', $iCCR);
        $this->oCPU->setRegister('d0',  $oTestCase->initial->d0);
        $this->oCPU->setRegister('d1',  $oTestCase->initial->d1);
        $this->oCPU->setRegister('d2',  $oTestCase->initial->d2);
        $this->oCPU->setRegister('d3',  $oTestCase->initial->d3);
        $this->oCPU->setRegister('d4',  $oTestCase->initial->d4);
        $this->oCPU->setRegister('d5',  $oTestCase->initial->d5);
        $this->oCPU->setRegister('d6',  $oTestCase->initial->d6);
        $this->oCPU->setRegister('d7',  $oTestCase->initial->d7);
        $this->oCPU->setRegister('a0',  $oTestCase->initial->a0);
        $this->oCPU->setRegister('a1',  $oTestCase->initial->a1);
        $this->oCPU->setRegister('a2',  $oTestCase->initial->a2);
        $this->oCPU->setRegister('a3',  $oTestCase->initial->a3);
        $this->oCPU->setRegister('a4',  $oTestCase->initial->a4);
        $this->oCPU->setRegister('a5',  $oTestCase->initial->a5);
        $this->oCPU->setRegister('a6',  $oTestCase->initial->a6);
        $this->oCPU->setRegister('a7',  $iStack);
    }

    public function checkTestResult(stdClass $oTestCase): array
    {
        $aFailures = [];
        $iExpect = 0;
        $iHave   = 0;

        if (
            ($iExpect = $oTestCase->final->pc) !=
            ($iHave = $this->oCPU->getPC())
        ) {
            $aFailures[] = sprintf(
                'PC mismatch: Expected 0x%08X, got 0x%08X for test case %s',
                $iExpect,
                $iHave,
                $oTestCase->name
            );
        }

        $iCCR   =  $oTestCase->final->sr & 0xFF;
        $iSR    =  $oTestCase->final->sr >> 8;

        // Choose the stack pointer depending on the state
        $iStack = ($iSR & IRegister::SR_MASK_SUPER) ?
            $oTestCase->final->ssp :
            $oTestCase->final->usp;

        if (
            ($iExpect = $iCCR) !=
            ($iHave = $this->oCPU->getRegister('ccr'))
        ) {
            $aFailures[] = sprintf(
                'CCR mismatch: Expected 0x%02X (%s), got 0x%02X (%s) for test case %s',
                $iExpect,
                $this->oCPU->formatCCR($iExpect),
                $iHave,
                $this->oCPU->formatCCR($iHave),
                $oTestCase->name
            );
        }

        for ($iReg = 0; $iReg < 8; ++$iReg) {
            $sRegName = 'd' . $iReg;
            if (
                ($iExpect = $oTestCase->final->{$sRegName}) !=
                ($iHave = $this->oCPU->getRegister($sRegName))
            ) {
                $aFailures[] = sprintf(
                    '%s mismatch: Expected 0x%08X, got 0x%08X for test case %s',
                    $sRegName,
                    $iExpect,
                    $iHave,
                    $oTestCase->name
                );
            }
        }

        for ($iReg = 0; $iReg < 7; ++$iReg) {
            $aRegName = 'a' . $iReg;
            if (
                ($iExpect = $oTestCase->final->{$sRegName}) !=
                ($iHave = $this->oCPU->getRegister($sRegName))
            ) {
                $aFailures[] = sprintf(
                    '%s mismatch: Expected 0x%08X, got 0x%08X for test case %s',
                    $sRegName,
                    $iExpect,
                    $iHave,
                    $oTestCase->name
                );
            }
        }



        if (
            ($iExpect = $iStack) !=
            ($iHave = $this->oCPU->getRegister('a7'))
        ) {
            $aFailures[] = sprintf(
                'SP mismatch: Expected 0x%08X, got 0x%08X for test case %s',
                $iExpect,
                $iHave,
                $oTestCase->name
            );
        }

        // Memory
        foreach ($oTestCase->final->ram as $aPair) {
            if (
                ($iExpect = $aPair[1]) !=
                ($iHave = $this->oMemory->readByte($aPair[0]))
            ) {
                $aFailures[] = sprintf(
                    'RAM mismatch: Expected 0x%02X (%d) at 0x%08X, got 0x%02X (%d) for test case %s',
                    $iExpect,
                    Sign::extByte($iExpect),
                    $aPair[0],
                    $iHave,
                    Sign::extByte($iHave),
                    $oTestCase->name
                );

            }
        }
        return $aFailures;
    }
}
