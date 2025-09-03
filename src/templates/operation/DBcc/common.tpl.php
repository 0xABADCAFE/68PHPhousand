<?php
    /**
     * Common body templte for Bcc instruction templates.
     */

    // If the DBcc condition is true, we immediately skip
    // Othwerise decrement the word in the data register by 1
    // If the word value is not -1, take the branch.
    // Using direct manipulation for speed here rather than the DataRegister EA, but
    // maybe that's a cleaner option
?>
        $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
    } else {
        $iReg   = &$this->oDataRegisters->aIndex[$iOpcode & IEffectiveAddress::MASK_BASE_REG];
        $iCount = (Sign::extWord($iReg & ISize::MASK_WORD) - 1) & ISize::MASK_WORD;
        $iReg   = ($iReg & ISize::MASK_INV_WORD)|$iCount;

        if (0xFFFF === $iCount) {
            $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
        } else {
<?php
if ($oParams->oAdditional->bUseJumpCache) {
?>
            $this->iProgramCounter = $this->aJumpCache[$this->iProgramCounter] ?? (
                $this->aJumpCache[$this->iProgramCounter] = (
                    (
                        $this->iProgramCounter + Sign::extWord(
                            $this->oOutside->readWord($this->iProgramCounter)
                        )
                    ) & ISize::MASK_LONG
                )
            );
<?php
} else {
?>
            $this->iProgramCounter = (
                $this->iProgramCounter + Sign::extWord($this->oOutside->readWord(
                    $this->iProgramCounter
                ))
            ) & ISize::MASK_LONG;
<?php
}
?>
        }
