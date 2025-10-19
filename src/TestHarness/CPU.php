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
            throw new LogicException(
                sprintf(
                    'Unhandled Opcode 0x%04X [%s]',
                    $iOpcode,
                    base_convert((string)$iOpcode, 10, 2)
                )
            );

        try {
            $cHandler($iOpcode);
        }
        catch (Processor\Fault\MisalignedRead $oReadFault) {
            // TODO
            // PROTOTYPE - export all the logic to an appropriate helper
            $this->syncSupervisorState(); // Transition to supervisor mode

            // Allocate Exception Frame (14 bytes)
            $this->oAddressRegisters->iReg7 -= 14;

            // TODO - populate it

            // Reload the PC from vector 0xC (AddressError), include VBR
            $this->iProgramCounter = $this->oOutside->readLong(
                $this->iVectorBaseRegister + 0xC
            );

        } catch (Processor\Fault\MisalignedWrite $oWriteFault) {
            // TODO
            //throw new LogicException('Intercepted misaligned write to ' . $oWriteFault->iAddress);
        }
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

    public function dumpMachineState(?ObjectCode $oObjectCode)
    {
        $iStackOffset = 10;
        $iPCOffset    = -4;
        $iStackAddress = ($this->oAddressRegisters->iReg7 + $iStackOffset) & Processor\ISize::MASK_LONG;

        $iProgramCounter = ($this->iProgramCounter + $iPCOffset) & Processor\ISize::MASK_LONG;

        printf(
            "\tData Regs                .l     .w   .b | Address Regs    | Stack Contents              | Program Contents\n"
        );

        for ($i = 7; $i >=0 ; --$i) {
            $oSourceInfo = $oObjectCode->aSourceMap[$iProgramCounter] ?? null;

            printf(
                "\td%d [0x%08X] %11d %6d %4d | a%d [0x%08X] | SP: %+3d [0x%08X] 0x%04X | PC: %+3d [0x%08X] 0x%04X %s %s\n",
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
                $this->oOutside->readWord($iProgramCounter),
                $iPCOffset ? '   ' : '>>>',
                $oSourceInfo ? $oSourceInfo->sLineSrc : ""
            );
            $iStackOffset    -= Processor\ISize::WORD;
            $iStackAddress   -= Processor\ISize::WORD;
            $iStackAddress   &= Processor\ISize::MASK_LONG;
            $iPCOffset       += Processor\ISize::WORD;
            $iProgramCounter += Processor\ISize::WORD;
            $iProgramCounter &= Processor\ISize::MASK_LONG;
        }
        printf(
            "\tCCR: %s\n",
            $this->formatCCR($this->iConditionRegister)
        );
    }

    public function formatCCR(int $iCC): string
    {
        return sprintf(
            "%s%s%s%s%s",
            $iCC & Processor\IRegister::CCR_EXTEND   ? 'X' : '-',
            $iCC & Processor\IRegister::CCR_NEGATIVE ? 'N' : '-',
            $iCC & Processor\IRegister::CCR_ZERO     ? 'Z' : '-',
            $iCC & Processor\IRegister::CCR_OVERFLOW ? 'V' : '-',
            $iCC & Processor\IRegister::CCR_CARRY    ? 'C' : '-'
        );

    }

    public function executeVerbose(ObjectCode $oObjectCode): int
    {
        Memory::loadObjectCode($this->oOutside, $oObjectCode);

        $this->iProgramCounter = $oObjectCode->iBaseAddress;
        $sSourceLine = $oObjectCode->aSourceMap[$this->iProgramCounter]->sLineSrc;
        $iCount = 0;
        printf(
            "\nBeginning Verbose Execution from 0x%08X : %s\n\n",
            $this->iProgramCounter,
            $sSourceLine
        );
        try {
            while(true) {
                $this->dumpMachineState($oObjectCode);
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);

                if (!isset($this->aExactHandler[$iOpcode])) {
                    throw new \RuntimeException('No handler');
                }

                $sSourceLine = $oObjectCode->aSourceMap[$this->iProgramCounter]->sLineSrc;

                printf("\nExecuted 0x%08X : %s\n\n", $this->iProgramCounter, $sSourceLine);

                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        } finally {
            printf("Execution halted at 0x%08X. Final state:\n", $this->iProgramCounter);
            $this->dumpMachineState($oObjectCode);
        }
        return $iCount;
    }

}
