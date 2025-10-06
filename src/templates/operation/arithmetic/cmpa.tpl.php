<?php

/**
 * CMPA.w/l <ea>,aN
 *
 */
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

//require_once $oParams->sBasePath . '/operation/fragments/inline.php';

assert(!empty($oParams), new \LogicException());

$iMode    = ($oParams->iOpcode >> IOpcode::LSB_EA_SIZE) & 7;
$iDataReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    $oEAMode = $this->aSrcEAModes[$iOpcode & 63];
<?php
switch ($iMode) {
    case 0b011: // Word - word is sign extended to 32 bits and the comparison
                // is done against full register.
?>
    $iSrc  = Sign::extWord($oEAMode->readWord()) & ISize::MASK_LONG ;
    $iDst  = $this->oAddressRegisters->iReg<?= $iDataReg ?> & ISize::MASK_LONG;
    $iRes  = $iDst - $iSrc;
    $this->updateCCRCMPLong($iSrc, $iDst, $iRes, false);
<?php
        break;
    case 0b111: //Long
?>
    $iSrc  = $oEAMode->readLong();
    $iDst  = $this->oAddressRegisters->iReg<?= $iDataReg ?> & ISize::MASK_LONG;
    $iRes  = $iDst - $iSrc;
    $this->updateCCRCMPLong($iSrc, $iDst, $iRes, false);
<?php
        break;
}
?>
};

