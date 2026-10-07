<?php
    /**
     * Common body templte for Bcc instruction templates.
     */

    if ($iLSB === 0) {
        // When the short displacement is 0, we have a word displacement next.
?>
        $this->updatePC(
            $this->iProgramCounter + Sign::extWord($this->oOutside->readWord(
                $this->iProgramCounter
            )),
            $iOpcode
        );
    } else {
        $this->iProgramCounter = (($this->iProgramCounter + ISize::WORD) & ISize::MASK_LONG);
<?php
    } else if ($iLSB < 128) {
        // When the short displacement is 1-127, we can just add it to the program counter
?>
        $this->updatePC(
            $this->iProgramCounter + ($iOpcode & 0x7F),
            $iOpcode
        );
<?php
    } else if ($iLSB > 127 && $iLSB < 255) {
        // When the short displacement is 128-254, we convert it to signed.
?>
        $this->updatePC(
            ($this->iProgramCounter + ($iOpcode & 0xFF) - 256),
            $iOpcode
        );
<?php
    } else {
        // When the short displacement is 255 (-1), we have a long displacement (68020+)
?>
        $this->updatePC(
            $this->iProgramCounter + $this->oOutside->readLong(
                $this->iProgramCounter
            ),
            $iOpcode
        );
    } else {
        $this->iProgramCounter = ($this->iProgramCounter + ISize::LONG) & ISize::MASK_LONG;
<?php
    }
?>

