<?php

/**
 * ADD_EA2D
 *
 * D = D + EA
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iMode       = ($oParams->iOpcode >> 6) & 7;
$iAddressReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
    $iReg    = &$this->oAddressRegisters->iReg<?= $iAddressReg ?>;
<?php
switch ($iMode) {
    //case IOpcode::OP_SIZE_W:
    case 0b011: // Word

    // TODO sign extend
?>
    $iSrc  = $oEAMode->readWord();
    $iDst  = $iReg & ISize::MASK_WORD;
    $iRes  = $iDst - $iSrc;
    $iReg &= ISize::MASK_INV_WORD;
    $iReg |= ($iRes & ISize::MASK_WORD);
<?php
        break;
    //case IOpcode::OP_SIZE_L:
    case 0b111:
?>
    $iSrc  = $oEAMode->readLong();
    $iDst  = $iReg & ISize::MASK_LONG;
    $iRes  = $iDst - $iSrc;
    $iReg &= ISize::MASK_INV_LONG;
    $iReg |= ($iRes & ISize::MASK_LONG);
<?php
        break;
}

?>
};

