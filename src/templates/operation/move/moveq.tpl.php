<?php
/**
 * MOVEQ
 *
 * Since the expectation of moveq is that it's quick, we might as well roll a template as
 * this can do away with worrying about decoding the target register or the immediate or
 * sign extending it. We can just inline if fully.
 *
 */
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\IMove;

assert(!empty($oParams), new \LogicException());

$iDataReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;

$iImmediate = $oParams->iOpcode & ISize::MASK_BYTE;

?>
return function(int $iOpcode): void {
<?php
if (0 === $iImmediate) {
    // Clear the register and set the zero flag
?>
    $this->oDataRegisters->iReg<?= $iDataReg ?> = 0;
    $this->iConditionRegister |= IRegister::CCR_ZERO;
<?php
} else if ($iImmediate < 0x80) {
    // Set the immediate and clear the zero flag
?>
    $this->oDataRegisters->iReg<?= $iDataReg ?> = <?= $iImmediate ?>;
    $this->iConditionRegister &= ~IRegister::CCR_ZERO;
<?php
} else {
    // Set the 2's complement negative value, clear the zero flag and set the negative
?>
    $this->oDataRegisters->iReg<?= $iDataReg ?> = <?= 0xFFFFFF00 | $iImmediate ?>;
    $this->iConditionRegister = (
        $this->iConditionRegister & IRegister::CCR_EXTEND
    ) | IRegister::CCR_NEGATIVE;
<?php
}
?>
};
