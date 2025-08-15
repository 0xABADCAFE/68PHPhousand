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
 * Address Register Direct EA
 */
class AddressRegister extends Register
{
    use EAMode\TWithoutLatch;

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        throw new LogicException('Cannot byte-size write to address register');
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        // Sign extend from 16 => 32 bit
        $iValue &= ISize::MASK_WORD;
        $this->iRegister = $iValue | ($iValue & ISize::SIGN_BIT_WORD ? ISize::MASK_INV_WORD : 0);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->iRegister = $iValue & ISize::MASK_LONG;
    }
}
