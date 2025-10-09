<?php

/**
 * ASL #d,dN
 *
 * X and C are set according to the last bit shifted out
 *
 * TODO V handling
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize      = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iImmediate = (($oParams->iOpcode & IOpcode::MASK_IMM_SMALL) >> IOpcode::REG_UP_SHIFT);
if (0 === $iImmediate) {
    $iImmediate = 8;
}

$iCheckMask = (1 << $iImmediate) - 1;

$iReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $this->iConditionRegister = 0;
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
        $iCheckMask <<= (8 - $iImmediate);
        if ($iImmediate == 8) {
            // Obvious special case for byte size. A byte shift of 8 is always zero. If the value is nonzero, we always
            // overflow. The last bit of the operand sets the carry/extend.

?>
    $iValue  = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->iConditionRegister = IRegister::CCR_ZERO | (
        $iValue ? (IRegister::CCR_OVERFLOW | (($iValue & 1) ? IRegister::CCR_MASK_XC : 0)) : 0
    );
<?php

        } else {
            // General case for shifts 1-7. We mask out N+1 bits to ensure all the bits shifted out and also the
            // remaining sign bit can be tested as a single block to be all zero or all one for overflow detection.
            $iCheckMask = ($iCheckMask >> 1) | ISize::SIGN_BIT_BYTE;

?>
    $iValue  = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    $iResult = $iValue << <?= $iImmediate ?>;
    $iValue &= <?= $iCheckMask ?>;
    $this->updateNZByte($iResult);
    $this->iConditionRegister |= (
        ($iResult & 0x100) ? IRegister::CCR_MASK_XC : 0
    ) | ((
        ($iValue && $iValue !== <?= $iCheckMask ?>)
    ) ? IRegister::CCR_OVERFLOW : 0);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iResult & ISize::MASK_BYTE);
<?php

        }
    break;

    case IOpcode::OP_SIZE_W:
        $iCheckMask <<= (16 - $iImmediate);
        $iCheckMask = ($iCheckMask >> 1) | ISize::SIGN_BIT_WORD;

?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);
    $iResult = $iValue << <?= $iImmediate ?>;
    $iValue &= <?= $iCheckMask ?>;
    $this->updateNZWord($iResult);
    $this->iConditionRegister |= (
        ($iResult & 0x10000) ? IRegister::CCR_MASK_XC : 0
    ) | ((
        ($iValue && $iValue !== <?= $iCheckMask ?>)
    ) ? IRegister::CCR_OVERFLOW : 0);

    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iResult & ISize::MASK_WORD);
<?php

    break;

    case IOpcode::OP_SIZE_L:
        $iCheckMask <<= (32 - $iImmediate);
        $iCheckMask = ($iCheckMask >> 1) | ISize::SIGN_BIT_LONG;

?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG);
    $iResult = $iValue << <?= $iImmediate ?>;
    $iValue &= <?= $iCheckMask ?>;
    $this->updateNZLong($iResult);
    $this->iConditionRegister |= (
        ($iResult & 0x100000000) ? IRegister::CCR_MASK_XC : 0
    ) | ((
        ($iValue && $iValue !== <?= $iCheckMask ?>)
    ) ? IRegister::CCR_OVERFLOW : 0);
    $this->oDataRegisters->iReg<?= $iReg ?> = ($iResult & ISize::MASK_LONG);
<?php

    break;

}
?>
};
