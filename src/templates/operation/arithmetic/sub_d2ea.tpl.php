<?php

/**
 * SUB_D2EA
 *
 * EA = EA - D
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
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_BYTE;
    $iDst  = $oEAMode->readByte();
    $iRes  = $iDst - $iSrc;
    $this->updateCCRMathByte($iSrc, $iDst, $iRes, false);
    $oEAMode->writebyte($iRes);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_WORD;
    $iDst  = $oEAMode->readWord();
    $iRes  = $iDst - $iSrc;
    $this->updateCCRMathWord($iSrc, $iDst, $iRes, false);
    $oEAMode->writeWord($iRes);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_LONG;
    $iDst  = $oEAMode->readLong();
    $iRes  = $iDst - $iSrc;
    $this->updateCCRMathLong($iSrc, $iDst, $iRes, false);
    $oEAMode->writeLong($iRes);
<?php
        break;
}


?>
};

