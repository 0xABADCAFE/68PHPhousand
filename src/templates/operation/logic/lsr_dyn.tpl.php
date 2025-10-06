<?php

/**
 * LSR dX,dY
 *
 * TODO X/C/V Handling
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize   = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iSrcReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
$iReg    = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $iShift = $this->oDataRegisters->iReg<?= $iSrcReg ?> & 63;
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    if ($iShift > 0) {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE) >> ($iShift - 1);
        $this->iConditionRegister |= (
            ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
        );
        $iValue >>= 1;
        $this->updateNZByte($iValue);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZByte($this->oDataRegisters->iReg<?= $iReg ?>);
    }
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    if ($iShift > 0) {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD) >> ($iShift - 1);
        $this->iConditionRegister |= (
            ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
        );
        $iValue >>= 1;
        $this->updateNZWord($iValue);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZWord($this->oDataRegisters->iReg<?= $iReg ?>);
    }
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    if ($iShift > 0) {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG) >> ($iShift - 1);
        $this->iConditionRegister |= (
            ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
        );
        $iValue >>= 1;
        $this->updateNZLong($iValue);
        $this->oDataRegisters->iReg<?= $iReg ?> = ($iValue & ISize::MASK_LONG);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZLong($this->oDataRegisters->iReg<?= $iReg ?>);
    }
<?php
    break;

}
?>
};
