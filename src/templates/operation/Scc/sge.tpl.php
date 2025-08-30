<?php

/**
 * SGE
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iCCR = $this->iConditionRegister & IRegister::CCR_MASK_NV;
    $iState = (0 === $iCCR || IRegister::CCR_MASK_NV === $iCCR) ? 0xFF : 0;
<?php
    require 'common.tpl.php';
?>
};
