<?php

/**
 * SCC
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = (0 === ($this->iConditionRegister & IRegister::CCR_CARRY)) ? 0xFF : 0;
<?php
    require $oParams->sBasePath . '/operation/fragments/set_conditional.tpl.php';
?>
};
