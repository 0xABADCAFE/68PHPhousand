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

use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

require 'bootstrap.php';

$oMemory = new Device\Memory(64, 0);

$oProcessor = new class($oMemory) extends Processor\Base
{
    public function getName(): string
    {
        return 'Test CPU';
    }

    public function getMemory(): Device\Memory
    {
        return $this->oOutside;
    }

    /** Expose the indexed data regs for testing */
    public function getDataRegs(): array
    {
        return $this->oDataRegisters->aIndex;
    }

    /** Expose the indexed addr regs for testing */
    public function getAddrRegs(): array
    {
        return $this->oAddressRegisters->aIndex;
    }

    public function executeAt(int $iAddress): void
    {
        assert($iAddress >= 0, new LogicException('Invalid start address'));
        $this->iProgramCounter = $iAddress;
        $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += Processor\ISize::WORD;

        $cHandler = $this->aExactHandler[$iOpcode] ??
            $this->aPrefixHandler[$iOpcode & Processor\IOpcode::MASK_OP_PREFIX] ??
            throw new LogicException('Unhandled Opcode ' . $iOpcode);

        $cHandler($iOpcode);
    }
};

///////////////////////////////////////////////////////////////////////////////////////////////////

echo "\nTesting selected exact match Opcodes\n";

echo "\tORI to CCR: ";
// Test ORI_CCR
$oMemory->writeWord(0x4, Processor\Opcode\IPrefix::OP_ORI_CCR);
$oMemory->writeWord(0x6, Processor\IRegister::CCR_CARRY);
$oProcessor->setRegister('ccr', Processor\IRegister::CCR_NEGATIVE);
$oProcessor->executeAt(0x4);

assertSame(
    0x8,
    $oProcessor->getPC(),
    'PC after executeAt()'
);

assertSame(
    Processor\IRegister::CCR_CARRY|Processor\IRegister::CCR_NEGATIVE,
    $oProcessor->getRegister('ccr'),
    'ccr after ORI to CCR'
);

echo "OK\n";

echo "\tEORI to CCR: ";

$oMemory->writeWord(0x4, Processor\Opcode\IPrefix::OP_EORI_CCR);
$oMemory->writeWord(0x6, Processor\IRegister::CCR_NEGATIVE);
$oProcessor->executeAt(0x4);

assertSame(
    0x8,
    $oProcessor->getPC(),
    'PC after executeAt()'
);
assertSame(
    Processor\IRegister::CCR_CARRY,
    $oProcessor->getRegister('ccr'),
    'CCR after EORI to CCR'
);

echo "OK\n";

///////////////////////////////////////////////////////////////////////////////////////////////////

echo "\nTesting selected prefix match Opcodes\n";

echo "\tORI.w to d0: ";
$oMemory->writeWord(0x4, Processor\Opcode\IPrefix::OP_ORI_W);
$oMemory->writeWord(0x6,       0b1010101001010101);
$oProcessor->setRegister('d0', 0b0101010110101010);
$oProcessor->executeAt(0x4);

assertSame(
    0x8,
    $oProcessor->getPC(),
    'PC after executeAt()'
);
assertSame(
    0xFFFF,
    $oProcessor->getRegister('d0'),
    'd0 after ORI.w'
);

echo "OK\n";

echo "\tANDI.b to d0: ";
$oMemory->writeWord(0x4, Processor\Opcode\IPrefix::OP_ANDI_B);
$oMemory->writeWord(0x6, 0);
$oProcessor->executeAt(0x4);

assertSame(
    0x8,
    $oProcessor->getPC(),
    'PC after executeAt()'
);
assertSame(
    0xFF00,
    $oProcessor->getRegister('d0'),
    'd0 after ANDI.b'
);

echo "OK\n";

echo "\tEORI.l to d0: ";
$oMemory->writeWord(0x4, Processor\Opcode\IPrefix::OP_EORI_L);
$oMemory->writeLong(0x6, 0xFFFFFFFF);
$oProcessor->executeAt(0x4);

assertSame(
    0xA,
    $oProcessor->getPC(),
    'PC after executeAt()'
);
assertSame(
    0xFFFF00FF,
    $oProcessor->getRegister('d0'),
    'd0 after EORI.l'
);

echo "OK\n";

///////////////////////////////////////////////////////////////////////////////////////////////////


echo "OK\n";
