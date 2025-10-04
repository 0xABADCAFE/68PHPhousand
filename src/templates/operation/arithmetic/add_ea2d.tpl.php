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

$iSize    = $oParams->iOpcode & IOpcode::MASK_OP_SIZE;
$iDataReg = (($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT);

?>
return function(int $iOpcode): void {
    assert(
        isset($this->aSrcEAModes[$iOpcode & 63]),
        new \LogicException(
            'Missing addressing mode ' . ($iOpcode & 63) .
            ' from set {' . implode(', ', array_keys($this->aSrcEAModes)). '}'
        )
    );
    $oEAMode = $this->aSrcEAModes[$iOpcode & 63];
    $iReg    = &$this->oDataRegisters->iReg<?= $iDataReg ?>;
<?php
switch ($iSize) {
    case IOpcode::OP_SIZE_B:
?>
    $iSrc  = $oEAMode->readByte();
    $iDst  = $iReg & ISize::MASK_BYTE;
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathByte($iSrc, $iDst, $iRes, true);
    $iReg &= ISize::MASK_INV_BYTE;
    $iReg |= ($iRes & ISize::MASK_BYTE);
<?php
        break;
    case IOpcode::OP_SIZE_W:
?>
    $iSrc  = $oEAMode->readWord();
    $iDst  = $iReg & ISize::MASK_WORD;
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathWord($iSrc, $iDst, $iRes, true);
    $iReg &= ISize::MASK_INV_WORD;
    $iReg ($iRes & ISize::MASK_WORD);
<?php
        break;
    case IOpcode::OP_SIZE_L:
?>
    $iSrc  = $oEAMode->readLong();
    $iDst  = $iReg & ISize::MASK_LONG;
    $iRes  = $iSrc + $iDst;
    $this->updateCCRMathLong($iSrc, $iDst, $iRes, true);
    $iReg &= ISize::MASK_LONG;
<?php
        break;
}

?>
};

