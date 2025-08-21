<?php

/**
 * BRA
 *
 * Generates code for handling the BRA instruction. There are 256 opcode words from $6000-$60FF,
 * where the LSB contains a signed 8-bit displacement with 2 special cases:
 * $00 - indicates a signed 16-bit extension word follows, with a 16-bit displacement.
 * $FF - indicates a signed 32-bit extension word follows (68020+).
 *
 * Note that the branch displacement is measured in bytes, meaning an odd displacement is possible
 * albeit not legal.
 */

assert(!empty($oParams), new \LogicException());

$iLSB = ($oParams->iOpcode & 0xFF);
?>
return function(int $iOpcode): void {
<?php
    if ($iLSB === 0) {
        // When the short displacement is 0, we have a word displacement next.
?>
    $this->iProgramCounter = (
        $this->iProgramCounter + $this->oOutside->readWord(
            $this->iProgramCounter
        )
    ) & ISize::MASK_LONG;
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
<?php
    }
?>
};
