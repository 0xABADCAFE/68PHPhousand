<?php

/**
 * SHI
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = ($this->iConditionRegister & IRegister::CCR_MASK_ZC) ? 0xFF : 0;
<?php
    include '../fragments/set_conditional.tpl.php';
?>
};
