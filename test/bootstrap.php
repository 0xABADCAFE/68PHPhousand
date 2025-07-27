<?php

/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Test;

if (1 !== (int)ini_get('zend.assertions')) {
    die('Assertions must be enabled for tests to run\n');
}

use Throwable;
use LogicException;

error_reporting(-1);
require  __DIR__ . '/../src/bootstrap.php';

function assertThrown(string $sCase, callable $cCall, string $sErrorClass): void {
    $oThrownError = null;
    try {
        $cCall();
    } catch (Throwable $oError) {
        $oThrownError = $oError;
    }
    assert(
        $oThrownError instanceof $sErrorClass,
        new LogicException($sCase)
    );
}

