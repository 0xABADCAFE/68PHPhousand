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

use RuntimeException;
use function \spl_autoload_register;

if (PHP_VERSION_ID < 70400) {
	throw new RuntimeException('Requires at least PHP 7.4');
}

const CLASS_MAP = [
  'ABadCafe\\G8PHPhousand\\IDevice'            => '/IDevice.php',
  'ABadCafe\\G8PHPhousand\\Device\\IReadable'  => '/Device/IReadable.php',
  'ABadCafe\\G8PHPhousand\\Device\\IWriteable' => '/Device/IWriteable.php',
  'ABadCafe\\G8PHPhousand\\Device\\IBus'       => '/Device/IBus.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory'     => '/Device/Memory.php',
  'ABadCafe\\G8PHPhousand\\Device\\IByteConv'  => '/Device/IByteConv.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcode' => '/Processor/IOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Base'    => '/Processor/Base.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TRegisterUnit'    => '/Processor/TRegisterUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TAddressUnit'    => '/Processor/TAddressUnit.php',
];

spl_autoload_register(function(string $str_class): void {
    if (isset(CLASS_MAP[$str_class])) {
        require_once __DIR__ . CLASS_MAP[$str_class];
    }
});
