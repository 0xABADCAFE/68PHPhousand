<?php

/**
 * DBNE
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    if (
        0 === ($this->iConditionRegister & IRegister::CCR_ZERO)
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

