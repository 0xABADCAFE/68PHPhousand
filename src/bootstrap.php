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
  'ABadCafe\\G8PHPhousand\\Device\\NullDevice' => '/Device/NullDevice.php',
  'ABadCafe\\G8PHPhousand\\Device\\IMemory' => '/Device/IMemory.php',
  'ABadCafe\\G8PHPhousand\\Device\\IWriteable' => '/Device/IWriteable.php',
  'ABadCafe\\G8PHPhousand\\Device\\IBus' => '/Device/IBus.php',
  'ABadCafe\\G8PHPhousand\\Device\\IByteConv' => '/Device/IByteConv.php',
  'ABadCafe\\G8PHPhousand\\Device\\IReadable' => '/Device/IReadable.php',
  'ABadCafe\\G8PHPhousand\\Device\\PageMap' => '/Device/PageMap.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\BinaryRAM' => '/Device/Memory/BinaryRAM.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\SparseWordRAM' => '/Device/Memory/SparseWordRAM.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\SparseRAM' => '/Device/Memory/SparseRAM.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\SparseRAM24' => '/Device/Memory/SparseRAM24.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\IDiagnostic' => '/Device/Memory/IDiagnostic.php',
  'ABadCafe\\G8PHPhousand\\Device\\Memory\\CodeROM' => '/Device/Memory/CodeROM.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\CPU' => '/TestHarness/CPU.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\TomHarte' => '/TestHarness/TomHarte.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\ObjectCode' => '/TestHarness/ObjectCode.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\IAssembler' => '/TestHarness/IAssembler.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\Memory' => '/TestHarness/Memory.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\CachedCPU' => '/TestHarness/CachedCPU.php',
  'ABadCafe\\G8PHPhousand\\TestHarness\\Assembler\\Vasmm68k' => '/TestHarness/Assembler/Vasmm68k.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcode' => '/Processor/IOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TCache' => '/Processor/TCache.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IOpcodeMSB' => '/Processor/IOpcodeMSB.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IRegister' => '/Processor/IRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TAddressUnit' => '/Processor/TAddressUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Sign' => '/Processor/Sign.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IEffectiveAddress' => '/Processor/IEffectiveAddress.php',
  'ABadCafe\\G8PHPhousand\\Processor\\RegisterSet' => '/Processor/RegisterSet.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TOpcode' => '/Processor/TOpcode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\DataRegisterSet' => '/Processor/DataRegisterSet.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Base' => '/Processor/Base.php',
  'ABadCafe\\G8PHPhousand\\Processor\\IConditionCode' => '/Processor/IConditionCode.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TArithmeticLogicUnit' => '/Processor/TArithmeticLogicUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\TRegisterUnit' => '/Processor/TRegisterUnit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\ISize' => '/Processor/ISize.php',
  'ABadCafe\\G8PHPhousand\\Processor\\AddressRegisterSet' => '/Processor/AddressRegisterSet.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithExtensionWords' => '/Processor/EAMode/TWithExtensionWords.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithoutLatch' => '/Processor/EAMode/TWithoutLatch.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadOnly' => '/Processor/EAMode/IReadOnly.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IReadWrite' => '/Processor/EAMode/IReadWrite.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithBusAccess' => '/Processor/EAMode/TWithBusAccess.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\IIndirect' => '/Processor/EAMode/IIndirect.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\TWithLatch' => '/Processor/EAMode/TWithLatch.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Indexed' => '/Processor/EAMode/Indirect/Indexed.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PostIncrementSP' => '/Processor/EAMode/Indirect/PostIncrementSP.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Illegal' => '/Processor/EAMode/Indirect/Illegal.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Basic' => '/Processor/EAMode/Indirect/Basic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PreDecrement' => '/Processor/EAMode/Indirect/PreDecrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PostIncrement' => '/Processor/EAMode/Indirect/PostIncrement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PreDecrementSP' => '/Processor/EAMode/Indirect/PreDecrementSP.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PCDisplacement' => '/Processor/EAMode/Indirect/PCDisplacement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\PCIndexed' => '/Processor/EAMode/Indirect/PCIndexed.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\Displacement' => '/Processor/EAMode/Indirect/Displacement.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\AbsoluteShort' => '/Processor/EAMode/Indirect/AbsoluteShort.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Indirect\\AbsoluteLong' => '/Processor/EAMode/Indirect/AbsoluteLong.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Register' => '/Processor/EAMode/Direct/Register.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\DataRegister' => '/Processor/EAMode/Direct/DataRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\Immediate' => '/Processor/EAMode/Direct/Immediate.php',
  'ABadCafe\\G8PHPhousand\\Processor\\EAMode\\Direct\\AddressRegister' => '/Processor/EAMode/Direct/AddressRegister.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IMove' => '/Processor/Opcode/IMove.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TSingleBit' => '/Processor/Opcode/TSingleBit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TArithmetic' => '/Processor/Opcode/TArithmetic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IArithmetic' => '/Processor/Opcode/IArithmetic.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IFlow' => '/Processor/Opcode/IFlow.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\ILogical' => '/Processor/Opcode/ILogical.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TMove' => '/Processor/Opcode/TMove.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IShifter' => '/Processor/Opcode/IShifter.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\ISingleBit' => '/Processor/Opcode/ISingleBit.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\IPrefix' => '/Processor/Opcode/IPrefix.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TFlow' => '/Processor/Opcode/TFlow.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TShifter' => '/Processor/Opcode/TShifter.php',
  'ABadCafe\\G8PHPhousand\\Processor\\Opcode\\TLogical' => '/Processor/Opcode/TLogical.php',
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
