<?php
    /**
     * Common body templte for Bcc instruction templates.
     */

    if ($iLSB === 0) {
        // When the short displacement is 0, we have a word displacement next.
?>
        $this->iProgramCounter = (
            $this->iProgramCounter + $this->oOutside->readWord(
                $this->iProgramCounter
            )
        ) & ISize::MASK_LONG;
    } else {
        $this->iProgramCounter = ($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG;
<?php
    } else if ($iLSB < 128) {
        // When the short displacement is 1-127, we can just add it to the program counter
?>
        $this->iProgramCounter =
            ($this->iProgramCounter + ($iOpcode & 0x7F)) & ISize::MASK_LONG;
<?php
    } else if ($iLSB > 127 && $iLSB < 255) {
        // When the short displacement is 128-254, we convert it to signed.
?>
        $this->iProgramCounter =
            ($this->iProgramCounter + ($iOpcode & 0xFF) - 256) & ISize::MASK_LONG;
<?php
    } else {
        // When the short displacement is 255 (-1), we have a long displacement (68020+)
?>
        $this->iProgramCounter = (
            $this->iProgramCounter + $this->oOutside->readLong(
                $this->iProgramCounter
            )
        ) & ISize::MASK_LONG;
    } else {
        $this->iProgramCounter = ($this->iProgramCounter + ISize::LONG) & ISize::MASK_LONG;
<?php
    }
?>

