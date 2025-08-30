<?php

/**
 * OR
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iDataReg = ($oParams->iOpcode >> 9) & 7;
$iSize    = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
<?php

if ($oParams->iOpcode & ILogical::OP_OR_DIR) {
    // D OR <ea> => <ea>

    switch ($iSize) {
        case IOpcode::OP_SIZE_B:
?>
    $iValue = $oEAMode->readByte() | $this->oDataRegisters->iReg<?= $iDataReg ?>;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZByte($iValue);
    $oEAMode->writeByte($iValue);
<?php
            break;
        case IOpcode::OP_SIZE_W:
?>
    $iValue = $oEAMode->readWord() | $this->oDataRegisters->iReg<?= $iDataReg ?>;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZWord($iValue);
    $oEAMode->writeWord($iValue);
<?php
            break;
        case IOpcode::OP_SIZE_L:
?>
    $iValue = $oEAMode->readLong() | $this->oDataRegisters->iReg<?= $iDataReg ?>;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZLong($iValue);
    $oEAMode->writeLong($iValue);
<?php
            break;
    }

} else {
    // <ea> OR <D> => <ea>

    switch ($iSize) {
        case IOpcode::OP_SIZE_B:
?>
    $iValue = $this->oDataRegisters->iReg<?= $iDataReg ?> |= $oEAMode->readByte();
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZByte($iValue);
<?php
            break;
        case IOpcode::OP_SIZE_W:
?>
    $iValue = $this->oDataRegisters->iReg<?= $iDataReg ?> |= $oEAMode->readWord();
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZWord($iValue);
<?php
            break;
        case IOpcode::OP_SIZE_L:
?>
    $iValue = $this->oDataRegisters->iReg<?= $iDataReg ?> |= $oEAMode->readLong();
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZLong($iValue);
<?php
            break;
    }
}

?>
};
