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

use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

class CPU extends Processor\Base
{
    public function __construct(Device\IBus $oOutside)
    {
        parent::__construct($oOutside, false);
    }

    public function getName(): string
    {
        return 'TestHarness CPU';
    }

    public function getOutside(): Device\IBus
    {
        return $this->oOutside;
    }

    public function getDataRegisters(): Processor\DataRegisterSet
    {
        return $this->oDataRegisters;
    }

    public function getAddressRegisters(): Processor\AddressRegisterSet
    {
        return $this->oAddressRegisters;
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

    public function executeTimed(int $iAddress): \stdClass
    {
        $fTime = -microtime(true);
        $iCount = $this->execute($iAddress);
        $fTime += microtime(true);
        return (object)[
            'iCount' => $iCount,
            'fTime'  => $fTime
        ];
    }

    public function execute(int $iAddress): int
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;

        try {
            while(true) {
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        return $iCount;
    }

    public function dumpMachineState()
    {
        $iStackOffset = 10;
        $iPCOffset = 10;
        $iStackAddress = ($this->oAddressRegisters->iReg7 + $iStackOffset) & Processor\ISize::MASK_LONG;

        $iProgramCounter = ($this->iProgramCounter + $iPCOffset) & Processor\ISize::MASK_LONG;

        printf(
            //"\td7 [0x00000000]          0      0    0 | a7 [0x0000FFFC] | SP: +10 [0x00010006] 0x0000 | PC: +10 [0x0000001C] 0x0000" .
            "\tData Regs                .l     .w   .b | Address Regs    | Stack Contents              | Program Contents\n"
        );

        for ($i = 7; $i >=0 ; --$i) {
            printf(
                "\td%d [0x%08X] %11d %6d %4d | a%d [0x%08X] | SP: %+3d [0x%08X] 0x%04X | PC: %+3d [0x%08X] 0x%04X\n",
                $i,
                $this->oDataRegisters->aIndex[$i],
                Processor\Sign::extLong($this->oDataRegisters->aIndex[$i]),
                Processor\Sign::extWord($this->oDataRegisters->aIndex[$i] & Processor\ISize::MASK_WORD),
                Processor\Sign::extByte($this->oDataRegisters->aIndex[$i] & Processor\ISize::MASK_BYTE),
                $i,
                $this->oAddressRegisters->aIndex[$i],
                $iStackOffset,
                $iStackAddress,
                $this->oOutside->readWord($iStackAddress),
                $iPCOffset,
                $iProgramCounter,
                $this->oOutside->readWord($iProgramCounter)
            );
            $iStackOffset    -= Processor\ISize::WORD;
            $iStackAddress   -= Processor\ISize::WORD;
            $iStackAddress   &= Processor\ISize::MASK_LONG;
            $iPCOffset       -= Processor\ISize::WORD;
            $iProgramCounter -= Processor\ISize::WORD;
            $iProgramCounter &= Processor\ISize::MASK_LONG;
        }
        printf(
            "\tCCR: %s%s%s%s%s\n",
            $this->iConditionRegister & Processor\IRegister::CCR_EXTEND   ? 'X' : '-',
            $this->iConditionRegister & Processor\IRegister::CCR_NEGATIVE ? 'N' : '-',
            $this->iConditionRegister & Processor\IRegister::CCR_ZERO     ? 'Z' : '-',
            $this->iConditionRegister & Processor\IRegister::CCR_OVERFLOW ? 'V' : '-',
            $this->iConditionRegister & Processor\IRegister::CCR_CARRY    ? 'C' : '-'
        );
    }

    public function executeVerbose(int $iAddress): int
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        echo "Beginning Verbose Execution\n";
        try {
            while(true) {
                $this->dumpMachineState();
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);

                printf("\nExecuting 0x%08X : 0x%04X\n", $this->iProgramCounter, $iOpcode);

                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        } finally {
            $this->dumpMachineState();
        }
        return $iCount;
    }

}
