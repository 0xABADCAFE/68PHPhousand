<?php

/**
 * DBMI
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    if (
        ($this->iConditionRegister & IRegister::CCR_ZERO) ||
        IRegister::CCR_OVERFLOW === $iCCR ||
        IRegister::CCR_NEGATIVE === $iCCR
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

