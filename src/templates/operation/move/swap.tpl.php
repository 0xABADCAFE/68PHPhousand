<?php
/**
 * SWAP
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\IMove;


assert(!empty($oParams), new \LogicException());

$iDataReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $iValue = $this->oDataRegisters->iReg<?= $iDataReg?> & 0xFFFFFFFF;
    $iValue = (($iValue >> 16) | ($iValue << 16)) & 0xFFFFFFFF;
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $this->updateNZLong($iValue);
    $this->oDataRegisters->iReg<?= $iDataReg ?> = $iValue;
};
