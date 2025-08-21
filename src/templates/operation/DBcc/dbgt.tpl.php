<?php

/**
 * DB
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_ZNV;
    if (
        (0 === $iCCR || IRegister::CCR_MASK_NV === $iCCR)
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

