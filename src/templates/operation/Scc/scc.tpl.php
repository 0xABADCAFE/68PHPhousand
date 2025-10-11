<?php

/**
 * SCC
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = (0 === ($this->iConditionRegister & IRegister::CCR_CARRY)) ? 0xFF : 0;
    $this->aDstEAModes[$iOpcode & 63]->resetLatch()->writeByte($iState);
};
