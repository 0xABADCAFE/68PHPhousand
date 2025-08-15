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

//     public function readLongA0PostIncrement(): int
//     {
//         return $this->readLongIndPostInc($this->iRegA0);
//     }
//
//     public function readWordA0PreDecrement(): int
//     {
//         return $this->readWordIndPreDec($this->iRegA0);
//     }
};

// Test the enumerated data register masks (lower) map to the specific registers
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D0] = 0x10000000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D1] = 0x02000000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D2] = 0x00300000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D3] = 0x00040000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D4] = 0x00005000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D5] = 0x00000600;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D6] = 0x00000070;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_EA_D7] = 0x00000008;

// Test the enumerated data register masks (upper) map to the specific registers
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D0] += 0x10000000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D1] += 0x01000000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D2] += 0x00100000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D3] += 0x00010000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D4] += 0x00001000;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D5] += 0x00000100;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D6] += 0x00000010;
$oProcessor->getDataRegs()[Processor\IOpcode::REG_UP_D7] += 0x00000001;


// Test the enumerated address register masks (lower) map to the specific registers
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A0] = 0x10000000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A1] = 0x02000000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A2] = 0x00300000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A3] = 0x00040000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A4] = 0x00005000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A5] = 0x00000600;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A6] = 0x00000070;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_EA_A7] = 0x00000008;

// Test the enumerated data register masks (upper) map to the specific registers
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A0] += 0x20000000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A1] += 0x02000000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A2] += 0x00200000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A3] += 0x00020000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A4] += 0x00002000;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A5] += 0x00000200;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A6] += 0x00000020;
$oProcessor->getAddrRegs()[Processor\IOpcode::REG_UP_A7] += 0x00000002;

// If there were any mistakes in the upper or lower mappings, these assertions will fail
assert(
    $oProcessor->getRegister('d0') |
    $oProcessor->getRegister('d1') |
    $oProcessor->getRegister('d2') |
    $oProcessor->getRegister('d3') |
    $oProcessor->getRegister('d4') |
    $oProcessor->getRegister('d5') |
    $oProcessor->getRegister('d6') |
    $oProcessor->getRegister('d7') === 0x23456789,
    new LogicException('Invalid Data Register Mapping')
);

// If there were any mistakes in the upper or lower mappings, these assertions will fail
assert(
    $oProcessor->getRegister('a0') |
    $oProcessor->getRegister('a1') |
    $oProcessor->getRegister('a2') |
    $oProcessor->getRegister('a3') |
    $oProcessor->getRegister('a4') |
    $oProcessor->getRegister('a5') |
    $oProcessor->getRegister('a6') |
    $oProcessor->getRegister('a7') === 0x3456789A,
    new LogicException('Invalid Address Register Mapping')
);

$oProcessor->getMemory()->writeLong(0, 0xABADCAFE);

$oProcessor->softReset();

// If there were any mistakes in the upper or lower mappings, these assertions will fail
assert(
    $oProcessor->getRegister('d0') |
    $oProcessor->getRegister('d1') |
    $oProcessor->getRegister('d2') |
    $oProcessor->getRegister('d3') |
    $oProcessor->getRegister('d4') |
    $oProcessor->getRegister('d5') |
    $oProcessor->getRegister('d6') |
    $oProcessor->getRegister('d7') |
    $oProcessor->getRegister('a0') |
    $oProcessor->getRegister('a1') |
    $oProcessor->getRegister('a2') |
    $oProcessor->getRegister('a3') |
    $oProcessor->getRegister('a4') |
    $oProcessor->getRegister('a5') |
    $oProcessor->getRegister('a6') |
    $oProcessor->getRegister('a7') === 0 &&
    $oProcessor->getMemory()->readLong(0) === 0xABADCAFE,
    new LogicException('Invalid Soft Reset')
);

echo "Register tests passed\n";
