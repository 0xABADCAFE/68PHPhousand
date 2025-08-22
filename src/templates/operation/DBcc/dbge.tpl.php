<?php

/**
 * DBGE
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    if (0 === $iCCR || IRegister::CCR_MASK_NV === $iCCR) {
<?php
    require $oParams->sBasePath . '/operation/fragments/dbra_conditional.tpl.php';
?>
    }
};

