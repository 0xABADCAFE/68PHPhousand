<?php

/**
 * BCLR
 *
 */
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\Opcode;

assert(!empty($oParams), new \LogicException());

$iUseCase  = IOpcode::LSB_EA_D === ($oParams->iOpcode & IOpcode::LSB_EA_MODE_MASK) ? 1 : 0;
$iUseCase |= ($oParams->iOpcode & Opcode\ISingleBit::OP_BTST_DN) ? 2 : 0;

?>
return function(int $iOpcode): void {
<?php

switch ($iUseCase) {

    case 0: // Immediate bit position, EA target, byte access
?>
    $iTestBit = 1 << ($this->oOutside->readWord($this->iProgramCounter) & 7);
    $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
    $oEAMode  = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
    $iValue   = $oEAMode->readByte();
    ($iValue & $iTestBit) ?
        ($this->iConditionRegister &= IRegister::CCR_CLEAR_Z) :
        ($this->iConditionRegister |= IRegister::CCR_ZERO);
    $oEAMode->writeByte($iValue & ~$iTestBit);
<?php
        break;

///////////////////////////////////////////////////////////////////////////////

    case 1: // Immediate bit position, register target, long access
        $iTargetReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;
?>
    $iValue   = $this->oDataRegisters->iReg<?= $iTargetReg ?>;
    $iTestBit = 1 << ($this->oOutside->readWord($this->iProgramCounter) & 31);
    $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
    ($iValue & $iTestBit) ?
        ($this->iConditionRegister &= IRegister::CCR_CLEAR_Z) :
        ($this->iConditionRegister |= IRegister::CCR_ZERO);
    $this->oDataRegisters->iReg<?= $iTargetReg ?> &= ~$iTestBit;

<?php
        break;

///////////////////////////////////////////////////////////////////////////////

    case 2: // Dynamic bit position, EA target, byte access
        $iSourceReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
?>
    $oEAMode  = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
    $iValue   = $oEAMode->readByte();
    $iTestBit = 1 << (($this->oDataRegisters->iReg<?= $iSourceReg ?>) & 7);
    ($iValue & $iTestBit) ?
        ($this->iConditionRegister &= IRegister::CCR_CLEAR_Z) :
        ($this->iConditionRegister |= IRegister::CCR_ZERO);
    $oEAMode->writeByte($iValue & ~$iTestBit);
<?php
        break;

///////////////////////////////////////////////////////////////////////////////

    case 3: // Dynamic bit position, register target, long access
        $iSourceReg = ($oParams->iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT;
        $iTargetReg = $oParams->iOpcode & IOpcode::MASK_EA_REG;
?>
    $iValue = $this->oDataRegisters->iReg<?= $iTargetReg ?>;
    $iTestBit = 1 << (($this->oDataRegisters->iReg<?= $iSourceReg ?>) & 31);
    ($iValue & $iTestBit) ?
        ($this->iConditionRegister &= IRegister::CCR_CLEAR_Z) :
        ($this->iConditionRegister |= IRegister::CCR_ZERO);
    $this->oDataRegisters->iReg<?= $iTargetReg ?> &= ~$iTestBit;
<?php
        break;

///////////////////////////////////////////////////////////////////////////////

}
?>
};
