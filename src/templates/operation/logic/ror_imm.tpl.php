<?php

/**
 * ROR #d,dN
 *
 * X and C are set according to the last bit shifted out
 *
 * TODO flags are probably all over the place
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
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE) << <?= (8 - $iImmediate) ?>;;
    $iValue |= ($iValue >> 8);
    $this->updateNZByte($iValue);
    $this->iConditionRegister |= (($iValue & 0x80) >> 7);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD) << <?= (16 - $iImmediate) ?>;;
    $iValue |= ($iValue >> 16);
    $this->updateNZWord($iValue);
    $this->iConditionRegister |= (($iValue & 0x8000) >> 15);
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
