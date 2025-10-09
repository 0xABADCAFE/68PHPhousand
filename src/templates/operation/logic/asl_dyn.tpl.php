<?php

/**
 * ASL dX,dY
 *
 * TODO This whole operation is a PITA
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize   = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iSrcReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
$iReg    = $oParams->iOpcode & IOpcode::MASK_EA_REG;

// Cases to worry about:

// Shift size is 0
//
// - Leave X flag alone
// - Clear C and V flags
// - Update N and Z flags
//
// Shift size > Operand Size
//
// - Set Z flag
// - Set V flag if operand was not zero
// - Clear XC flags
//
// Shift = Operand Size
//
// - Set Z flag
// - Set V flag if operand was not zero
// - Set XC flags based on LSB of operand
//
// Shift > 0 and < Operand Size
//
// - Construct an Shift + 1 sized left-aligned bitmask to AND with the operand
// - If the masked bits are not all zero or all 1 (i.e. equal to the mask) set the V flag
// - Set XC flags based on last bit shifted out
// - Set ZN flags based on result of shift
//
// Shift = 0
//
// - Leave X flag unchagned
// - Clear C and V flags
// - Set ZN flags based on operand
?>
return function(int $iOpcode): void {
    $iShift = $this->oDataRegisters->iReg<?= $iSrcReg ?> & 63;
    $iReg   = &$this->oDataRegisters->iReg<?= $iReg ?>;
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue  = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE);
    if ($iShift > 8) {
        $iReg   &= ISize::MASK_INV_BYTE;
        $this->iConditionRegister = IRegister::CCR_ZERO | ($iValue ? IRegister::CCR_OVERFLOW : 0);
    } else if ($iShift == 8) {
        $iReg   &= ISize::MASK_INV_BYTE;
        $this->iConditionRegister = IRegister::CCR_ZERO | (
            $iValue ? (IRegister::CCR_OVERFLOW | (($iValue & 1) ? IRegister::CCR_MASK_XC : 0)) : 0
        );
    } else if ($iShift) {
        $iReg   &= ISize::MASK_INV_BYTE;
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iResult    = $iValue << $iShift;
        $iCheckMask = ((1 << ($iShift + 1)) - 1) << (7 - $iShift);
        $iValue &= $iCheckMask;
        $this->updateNZByte($iResult);
        $this->iConditionRegister |= (
            ($iResult & 0x100) ? IRegister::CCR_MASK_XC : 0
        ) | ((
            ($iValue && $iValue !== $iCheckMask)
        ) ? IRegister::CCR_OVERFLOW : 0);
        $iReg |= ($iResult & ISize::MASK_BYTE);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZByte($iReg);
    }
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue  = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD);

    if ($iShift > 16) {
        $iReg   &= ISize::MASK_INV_WORD;
        $this->iConditionRegister = IRegister::CCR_ZERO | ($iValue ? IRegister::CCR_OVERFLOW : 0);
    } else if ($iShift == 16) {
        $iReg   &= ISize::MASK_INV_WORD;
        $this->iConditionRegister = IRegister::CCR_ZERO | (
            $iValue ? (IRegister::CCR_OVERFLOW | (($iValue & 1) ? IRegister::CCR_MASK_XC : 0)) : 0
        );
    } else if ($iShift) {
        $iReg   &= ISize::MASK_INV_WORD;
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iResult    = $iValue << $iShift;
        $iCheckMask = ((1 << ($iShift + 1)) - 1) << (15 - $iShift);
        $iValue &= $iCheckMask;
        $this->updateNZWord($iResult);
        $this->iConditionRegister |= (
            ($iResult & 0x10000) ? IRegister::CCR_MASK_XC : 0
        ) | ((
            ($iValue && $iValue !== $iCheckMask)
        ) ? IRegister::CCR_OVERFLOW : 0);
        $iReg |= ($iResult & ISize::MASK_WORD);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZWord($iReg);
    }
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG);
    if ($iShift > 32) {
        $iReg = 0;
        $this->iConditionRegister = IRegister::CCR_ZERO | ($iValue ? IRegister::CCR_OVERFLOW : 0);
    } else if ($iShift == 32) {
        $iReg = 0;
        $this->iConditionRegister = IRegister::CCR_ZERO | (
            $iValue ? (IRegister::CCR_OVERFLOW | (($iValue & 1) ? IRegister::CCR_MASK_XC : 0)) : 0
        );
    } else if ($iShift) {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
        $iResult    = $iValue << $iShift;
        $iCheckMask = ((1 << ($iShift + 1)) - 1) << (31 - $iShift);
        $iValue &= $iCheckMask;
        $this->updateNZLong($iResult);
        $this->iConditionRegister |= (
            ($iResult & 0x100000000) ? IRegister::CCR_MASK_XC : 0
        ) | ((
            ($iValue && $iValue !== $iCheckMask)
        ) ? IRegister::CCR_OVERFLOW : 0);
        $iReg = ($iResult & ISize::MASK_LONG);
    } else {
        $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
        $this->updateNZLong($iReg);
    }
<?php
    break;

}
?>
};
