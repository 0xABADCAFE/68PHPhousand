<?php

/**
 * ROXR #d,dN
 *
 * TODO - extend and flags
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize      = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iImmediate = (($oParams->iOpcode & IOpcode::MASK_IMM_SMALL) >> IOpcode::REG_UP_SHIFT);
if (0 === $iImmediate) {
    $iImmediate = 8;
}
$iReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
        // First prepend the X flag to the operand to create the 9-bit field.
        // Rotate the 9-bit field using a pair of contra-shifts
        // Bit 9 of the result is the new X and C flag values
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    $iValue |= (($this->iConditionRegister & IRegister::CCR_EXTEND) << 4);
    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
    $iValue <<= <?= (9 - $iImmediate) ?>;
    $iValue |= ($iValue >> 9);
    $this->updateNZByte($iValue);
    $this->iConditionRegister |= (($iValue & 0x100) ? IRegister::CCR_MASK_XC : 0);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue   = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);
    $iShifted = $iValue << <?= (16 - $iImmediate) ?>;
    $iValue   = ($iValue >> <?= $iImmediate ?>) | $iShifted;
    $this->updateNZWord($iValue);
    $this->iConditionRegister |= (
        ($iValue & 0x10000) ? IRegister::CCR_MASK_XC : 0
    );
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    $iValue   = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG);
    $iShifted = $iValue << <?= (32 - $iImmediate) ?>;
    $iValue   = ($iValue >> <?= $iImmediate ?>) | $iShifted;
    $this->updateNZLong($iValue);
    $this->iConditionRegister |= (
        ($iValue & 0x100000000) ? IRegister::CCR_MASK_XC : 0
    );
    $this->oDataRegisters->iReg<?= $iReg ?> = ($iValue & ISize::MASK_LONG);
<?php
    break;

}
?>
};
