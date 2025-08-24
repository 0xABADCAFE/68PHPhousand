<?php

/**
 * BTST
 *
 */

assert(!empty($oParams), new \LogicException());

$bTargetIsReg = IOpcode::LSB_EA_D === $oParams->iOpcode & IOpcode::LSB_EA_MODE_MASK;
$iModulo      = $bTargetIsReg ? 7 : 31;

?>
return function(int $iOpcode): void {
<?php
if ($oParams->iOpcode & Opcode\ISingleBit::OP_BTST_DN) {

    // Bit to test is dynamic, stored in the source register.
    $iSourceReg = ($oParams->iOpcode >> 9) & 7;
?>
    $iTestBit = 1 << (($this->oDataRegisters->iReg<?= $iSourceReg ?>) & <?= $iModulo ?>);
<?php
    if ($bTargetIsReg) {
        // Operand to test is a register, so we permit all 32 bits to be tested
        $iTargetReg = $oParams->iOpcode & 7;
?>
    $iValue = $this->oDataRegisters->iReg<?= $iTargetReg ?>;
<?php
    } else {
        // Operand to test is an EA Byte, so we we only
?>
    $iValue = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readByte();
<?php
    }
?>

<?php
} else {
    // Bit to test is immediate
}

?>
    ($iValue & $iTestBit) ?
        ($this->iConditionRegister &= IRegister::CCR_CLEAR_Z) :
        ($this->iConditionRegister |= IRegister::CCR_ZERO);
};
