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
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\Processor;

use ValueError;

/**
 * Address Register indirect, post increment. Increment happens on read/write.
 */
class PostIncrement extends Basic
{
    use EAMode\TWithLatch;

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        $this->iAddress = $this->iRegister;
        $this->iRegister = ($this->iRegister + ISize::BYTE) & ISize::MASK_LONG;
        return $this->oOutside->readByte($this->iAddress);
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        $this->iAddress = $this->iRegister;
        $this->iRegister = ($this->iRegister + ISize::WORD) & ISize::MASK_LONG;
        return $this->oOutside->readWord($this->iAddress);
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        $this->iAddress = $this->iRegister;
        $this->iRegister = ($this->iRegister + ISize::LONG) & ISize::MASK_LONG;
        return $this->oOutside->readLong($this->iAddress);
    }

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $iAddress = $this->iAddress;
        if (null === $iAddress) {
            $iAddress = $this->iRegister;
            $this->iRegister = ($this->iRegister + ISize::BYTE) & ISize::MASK_LONG;
        }
        $this->oOutside->writeByte($iAddress, $iValue);
        $this->iAddress = null;
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $iAddress = $this->iAddress;
        if (null === $iAddress) {
            $iAddress = $this->iRegister;
            $this->iRegister = ($this->iRegister + ISize::WORD) & ISize::MASK_LONG;
        }
        $this->oOutside->writeWord($iAddress, $iValue);
        $this->iAddress = null;
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $iAddress = $this->iAddress;
        if (null === $iAddress) {
            $iAddress = $this->iRegister;
            $this->iRegister = ($this->iRegister + ISize::LONG) & ISize::MASK_LONG;
        }
        $this->oOutside->writeLong($iAddress, $iValue);
        $this->iAddress = null;
    }
}
