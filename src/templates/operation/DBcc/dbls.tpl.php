<?php

/**
 * DBLS
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    if (
        $this->iConditionRegister & IRegister::CCR_MASK_ZC
    ) {
<?php
    require $oParams->sBasePath . '/operation/fragments/dbra_conditional.tpl.php';
?>
    }
};

