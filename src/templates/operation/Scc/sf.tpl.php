<?php

/**
 * SF
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = 0;
<?php
    include '../fragments/set_conditional.tpl.php';
?>
};
