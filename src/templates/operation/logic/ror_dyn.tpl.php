<?php

/**
 * ROR dX,dY
 *
 * TODO X/C/V Handling
 *
 * TODO flags are probably all over the place
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize   = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iSrcReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
$iReg    = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
    $iShift = $this->oDataRegisters->iReg<?= $iSrcReg ?> & 0x63;
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue   = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    $iShifted = $iValue << (8 - $iShift);
    $iValue   = ($iValue >> $iShift) | $iShifted;
    $this->updateNZByte($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue   = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);
    $iShifted = $iValue << (16 - $iShift);
    $iValue   = ($iValue >> $iShift) | $iShifted;
    $this->updateNZWord($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    $iValue   = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG);
    $iShifted = $iValue << (32 - $iShift);
    $iValue   = ($iValue >> $iShift) | $iShifted;
    $this->updateNZLong($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> = ($iValue & ISize::MASK_LONG);
<?php
    break;

}
?>
};
