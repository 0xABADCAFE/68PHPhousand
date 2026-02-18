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

namespace ABadCafe\G8PHPhousand;

use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\TestHarness;
use Throwable;

require  __DIR__ . '/../src/bootstrap.php';

$oDeviceMap = new Device\PagedMap(8); // 256 bytes

$oSerialConsoleOutput = new Device\SerialConsoleOutput(0xFF0000);

$oDeviceMap->add($oSerialConsoleOutput);

$sMessage = "Hello World\n";

$i = 0;

while (isset($sMessage[$i])) {
    $oDeviceMap->writeByte(0xFF0000, ord($sMessage[$i++]));
}
