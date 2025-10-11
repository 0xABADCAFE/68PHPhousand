<?php

/**
 * SEQ
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = ($this->iConditionRegister & IRegister::CCR_ZERO) ? 0xFF : 0;
    $this->aDstEAModes[$iOpcode & 63]->resetLatch()->writeByte($iState);
};
