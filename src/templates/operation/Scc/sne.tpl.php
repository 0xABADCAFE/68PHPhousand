<?php

/**
 * SNE
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = (0 === ($this->iConditionRegister & IRegister::CCR_ZERO)) ? 0xFF : 0;
<?php
    include '../fragments/set_conditional.tpl.php';
?>
};
