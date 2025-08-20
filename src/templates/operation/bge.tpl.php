<?php

/**
 * BGE
 *
 * Generates code for handling the BGE instruction. There are 256 opcode words from $6x00-$6xFF,
 * where the LSB contains a signed 8-bit displacement with 2 special cases:
 * $00 - indicates a signed 16-bit extension word follows, with a 16-bit displacement.
 * $FF - indicates a signed 32-bit extension word follows (68020+).
 *
 * Note that the branch displacement is measured in bytes, meaning an odd displacement is possible
 * albeit not legal.
 *
 * BGE takes the branch when the N and V flags are either both clear or both set.
 */

assert(!empty($oParams), new \LogicException());

$iLSB = ($oParams->iOpcode & 0xFF);
?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    if (0 === $iCCR || IRegister::CCR_MASK_NV === $iCCR) {
<?php
    include 'fragments/branch_conditional.tpl.php';
?>
    }
};
