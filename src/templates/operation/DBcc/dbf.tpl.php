<?php

/**
 * DBF
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iReg   = &$this->oDataRegisters->aIndex[$iOpcode & IEffectiveAddress::MASK_BASE_REG];
    $iCount = (Sign::extWord($iReg & ISize::MASK_WORD) - 1) & ISize::MASK_WORD;
    $iReg   = ($iReg & ISize::MASK_INV_WORD) | $iCount;

    if (0xFFFF === $iCount) {
        $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
    } else {
        $this->iProgramCounter = (
            $this->iProgramCounter + Sign::extWord($this->oOutside->readWord(
                $this->iProgramCounter
            ))
        ) & ISize::MASK_LONG;
    }
};

