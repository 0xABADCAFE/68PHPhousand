<?php
/**
 * SWAP
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\IMove;


assert(!empty($oParams), new \LogicException());

$iXReg = ($oParams->iOpcode >> IOpcode::REG_UP_SHIFT) & IOpcode::MASK_EA_REG;
$iYReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
<?php
switch ($oParams->oAdditional->iMode) {

    case IMove::OP_EXG_DD:
        if ($iXReg != $iYReg) {
?>
    $iTemp = $this->oDataRegisters->iReg<?= $iYReg ?>;
    $this->oDataRegisters->iReg<?= $iYReg ?> = $this->oDataRegisters->iReg<?= $iXReg ?>;
    $this->oDataRegisters->iReg<?= $iXReg ?> = $iTemp;
<?php
        }
        break;

    case IMove::OP_EXG_AA:
        if ($iXReg != $iYReg) {
?>
    $iTemp = $this->oAddressRegisters->iReg<?= $iYReg ?>;
    $this->oAddressRegisters->iReg<?= $iYReg ?> = $this->oAddressRegisters->iReg<?= $iXReg ?>;
    $this->oAddressRegisters->iReg<?= $iXReg ?> = $iTemp;
<?php
        }
        break;

    case IMove::OP_EXG_DA:
?>
    $iTemp = $this->oAddressRegisters->iReg<?= $iYReg ?>;
    $this->oAddressRegisters->iReg<?= $iYReg ?> = $this->oDataRegisters->iReg<?= $iXReg ?>;
    $this->oDataRegisters->iReg<?= $iXReg ?> = $iTemp;
<?php
        break;
}
?>
};
