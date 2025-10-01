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

    public function executeVerbose(int $iAddress): int
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        echo "Beginning Verbose Execution\n";
        try {
            while(true) {
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                printf("0x%08X : %04X\n", $this->iProgramCounter, $iOpcode);
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        return $iCount;
    }

}
