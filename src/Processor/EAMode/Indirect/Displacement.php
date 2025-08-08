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
use ABadCafe\G8PHPhousand\Processor\EAMode\Direct;
use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Processor\ISize;

use ValueError;

/**
 * Address register indirect with 16-bit signed displacement
 */
class Displacement extends Basic
{
    use EAMode\TWithBusAccess;
    use EAMode\TWithExtensionWords;

    public function __construct(int& $iProgramCounter, Processor\RegisterSet $oRegisters, Device\IBus $oOutside)
    {
        parent::__construct($oRegisters, $oOutside);
        $this->bindProgramCounter($iProgramCounter);
    }

    private function getAddress(): int
    {
        $iDisplacement = Processor\Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
        $this->iProgramCounter += ISize::WORD;
        return ($iDisplacement + $this->iRegister) & ISize::MASK_LONG;
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
        $this->oOutside->writeByte($this->getAddress(), $iValue);
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->oOutside->writeWord($this->getAddress(), $iValue);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->oOutside->writeLong($this->getAddress(), $iValue);
    }
}
