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

namespace ABadCafe\G8PHPhousand\Processor;

trait TArithmeticLogicUnit
{
    use TRegisterUnit;

    /**
     * Tests the provided value as a byte and sets the negative/zero flags accordingly.
     */
    protected function updateNZByte(int $iValue): void
    {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZ;
        $this->iConditionRegister |= (
            ($iValue & ISize::MASK_BYTE) ?
            (($iValue & ISize::SIGN_BIT_BYTE) >> 4) : // Shift the MSB into N
            IRegister::CCR_ZERO
        );
    }

    /**
     * Tests the provided value as a word and sets the negative/zero flags accordingly.
     */
    protected function updateNZWord(int $iValue): void
    {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZ;
        $this->iConditionRegister |= (
            ($iValue & ISize::MASK_WORD) ?
            (($iValue & ISize::SIGN_BIT_WORD) >> 12) : // Shift MSB into N
            IRegister::CCR_ZERO
        );
    }

    /**
     * Tests the provided value as a long and sets the negative/zero flags accordingly.
     */
    protected function updateNZLong(int $iValue): void
    {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZ;
        $this->iConditionRegister |= (
            ($iValue & ISize::MASK_LONG) ?
            (($iValue & ISize::SIGN_BIT_LONG) >> 28) : // Shift the MSB into N
            IRegister::CCR_ZERO
        );
    }

}
