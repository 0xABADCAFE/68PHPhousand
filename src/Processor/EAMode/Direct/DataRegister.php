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

namespace ABadCafe\G8PHPhousand\Processor\EAMode\Direct;
use ABadCafe\G8PHPhousand\Processor\EAMode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use LogicException;

/**
 * Data Register Direct EA
 */
class DataRegister extends Register
{
    use EAMode\TWithoutLatch;

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $this->iRegister &= ISize::MASK_INV_BYTE;
        $this->iRegister |= ($iValue & ISize::MASK_BYTE);
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->iRegister &= ISize::MASK_INV_WORD;
        $this->iRegister |= ($iValue & ISize::MASK_WORD);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->iRegister = $iValue & ISize::MASK_LONG;
    }
}
