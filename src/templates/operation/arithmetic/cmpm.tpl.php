<?php

/**
 * CMPM.b/w/l (aX)+,(aY)+
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize    = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;

$iAddrXReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;
$iAddrYReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;

$iSrcMode = IOpcode::LSB_EA_AIPI|$iAddrXReg;
$iDstMode = IOpcode::LSB_EA_AIPI|$iAddrYReg;

?>
return function(int $iOpcode): void {
    $oSrcMode = $this->aSrcEAModes[<?= $iSrcMode ?>];
    $oDstMode = $this->aSrcEAModes[<?= $iDstMode ?>];
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iSrc = $oSrcMode->readByte();
    $iDst = $oDstMode->readByte();
    $iRes = $iDst - $iSrc;
    $this->updateCCRCMPByte($iSrc, $iDst, $iRes, false);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iSrc = $oSrcMode->readWord();
    $iDst = $oDstMode->readWord();
    $iRes = $iDst - $iSrc;
    $this->updateCCRCMPWord($iSrc, $iDst, $iRes, false);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iSrc = $oSrcMode->readLong();
    $iDst = $oDstMode->readLong();
    $iRes = $iDst - $iSrc;
    $this->updateCCRCMPLong($iSrc, $iDst, $iRes, false);
<?php
        break;
}
?>
};

