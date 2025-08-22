<?php

/**
 * ST
 *
 */

assert(!empty($oParams), new \LogicException());

?>
return function(int $iOpcode): void {
    $iState = 0xFF;
<?php
    require $oParams->sBasePath . '/operation/fragments/set_conditional.tpl.php';
?>
};
