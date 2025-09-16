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
    private function updateNZByte(int $iValue): void
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
    private function updateNZWord(int $iValue): void
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
    private function updateNZLong(int $iValue): void
    {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_NZ;
        $this->iConditionRegister |= (
            ($iValue & ISize::MASK_LONG) ?
            (($iValue & ISize::SIGN_BIT_LONG) >> 28) : // Shift the MSB into N
            IRegister::CCR_ZERO
        );
    }

    /**
     * Full CCR checks for add/sub behaviour.
     *
     * For addition, overflow is set when the source and destination sign bits are the same and the newly
     * calculated sign bit different than the destination.
     * Carry and extend are both set if the result is larger than the operand size.
     *
     * For subtraction, overflow is set wwhen the source and destination sign bits are opposite and the
     * newly calcualted sign but is different than the destination.
     *
     */
    private function updateCCRMathByte(int $iSrc, int $iDst, int $iRes, bool $bAdd): void
    {
        $this->updateNZByte($iRes);
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iTest = $bAdd ? 0 : ISize::SIGN_BIT_BYTE;
        $iDiff = ($iSrc ^ $iDst) & ISize::SIGN_BIT_BYTE; // nonzero if src and dst are different sign
        $iFlip = ($iDst ^ $iRes) & ISize::SIGN_BIT_BYTE; // nonzero if dst sign will be swapped

        $this->iConditionRegister |= (
            ($iRes & 0x100) ? IRegister::CCR_MASK_XC : 0
        ) | (
            ($iTest === $iDiff && $iFlip) ? IRegister::CCR_OVERFLOW : 0
        );
    }

    private function updateCCRMathWord(int $iSrc, int $iDst, int $iRes, bool $bAdd): void
    {
        $this->updateNZWord($iRes);
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iTest = $bAdd ? 0 : ISize::SIGN_BIT_WORD;
        $iDiff = ($iSrc ^ $iDst) & ISize::SIGN_BIT_WORD; // nonzero if src and dst are different sign
        $iFlip = ($iDst ^ $iRes) & ISize::SIGN_BIT_WORD; // nonzero if dst sign will be swapped

        $this->iConditionRegister |= (
            ($iRes & 0x10000) ? IRegister::CCR_MASK_XC : 0
        ) | (
            ($iTest === $iDiff && $iFlip) ? IRegister::CCR_OVERFLOW : 0
        );
    }

    private function updateCCRMathLong(int $iSrc, int $iDst, int $iRes, bool $bAdd): void
    {
        $this->updateNZLong($iRes);
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iTest = $bAdd ? 0 : ISize::SIGN_BIT_LONG;
        $iDiff = ($iSrc ^ $iDst) & ISize::SIGN_BIT_LONG; // nonzero if src and dst are different sign
        $iFlip = ($iDst ^ $iRes) & ISize::SIGN_BIT_LONG; // nonzero if dst sign will be swapped

        $this->iConditionRegister |= (
            ($iRes & 0x100000000) ? IRegister::CCR_MASK_XC : 0
        ) | (
            ($iTest === $iDiff && $iFlip) ? IRegister::CCR_OVERFLOW : 0
        );
    }


}
