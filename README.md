```
    _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
  _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
_/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 _/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/

>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
```
# 68PHPhousand

The world's least sensible 68000 emulator.

## Work In Progress

Follows a similar design principle to [SixPHPhive02](https://github.com/0xABADCAFE/sixphphive02/tree/main) with a decoupled memory / CPU model.

The implementation makes heavy use of `assert()` which is required for the basic tests to function. Assertions should be disabled for performance when running code normally.

# Interfaces

Under the root namespace, `ABadCafe\G8PHPhousand\` the following interfaces exist.

## IDevice

Principle interface for any device (CPU or something attached) that can be soft or hard reset.

## Device\IReadable

Defines an entity that can be read from as byte, word (16-bit) or long (32-bit). All values read are considered to be raw/unsigned.

## Device\IWriteable

Defines an entity that can be written to as byte, word (16-bit) or long (32-bit). All values written are considered to be raw/unsigned.

## Device\IBus

Union inteface of IDevice, Device\IReadable and Device\IWriteable

## Processor\IRegister

Enumerates general purpose (data/address) registers.

## Processor\IConditionCode

Enumerates the 16 standard condition codes used by branching/conditional set.

## Processor\IOpcode

Various masks and other enumerated values needed for decoding instruction words.

## Processor\EAMode\IReadOnly

Read-only interface for an Effective Address target, such as a specific register or memory location.

## Processor\EAMode\IReadWrite

Writeable extension of Processor\EATarget\IReadOnly


# Classes/Traits

## Device\Memory

Implementation of `Device\IBus` that manages a block of memory of a given length, with some given start address. The length and start addresses must be divisible by 4. Memory is managed as a binary string with big-endian semantics for word/long accesses. There are no alignment assertions for the memory (these may be made by the CPU), but addresses are asserted to be within bounds and the values written are asserted to be within the unsigned limits implied by the access size.

## Processor\Base

Abstract base implementation of the CPU, defining the main state and the basic IDevice requirements.

## Processor\RegisterSet

Simple structure type that manages a set of 8 explicit integer values and an index array to allow them to be selected by register number and various masked values from within opcodes.

## Processor\TRegisterUnit

Implementation logic for maintaining the data RegisterSet, address RegisterSet, Program Counter, Condition Code and Status Registers.

## Processor\TAddressUnit

Implementation logic for addressing modes.

## Processor\EAMode\Direct\*

Implementations of addressing modes that directly access register values or immediates:

- DataRegister `dN`
- AddressRegister `aN`
- Immediate `#N`

## Processor\EAMode\Indirect\*

- Basic `(aN)`
- Displacement `d16(aN)`
- PostIncrement `(aN)+`
- PreDecrement `-(aN)`
- Indexed `d8(xN.w|.l, aN)`

Implementations of addressing modes that use indirection to access values in memory.

## Processor\EATarget\Bus

Implementation of Processor\EATarget\IReadWrite that delegates to Device\IBus which is intended for all Effective Address targets that route to external memory.

