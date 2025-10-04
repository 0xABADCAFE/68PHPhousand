<?php

/**
 * ADD_D2EA
 *
 * EA = EA + D
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iSize    = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iDataReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    assert(
        isset($this->aDstEAModes[$iOpcode & 63]),
        new \LogicException(
            'Missing addressing mode ' . ($iOpcode & 63) .
            ' from set {' . implode(', ', array_keys($this->aDstEAModes)). '}'
        )
    );
    $oEAMode = $this->aDstEAModes[$iOpcode & 63];
<?php

switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_BYTE;
    $iDst  = $oEAMode->readByte();
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathByte($iSrc, $iDst, $iRes, true);
    $oEAMode->writebyte($iRes);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_WORD;
    $iDst  = $oEAMode->readWord();
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathWord($iSrc, $iDst, $iRes, true);
    $oEAMode->writeWord($iRes);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iSrc  = $this->oDataRegisters->iReg<?= $iDataReg ?> & ISize::MASK_LONG;
    $iDst  = $oEAMode->readLong();
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathLong($iSrc, $iDst, $iRes, true);
    $oEAMode->writeLong($iRes);

<?php
        break;
}


?>
};

