<?php

/**
 * ROXL dX,dY
 *
 * TODO extend and flags
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
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
        // First shift up by 1, and include the X flag as the new LSB
        // Then rotate by the size as a 9-bit field
        // Then shift down by 1 for the result and put the 8th bit back into X, C
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    if (($iShift = Opcode\IShifter::ROXX_MOD_9[$iShift])) {
        $iValue <<= 1;
        $iValue |= ($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4;
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iValue <<= $iShift;
        $iValue |= ($iValue >> 9);
        $iValue >>= 1;
        $this->iConditionRegister |= (($iValue & 0x100) ? IRegister::CCR_MASK_XC : 0);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
    } else {
        $this->iConditionRegister |= (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
    }
    $this->updateNZByte($iValue);
<?php
    break;

    case IOpcode::OP_SIZE_W:
        // First shift up by 1, and include the X flag as the new LSB
        // Then rotate by the size as a 17-bit field
        // Then shift down by 1 for the result and put the 8th bit back into X, C
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);
    if (($iShift = Opcode\IShifter::ROXX_MOD_17[$iShift])) {
        $iValue <<= 1;
        $iValue |= ($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4;
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iValue <<= $iShift;
        $iValue |= ($iValue >> 17);
        $iValue >>= 1;
        $this->iConditionRegister |= (($iValue & 0x10000) ? IRegister::CCR_MASK_XC : 0);
        $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
        $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
    } else {
        $this->iConditionRegister |= (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
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
