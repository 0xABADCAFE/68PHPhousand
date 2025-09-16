<?php

/**
 * ADDQ
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize      = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iImmediate = (($oParams->iOpcode & IOpcode::MASK_IMM_SMALL) >> IOpcode::REG_UP_SHIFT);
if (0 === $iImmediate) {
    $iImmediate = 8;
}

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iDst = $oEAMode->readByte();
    $iRes = $iDst + <?= $iImmediate ?>;
    $this->updateCCRMathByte(<?= $iImmediate ?>, $iDst, $iRes, true);
    $oEAMode->writeByte($iRes);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iDst = $oEAMode->readWord();
    $iRes = $iDst + <?= $iImmediate ?>;
<?php
    if (!$oParams->oAdditional->bNoCCR) {
?>
    $this->updateCCRMathWord(<?= $iImmediate ?>, $iDst, $iRes, true);
<?php
    }
?>
    $oEAMode->writeWord($iRes);

<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iDst = $oEAMode->readLong();
    $iRes = $iDst + <?= $iImmediate ?>;
<?php
    if (!$oParams->oAdditional->bNoCCR) {
?>
    $this->updateCCRMathLong(<?= $iImmediate ?>, $iDst, $iRes, true);
<?php
    }
?>
    $oEAMode->writeLong($iRes);

<?php
        break;
}


?>
};

