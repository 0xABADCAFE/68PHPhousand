<?php

/**
 * SUBA.w/l <ea>,aN
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iMode       = ($oParams->iOpcode >> 6) & 7;
$iAddressReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aSrcEAModes[$iOpcode & 63];
    $iReg    = &$this->oAddressRegisters->iReg<?= $iAddressReg ?>;
<?php
switch ($iMode) {
    //case IOpcode::OP_SIZE_W:
    case 0b011: // Word
?>
    $iSrc  = $oEAMode->readWord();
    $iSrc |= ($iSrc & ISize::SIGN_BIT_WORD) ? ISize::MASK_INV_WORD : 0;
    $iReg  = ($iReg - $iSrc) & ISize::MASK_LONG;
<?php
        break;
    //case IOpcode::OP_SIZE_L:
    case 0b111:
?>
    $iReg  = ($iReg - $oEAMode->readLong()) & ISize::MASK_LONG;
<?php
        break;
}

?>
};

