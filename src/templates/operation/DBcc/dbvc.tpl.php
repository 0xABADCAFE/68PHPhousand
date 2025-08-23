<?php

/**
 * DBVC
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    if (
        !($this->iConditionRegister & IRegister::CCR_OVERFLOW)
    ) {
<?php
    require $oParams->sBasePath . '/operation/fragments/dbra_conditional.tpl.php';
?>
    }
};

