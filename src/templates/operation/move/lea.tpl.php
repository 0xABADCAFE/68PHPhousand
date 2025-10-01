<?php
/**
 * LEA
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\IMove;


assert(!empty($oParams), new \LogicException());

$iAddressReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;

?>
return function(int $iOpcode): void {
    $this->oAddressRegisters->iReg<?= $iAddressReg ?> = $this->aDstEAModes[$iOpcode & 63]->getAddress();
};
