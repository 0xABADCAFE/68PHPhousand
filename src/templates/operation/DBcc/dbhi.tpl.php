<?php

/**
 * DBMI
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    if (
        0 === ($this->iConditionRegister & IRegister::CCR_MASK_ZC)
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

