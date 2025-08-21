<?php

/**
 * DBLS
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    if (
        ($this->iConditionRegister & IRegister::CCR_MASK_ZC)
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

