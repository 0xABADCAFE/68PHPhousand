<?php

/**
 * ROXL #d,dN
 *
 * TODO extend and flags
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

//$oParams->bDumpCode = true;

?>
return function(int $iOpcode): void {
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
        // First shift up by 1, and include the X flag as the new LSB
        // Then rotate by the size as a 9-bit field
        // Then shift down by 1 for the result and put the 8th bit back into X, C
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE) << 1;
    $iValue |= ($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
    $iValue <<= <?= $iImmediate ?>;
    $iValue |= ($iValue >> 9);
    $iValue >>= 1;
    $this->updateNZByte($iValue);
    $this->iConditionRegister |= (($iValue & 0x100) ? IRegister::CCR_MASK_XC : 0);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD) << <?= $iImmediate ?>;
    $iValue |= ($iValue >> 16);
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
    $iValue = ($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG) << <?= $iImmediate ?>;
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
