<?php

/**
 * LSL dX,dY
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize   = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iSrcReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
$iReg    = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $iShift = $this->oDataRegisters->iReg<?= $iSrcReg ?> & 63;
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    if ($iShift) {
        $iShift &= 7;
        $iValue <<= $iShift;
        $iValue |= ($iValue >> 8);
        $this->iConditionRegister |= ($iValue & IRegister::CCR_CARRY);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
    }
    $this->updateNZByte($iValue);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);
    if ($iShift) {
        $iShift &= 15;
        $iValue <<= $iShift;
        $iValue |= ($iValue >> 16);
        $this->iConditionRegister |= ($iValue & IRegister::CCR_CARRY);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
    }
    $this->updateNZWord($iValue);
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG) << $iShift;
    $iValue |= ($iValue >> 32);
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
