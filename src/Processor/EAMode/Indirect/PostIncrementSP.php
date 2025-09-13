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
 * Variant of PostIncrement that is used for the 68000 stack pointer. Byte sized
 * accesses always result in word sized adjustments.
 */
class PostIncrementSP extends PostIncrement
{
    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        $this->iAddress = $this->iRegister;
        $this->iRegister = ($this->iRegister + ISize::WORD) & ISize::MASK_LONG;
        return $this->oOutside->readByte($this->iAddress);
    }


    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $iAddress = $this->iAddress;
        if (null === $iAddress) {
            $iAddress = $this->iRegister;
            $this->iRegister = ($this->iRegister + ISize::WORD) & ISize::MASK_LONG;
        }
        $this->oOutside->writeByte($iAddress, $iValue);
        $this->iAddress = null;
    }

}
