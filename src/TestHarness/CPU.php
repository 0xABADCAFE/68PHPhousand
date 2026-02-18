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
use ABadCafe\G8PHPhousand\Processor\Fault;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

class CPU extends Processor\Base
{
    public function __construct(Device\IBusAccessible $oOutside)
    {
        parent::__construct($oOutside, false);
    }

    public function getName(): string
    {
        return 'TestHarness CPU';
    }

    public function getOutside(): Device\IBusAccessible
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

    public function asSupervisor(): self
    {
        $this->syncSupervisorState();
        return $this;
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
        catch (Processor\Fault\Access $oFault) {
            $this->processAccessError(
                $oFault,
                $this->iProgramCounter - Processor\ISize::WORD,
                $iOpcode
            );
        }
        catch (Processor\Fault\Address $oFault) {
            $this->processAddressError(
                $oFault,
                $this->iProgramCounter - Processor\ISize::WORD,
                $iOpcode
            );
        }
        catch (\DivisionByZeroError $oFault) {
            $this->processZeroDivideError();
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

    public function execute(?int $iAddress = null): int
    {
        if (null !== $iAddress) {
            $this->iProgramCounter = $iAddress;
        }

        $iCount = 0;
        try {
            while (true) {
                try {
                    while(true) {
                        $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                        $this->iProgramCounter += Processor\ISize::WORD;
                        $this->aExactHandler[$iOpcode]($iOpcode);
                        ++$iCount;
                    };
                }
                catch (Fault\Access $oFault) {
                    $this->processAccessError(
                        $oFault,
                        $this->iProgramCounter - Processor\ISize::WORD,
                        $iOpcode
                    );
                }
                catch (Fault\Address $oFault) {
                    $this->processAddressError(
                        $oFault,
                        $this->iProgramCounter - Processor\ISize::WORD,
                        $iOpcode
                    );
                }
                catch (\DivisionByZeroError $oFault) {
                    $this->processZeroDivideError();
                }
            }
        }
        catch (LogicException $oError) {
            echo "Emulation terminated\n";
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

            // We have to access memory data as bytes just in case we have an alignment adaptor in place.

            printf(
                "\td%d [0x%08X] %11d %6d %4d | a%d [0x%08X] | SP: %+3d [0x%08X] 0x%02X%02X | PC: %+3d [0x%08X] 0x%02X%02X %s %s\n",
                $i,
                $this->oDataRegisters->aIndex[$i],
                Processor\Sign::extLong($this->oDataRegisters->aIndex[$i]),
                Processor\Sign::extWord($this->oDataRegisters->aIndex[$i] & Processor\ISize::MASK_WORD),
                Processor\Sign::extByte($this->oDataRegisters->aIndex[$i] & Processor\ISize::MASK_BYTE),
                $i,
                $this->oAddressRegisters->aIndex[$i],
                $iStackOffset,
                $iStackAddress,
                $this->oOutside->readByte($iStackAddress),
                $this->oOutside->readByte($iStackAddress + 1),
                $iPCOffset,
                $iProgramCounter,
                $this->oOutside->readByte($iProgramCounter),
                $this->oOutside->readByte($iProgramCounter + 1),

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
        printf(
            "\tVBR: 0x%08X\n" .
            "\tUSP: 0x%08X *\n" .
            "\tSSP: 0x%08X *\n" .
            "\t* May not yet have synced with active SP\n",
            $this->iVectorBaseRegister,
            $this->iUserStackPtrRegister,
            $this->iSupervisorStackPtrRegister
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

    public function resetAndExecute(ObjectCode $oObjectCode, bool $bVerbose = false): int
    {
        Memory::loadObjectCode($this->oOutside, $oObjectCode);
        $this->softReset();
        return $bVerbose ?
            $this->executeCodeVerbose($oObjectCode) :
            $this->execute($this->iProgramCounter);
    }

    public function executeVerbose(ObjectCode $oObjectCode): int
    {
        Memory::loadObjectCode($this->oOutside, $oObjectCode);
        $this->iProgramCounter = $oObjectCode->iBaseAddress;
        return $this->executeCodeVerbose($oObjectCode);
    }

    protected function executeCodeVerbose(ObjectCode $oObjectCode): int
    {
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

                $sSourceLine = $oObjectCode->aSourceMap[$this->iProgramCounter]->sLineSrc ?? '---';

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
