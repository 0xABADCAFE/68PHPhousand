<?php

/**
 * SHI
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    $iState = (
        ($this->iConditionRegister & IRegister::CCR_ZERO) ||
        IRegister::CCR_OVERFLOW === $iCCR ||
        IRegister::CCR_NEGATIVE === $iCCR
    ) ? 0xFF : 0;
<?php
    require 'common.tpl.php';
?>
};
