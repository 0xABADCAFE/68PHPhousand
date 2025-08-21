<?php

/**
 * DBPL
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    if (
        0 === ($this->iConditionRegister & IRegister::CCR_CARRY)
    ) {
<?php
    include '../fragments/dbra_conditional.tpl.php';
?>
    }
};

