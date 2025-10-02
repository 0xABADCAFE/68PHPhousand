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

namespace ABadCafe\G8PHPhousand\Processor\EAMode\Indirect;
use ABadCafe\G8PHPhousand\Processor\EAMode;
use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\ISize;

use ValueError;

/**
 * Address register, indexed with displacement
 */
class PCIndexed implements EAMode\IIndirect
{
    use EAMode\TWithBusAccess;
    use EAMode\TWithExtensionWords;
    use EAMode\TWithLatch;

    protected array $aIndexRegisters = [];

    public function __construct(
        int& $iProgramCounter,
        Processor\AddressRegisterSet $oAddressRegisters,
        Processor\DataRegisterSet    $oDataRegisters,
        Device\IBus $oOutside
    ) {
        $this->bindBus($oOutside);
        $this->bindProgramCounter($iProgramCounter);
        $this->bindIndexRegisters($oAddressRegisters, $oDataRegisters);
    }

    public function getAddress(): int
    {
        $iBaseAddress = $this->iProgramCounter;
        $iExtension = $this->oOutside->readWord($iBaseAddress);
        $this->iProgramCounter += ISize::WORD;

        // Get the value in the index register
        $iIndex = ($this->aIndexRegisters[$iExtension & IOpcode::BXW_IDX_REG]);

        // If the size bit is clear, the index is a signed word, otherwise signed long.
        // There is no scale applie (020+ only)
        if (!($iExtension & IOpcode::BXW_IDX_SIZE)) {
            $iIndex = Processor\Sign::extWord($iIndex);
        }

        // Get the fixed 8-bit displacement
        $iDisplacement = Processor\Sign::extByte($iExtension & IOpcode::BXW_DISP_MASK);

        return $this->iAddress = ($iDisplacement + $iBaseAddress + $iIndex) & ISize::MASK_LONG;
    }

    private function bindIndexRegisters(
        Processor\RegisterSet $oAddressRegisters,
        Processor\RegisterSet $oDataRegisters
    ) {
        $this->aIndexRegisters = [
            IOpcode::BXW_REG_D0 => &$oDataRegisters->iReg0,
            IOpcode::BXW_REG_D1 => &$oDataRegisters->iReg1,
            IOpcode::BXW_REG_D2 => &$oDataRegisters->iReg2,
            IOpcode::BXW_REG_D3 => &$oDataRegisters->iReg3,
            IOpcode::BXW_REG_D4 => &$oDataRegisters->iReg4,
            IOpcode::BXW_REG_D5 => &$oDataRegisters->iReg5,
            IOpcode::BXW_REG_D6 => &$oDataRegisters->iReg6,
            IOpcode::BXW_REG_D7 => &$oDataRegisters->iReg7,

            IOpcode::BXW_REG_A0 => &$oAddressRegisters->iReg0,
            IOpcode::BXW_REG_A1 => &$oAddressRegisters->iReg1,
            IOpcode::BXW_REG_A2 => &$oAddressRegisters->iReg2,
            IOpcode::BXW_REG_A3 => &$oAddressRegisters->iReg3,
            IOpcode::BXW_REG_A4 => &$oAddressRegisters->iReg4,
            IOpcode::BXW_REG_A5 => &$oAddressRegisters->iReg5,
            IOpcode::BXW_REG_A6 => &$oAddressRegisters->iReg6,
            IOpcode::BXW_REG_A7 => &$oAddressRegisters->iReg7,
        ];
    }

    public function readByte(): int
    {
        return $this->oOutside->readByte($this->getAddress());
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        return $this->oOutside->readWord($this->getAddress());
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        return $this->oOutside->readLong($this->getAddress());
    }

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $this->oOutside->writeByte($this->iAddress ?? $this->getAddress(), $iValue);
        $this->iAddress = null;
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->oOutside->writeWord($this->iAddress ?? $this->getAddress(), $iValue);
        $this->iAddress = null;
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->oOutside->writeLong($this->iAddress ?? $this->getAddress(), $iValue);
        $this->iAddress = null;
    }
}
