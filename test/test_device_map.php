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

$oDeviceMap = new Device\SimpleDeviceMap(8); // 256 bytes

$oMemory = new Device\Memory\SparseWordRAM(512);

$oDeviceMap->map($oMemory, 256, $oMemory->getLength());

for ($iAddress = 0; $iAddress<1024; ++$iAddress) {
    printf("Attempting readByte(0x%08X): ", $iAddress);
    try {
        $oDeviceMap->readByte($iAddress);
        print("OK\n");
    } catch (Processor\Fault\Access $oError) {
        printf("Access fault\n");
    }
}
