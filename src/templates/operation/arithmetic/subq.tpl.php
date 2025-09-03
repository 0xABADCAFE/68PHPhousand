<?php

/**
 * SUBQ
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize      = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iImmediate = 1 + (($oParams->iOpcode & IOpcode::MASK_IMM_SMALL) >> IOpcode::REG_UP_SHIFT);
if (0 === $iImmediate) {
    $iImmediate = 8;
}

// TODO CV flags

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iValue = $oEAMode->readByte() - <?= $iImmediate ?>;

    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZByte($iValue);
    $oEAMode->writeByte($iValue);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iValue = $oEAMode->readWord() - <?= $iImmediate ?>;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZWord($iValue);
    $oEAMode->writeWord($iValue);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iValue = $oEAMode->readLong() - <?= $iImmediate ?>;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZLong($iValue);
    $oEAMode->writeLong($iValue);
<?php
        break;
}


?>
};

