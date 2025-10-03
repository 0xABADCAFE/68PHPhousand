<?php

/**
 * CMP.b/w/l <ea>,dN
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize    = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iDataReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iSrc  = $oEAMode->readByte();
    $iDst  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_BYTE;
    $iRes  = $iDst - $iSrc;
    $this->updateCCRCMPByte($iSrc, $iDst, $iRes, false);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iSrc  = $oEAMode->readWord();
    $iDst  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_WORD;
    $iRes  = $iDst - $iSrc;
    $this->updateCCRCMPWord($iSrc, $iDst, $iRes, false);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iSrc  = $oEAMode->readLong();
    $iDst  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_LONG;
    $iRes  = $iDst - $iSrc;
    $this->updateCCRCMPLong($iSrc, $iDst, $iRes, false);
<?php
        break;
}
?>
};

