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
        !($this->iConditionRegister & IRegister::CCR_ZERO)
    ) {
<?php
    require $oParams->sBasePath . '/operation/fragments/dbra_conditional.tpl.php';
?>
    }
};

