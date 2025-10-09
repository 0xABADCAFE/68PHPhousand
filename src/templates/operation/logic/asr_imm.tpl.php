<?php

/**
 * ASR #d,dN
 *
 * X and C are set according to the last bit shifted out
 *
 * TODO - Handle X/V correctly, sign propagation
 *
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
    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue = Sign::extByte($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_BYTE) >> <?= $iImmediate - 1 ?>;
    $this->iConditionRegister |= (
        ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
    );
    $iValue >>= 1;
    $this->updateNZByte($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_BYTE;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_BYTE);
<?php
    break;

    case IOpcode::OP_SIZE_W:
?>
    $iValue = Sign::extWord($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_WORD) >> <?= $iImmediate - 1 ?>;
    $this->iConditionRegister |= (
        ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
    );
    $iValue >>= 1;
    $this->updateNZWord($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> &= ISize::MASK_INV_WORD;
    $this->oDataRegisters->iReg<?= $iReg ?> |= ($iValue & ISize::MASK_WORD);
<?php
    break;

    case IOpcode::OP_SIZE_L:
?>
    $iValue = Sign::extLong($this->oDataRegisters->iReg<?= $iReg ?> & ISize::MASK_LONG) >> <?= $iImmediate - 1 ?>;
    $this->iConditionRegister |= (
        ($iValue & 1) ? IRegister::CCR_MASK_XC : 0
    );
    $iValue >>= 1;
    $this->updateNZLong($iValue);
    $this->oDataRegisters->iReg<?= $iReg ?> = ($iValue & ISize::MASK_LONG);
<?php
    break;

}
?>
};
