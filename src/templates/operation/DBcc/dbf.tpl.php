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
<?php
if ($oParams->oAdditional->bUseJumpCache) {
?>
        if (isset($this->aJumpCache[$this->iProgramCounter])) {
            $this->iProgramCounter = $this->aJumpCache[$this->iProgramCounter];
        } else {
            $iSourceProgramCounter = $this->iProgramCounter;
            if ($this->updatePC(
                $this->iProgramCounter + Sign::extWord(
                    $this->oOutside->readWord($this->iProgramCounter)
                ),
                $iOpcode
            )) {
                $this->aJumpCache[$iSourceProgramCounter] = $this->iProgramCounter;
            }
        }
<?php
} else {
?>
        $this->updatePC(
            $this->iProgramCounter + Sign::extWord($this->oOutside->readWord(
                $this->iProgramCounter
            )),
            $iOpcode
        );
<?php
}
?>
    }
};

