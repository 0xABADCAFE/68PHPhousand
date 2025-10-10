<?php

/**
 * EXT
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode\ILogical;

assert(!empty($oParams), new \LogicException());

$iMode    = ($oParams->iOpcode & IOpcode::MASK_OP_MODE) >> IOpcode::LSB_EA_SIZE;
$iRegNum  = $oParams->iOpcode & IOpcode::MASK_EA_REG;

?>
return function(int $iOpcode): void {
    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
    $iReg = &$this->oDataRegisters->iReg<?= $iRegNum ?>;
<?php
switch ($iMode) {

    case 0b010:
        // Byte to Word
?>
    $iReg = $iReg & ISize::MASK_INV_WORD | (Sign::extByte($iReg) & ISize::MASK_WORD);
    $this->updateNZWord($iReg);
<?php
        break;

    case 0b011:
        // Word to long
?>
    $iReg = Sign::extWord($iReg) & ISize::MASK_LONG;
    $this->updateNZLong($iReg);
<?php

        break;

    case 0b111:
        // Byte to Long (020+)
?>
    $iReg = Sign::extByte($iReg) & ISize::MASK_LONG;
    $this->updateNZLong($iReg);
<?php
        break;
    default:
        throw new \LogicException("Invalid mode field for EXT");
}
?>
};

