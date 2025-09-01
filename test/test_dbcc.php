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

$oMemory = new Device\Memory(64, 0);

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

    public function executeAt(int $iAddress): float
    {
        assert($iAddress >= 0, new LogicException('Invalid start address'));
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        $tStart = microtime(true);
        try {
            while(true) {
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += Processor\ISize::WORD;
                $cHandler = $this->aExactHandler[$iOpcode];
                $cHandler($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        $fTime = microtime(true) - $tStart;

        printf(
            "Executed %d dbf d0,-2 instructions in %.6f seconds: %.3f IPS\n",
            $iCount,
            $fTime,
            $iCount / $fTime
        );

        return $iCount / $fTime;
    }
};

$oMemory->writeWord(0x4, Processor\Opcode\IConditional::OP_DBF);
$oMemory->writeWord(0x6, -2);
$oMemory->writeWord(0x8, Processor\Opcode\IPrefix::OP_STOP);

$fTotal = 0;
for ($i = 0; $i < 100; ++$i) {
    printf("Run %3d: ", $i + 1);
    $oProcessor->getDataRegs()->iReg0 = 65535;
    $fTotal += $oProcessor->executeAt(0x4);
}

printf("Average over 100 runs: %.3f IPS\n", 0.01 * $fTotal);

$aOpcacheStatus = opcache_get_status();
if (isset($aOpcacheStatus['jit'])) {
    echo "JIT parameters: ";
    print_r($aOpcacheStatus['jit']);
} else {
    echo "JIT mode disabled\n";
}
