<?php

/**
 * BCS
 *
 * Generates code for handling the BCS instruction. There are 256 opcode words from $6x00-$6xFF,
 * where the LSB contains a signed 8-bit displacement with 2 special cases:
 * $00 - indicates a signed 16-bit extension word follows, with a 16-bit displacement.
 * $FF - indicates a signed 32-bit extension word follows (68020+).
 *
 * Note that the branch displacement is measured in bytes, meaning an odd displacement is possible
 * albeit not legal.
 *
 * BCS takes the branch when the C flag is set
 */

assert(!empty($oParams), new \LogicException());

$iLSB = ($oParams->iOpcode & 0xFF);
?>
return function(int $iOpcode): void {
    if (
        ($this->iConditionRegister & IRegister::CCR_CARRY)
    ) {
<?php
    include 'fragments/branch_conditional.tpl.php';

