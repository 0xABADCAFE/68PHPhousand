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

$oProcessor = new class extends Processor\Base
{
    public function __construct()
    {
        parent::__construct(new Device\Memory(64, 0));
    }

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

echo "Testing selected exact match Opcodes\n";


echo "\tORI to CCR: ";
// Test ORI_CCR
$oProcessor->getMemory()->writeWord(0x4, Processor\Opcode\IPrefix::OP_ORI_CCR);
$oProcessor->getMemory()->writeWord(0x6, Processor\IRegister::CCR_MASK_C);
$oProcessor->setRegister('ccr', Processor\IRegister::CCR_MASK_N);
$oProcessor->executeAt(0x4);

assert(0x8 === $oProcessor->getPC(), new AssertionFailureException('Incorrect PC after executeAt()'));
assert(
    Processor\IRegister::CCR_MASK_C|Processor\IRegister::CCR_MASK_N === $oProcessor->getRegister('ccr'),
    new AssertionFailureException()
);

echo "OK\n";

echo "\tEORI to CCR: ";

$oProcessor->getMemory()->writeWord(0x4, Processor\Opcode\IPrefix::OP_EORI_CCR);
$oProcessor->getMemory()->writeWord(0x6, Processor\IRegister::CCR_MASK_N);

$oProcessor->executeAt(0x4);

assert(0x8 === $oProcessor->getPC(), new AssertionFailureException('Incorrect PC after executeAt()'));
assert(
    Processor\IRegister::CCR_MASK_C === $oProcessor->getRegister('ccr'),
    new AssertionFailureException()
);

echo "OK\n";

echo "Testing selected prefix match Opcodes\n";

echo "\tORI.w to d0: ";
// Test ORI_CCR
$oProcessor->getMemory()->writeWord(0x4, Processor\Opcode\IPrefix::OP_ORI_W);
$oProcessor->getMemory()->writeWord(0x6, 0b1010101001010101);
$oProcessor->setRegister('d0',           0b0101010110101010);
$oProcessor->executeAt(0x4);

assert(0x8 === $oProcessor->getPC(), new AssertionFailureException('Incorrect PC after executeAt()'));
assert(
    0xFFFF === $oProcessor->getRegister('d0'),
    new AssertionFailureException()
);

echo "OK\n";

echo "\tANDI.b to d0: ";
// Test ORI_CCR
$oProcessor->getMemory()->writeWord(0x4, Processor\Opcode\IPrefix::OP_ANDI_B);
$oProcessor->getMemory()->writeWord(0x6, 0);
$oProcessor->executeAt(0x4);

assert(0x8 === $oProcessor->getPC(), new AssertionFailureException('Incorrect PC after executeAt()'));
assert(
    0xFF00 === $oProcessor->getRegister('d0'),
    new AssertionFailureException()
);

echo "OK\n";

echo "\tEORI.l to d0: ";
// Test ORI_CCR
$oProcessor->getMemory()->writeWord(0x4, Processor\Opcode\IPrefix::OP_EORI_L);
$oProcessor->getMemory()->writeLong(0x6, 0xFFFFFFFF);
$oProcessor->executeAt(0x4);

assert(0xA === $oProcessor->getPC(), new AssertionFailureException('Incorrect PC after executeAt()'));
assert(
    0xFFFF00FF === $oProcessor->getRegister('d0'),
    new AssertionFailureException()
);

echo "OK\n";
