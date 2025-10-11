<?php

/**
 * ST
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $this->aDstEAModes[$iOpcode & 63]->resetLatch()->writeByte(0xFF);
};
