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
  'ABadCafe\\G8PHPhousand\\Device\\SparseRAM' => '/Device/SparseRAM.php',
  'ABadCafe\\G8PHPhousand\\Device\\IReadable' => '/Device/IReadable.php',
  'ABadCafe\\G8PHPhousand\\Device\\CodeROM' => '/Device/CodeROM.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory' => '/Device/Memory.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcode' => '/Processor/IOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcodeMSB' => '/Processor/IOpcodeMSB.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IRegister' => '/Processor/IRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TAddressUnit' => '/Processor/TAddressUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Sign' => '/Processor/Sign.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IEffectiveAddress' => '/Processor/IEffectiveAddress.php',
  'ABadCafe\\G8PHPhousand\\Processor\\RegisterSet' => '/Processor/RegisterSet.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TOpcode' => '/Processor/TOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Base' => '/Processor/Base.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IConditionCode' => '/Processor/IConditionCode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TArithmeticLogicUnit' => '/Processor/TArithmeticLogicUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TRegisterUnit' => '/Processor/TRegisterUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\ISize' => '/Processor/ISize.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithExtensionWords' => '/Processor/EAMode/TWithExtensionWords.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithoutLatch' => '/Processor/EAMode/TWithoutLatch.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadOnly' => '/Processor/EAMode/IReadOnly.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadWrite' => '/Processor/EAMode/IReadWrite.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithBusAccess' => '/Processor/EAMode/TWithBusAccess.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithLatch' => '/Processor/EAMode/TWithLatch.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Indexed' => '/Processor/EAMode/Indirect/Indexed.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Basic' => '/Processor/EAMode/Indirect/Basic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PreDecrement' => '/Processor/EAMode/Indirect/PreDecrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PostIncrement' => '/Processor/EAMode/Indirect/PostIncrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Displacement' => '/Processor/EAMode/Indirect/Displacement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Register' => '/Processor/EAMode/Direct/Register.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\DataRegister' => '/Processor/EAMode/Direct/DataRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Immediate' => '/Processor/EAMode/Direct/Immediate.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\AddressRegister' => '/Processor/EAMode/Direct/AddressRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IMove' => '/Processor/Opcode/IMove.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TSingleBit' => '/Processor/Opcode/TSingleBit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TArithmetic' => '/Processor/Opcode/TArithmetic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IFlow' => '/Processor/Opcode/IFlow.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\ILogical' => '/Processor/Opcode/ILogical.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TMove' => '/Processor/Opcode/TMove.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\ISingleBit' => '/Processor/Opcode/ISingleBit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IPrefix' => '/Processor/Opcode/IPrefix.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TFlow' => '/Processor/Opcode/TFlow.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TLogical' => '/Processor/Opcode/TLogical.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TConditional' => '/Processor/Opcode/TConditional.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IConditional' => '/Processor/Opcode/IConditional.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TSpecial' => '/Processor/Opcode/TSpecial.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\Template\\Params' => '/Processor/Opcode/Template/Params.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\Template\\TGenerator' => '/Processor/Opcode/Template/TGenerator.php',
];

const PROJ_SRC_BASE = __DIR__;

spl_autoload_register(function(string $str_class): void {
    if (isset(CLASS_MAP[$str_class])) {
        require_once PROJ_SRC_BASE . CLASS_MAP[$str_class];
    }
});
