<?php

/**
 * SLT
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    $iState = (
        IRegister::CCR_OVERFLOW === $iCCR ||
        IRegister::CCR_NEGATIVE === $iCCR
    ) ? 0xFF : 0;
<?php
    include 'fragments/set_conditional.tpl.php';
?>
};
