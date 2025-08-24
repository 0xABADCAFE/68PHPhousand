<?php
    /**
     * Common body templte for Scc instruction templates.
     */

    // This implementation could change, e.g. using a statically evaluated
    // EA. This is considered ultra MVP.
?>
    $this->aDstEAModes[$iOpcode & 63]->writeByte($iState);
