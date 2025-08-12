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
use \RuntimeException;
use function \spl_autoload_register;

if (PHP_VERSION_ID < 70400) {
    throw new RuntimeException('Requires at least PHP 7.4');
}

const CLASS_MAP = [
  'ABadCafe\\G8PHPhousand\\I68KProcessor' => '/I68KProcessor.php',
  'ABadCafe\\G8PHPhousand\\IDevice' => '/IDevice.php',
  'ABadCafe\\G8PHPhousand\\Device\\IWriteable' => '/Device/IWriteable.php',
  'ABadCafe\\G8PHPhousand\\Device\\IBus' => '/Device/IBus.php',
  'ABadCafe\\G8PHPhousand\\Device\\IByteConv' => '/Device/IByteConv.php',
  'ABadCafe\\G8PHPhousand\\Device\\IReadable' => '/Device/IReadable.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory' => '/Device/Memory.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcode' => '/Processor/IOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcodeMSB' => '/Processor/IOpcodeMSB.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IRegister' => '/Processor/IRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TAddressUnit' => '/Processor/TAddressUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Sign' => '/Processor/Sign.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IEffectiveAddress' => '/Processor/IEffectiveAddress.php',
  'ABadCafe\\G8PHPhousand\\Processor\\RegisterSet' => '/Processor/RegisterSet.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Base' => '/Processor/Base.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IConditionCode' => '/Processor/IConditionCode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TRegisterUnit' => '/Processor/TRegisterUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\ISize' => '/Processor/ISize.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithExtensionWords' => '/Processor/EAMode/TWithExtensionWords.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadOnly' => '/Processor/EAMode/IReadOnly.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadWrite' => '/Processor/EAMode/IReadWrite.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithBusAccess' => '/Processor/EAMode/TWithBusAccess.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Indexed' => '/Processor/EAMode/Indirect/Indexed.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Basic' => '/Processor/EAMode/Indirect/Basic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PreDecrement' => '/Processor/EAMode/Indirect/PreDecrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PostIncrement' => '/Processor/EAMode/Indirect/PostIncrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Displacement' => '/Processor/EAMode/Indirect/Displacement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Register' => '/Processor/EAMode/Direct/Register.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\DataRegister' => '/Processor/EAMode/Direct/DataRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Immediate' => '/Processor/EAMode/Direct/Immediate.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\AddressRegister' => '/Processor/EAMode/Direct/AddressRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IPrefix' => '/Processor/Opcode/IPrefix.php',
];

spl_autoload_register(function(string $str_class): void {
    if (isset(CLASS_MAP[$str_class])) {
        require_once __DIR__ . CLASS_MAP[$str_class];
    }
});
