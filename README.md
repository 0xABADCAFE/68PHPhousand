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

## Architecture

The design separates the CPU from the outside world and uses dependency injection to attach external devices to the CPU. Inside the CPU, abstractions are minimal and functional e.g.

- Register sets and basic state.
- Addressing mode calculators.

An internal array maps 68000 opcode words to specific closure handelers that execute the operation required. These handlers are either:

- Defined directly.
- Metaprogrammed using template code that is evaluated when the CPU is instantiated.

The CPU class is rather monolithic and quite coupled internally for performance reasons. To make the code more manageable, logical areas of concern are separated out into traits that are composed together.

## Intentional Differences from 68000

By design, there are some differences to the 68000 the emulation core does not specifically handle. The following divergences affect both user and supervisor mode:

- 24-bit addressing:

    - All 32 bits of the address are exposed to the IBus implementation.
    - An IBus Address24Bit adapter is provided that masks off the upper 8 bits of an address to better simulate the original addressing behaviour.

- Bus width:
    - The original 68000 transfers 16-bit word at a time. For predecrement and postincrement addressing modes, this means that should an address or access fault occur for a long sized access, a given register may only have been half updated, e.g. adjusted by 2 and not 4.
    - IBus accesses will always transfer a full byte, word or long and any registers affected by predecrement/postincrement updated accordingly.

- Misaligned accesses:

    - The original 68000 is incapable of accessing a word or long on an odd address boundary, triggering an access fault.
    - This will not happen by default. An IBus WordAligned adapter is provided that will trigger an access fault for misalgned word/long accesses.

- Branch displacements:
    - Branch displacements recognise 0xFF in the short displacement field as indicating that a 32-bit long branch displacement follows, as per the 68020+

Using the WordAligned and Address24Bit adapters together more faithfully recreate the access fault behaviour of the original 68000 at the cost of making memory accesses slower.

Without these adapters, the normal user mode operation is more akin to a 68020 running 68000 object code.

## Intentional Supervisor mode differences from the 68000

The following supervisor mode differces are intentional:

- Vector Base Register:
    - Initially set to 0, this allows the relocation of the vector base for exception handling.
    - The MOVEC instruction is implemented to facilitate management of the VBR. Also supports the USP.


# Work In Progress

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

Union inteface of IDevice, Device\IReadable and Device\IWriteable. Most devices will implement this interface.

## Device\IMemory

IBus extension for a potentially relocatable block of memory.

## Device\NullDevice

IBus implementation that ignores all writes and returns zero for all reads.

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

## Device\Memory\BinaryRAM

Implementation of `Device\IMemory` that manages a block of memory of a given length, with some given start address. The length and start addresses must be divisible by 4. Memory is managed as a binary string with big-endian semantics for word/long accesses. There are no alignment assertions for the memory (these may be made by the CPU), but addresses are asserted to be within bounds and the values written are asserted to be within the unsigned limits implied by the access size.

## Device\Memory\SparseRAM

Implementation of `Device\IMemory` that treats the memory as an associative array of address/value bytes. This covers the full address range.

## Device\Memory\SparseWordRAM

Variant of SparseRAM that models the memory as 16-bit words. This is intended to have the fastest read performance for basic code.

## Device\Memory\CodeROM

IMemory implementation as a sparse word-based ROM that accepts a raw binary image to load.

## Processor\Base

Abstract base implementation of the CPU, defining the main state and the basic IDevice requirements.

## Processor\RegisterSet

Simple structure type that manages a set of 8 explicit integer values and an index array to allow them to be selected by register number and various masked values from within opcodes.

## Processor\DataRegisterSet

Concretisation of RegisterSet specifically for the Data Registers. This assists with type safety to ensure the correct register sets are used by addressing mode implementations.

## Processor\AddressRegisterSet

Concretisation of RegisterSet specifically for the Address Registers. This assists with type safety to ensure the correct register sets are used by addressing mode implementations.

## Processor\TRegisterUnit

Implementation logic for maintaining the DataRegisterSet, AddressRegisterSet, Program Counter, Condition Code and Status Registers.

## Processor\TAddressUnit

Implementation logic for addressing modes. Manages the set of EAModes that are available.

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
- Indexed `d8(aN,xN.w|.l)`

Implementations of addressing modes that use indirection to access values in memory. A specific concretisation exists for PostIncrement and PreDecrement for the A7 register, which maintains word alignment.

## Processor\EAMode\TWithBusAccess

Trait that provides external memory access to addressing mode calculations.

## Processor\EAMode\TWithExtensionWords

Trait that provides the necessary mechanisms to fetch extension words used for addressing modes.

## Processor\EAMode\TWithLatch

Trait that provides latching of the effective address calculated for read, to be reused on write. For destination effective address modes, an operand effective address is generated on access and reused on write.

## Processor\EAMode\TWithoutLatch

Counterpart to TWithLatch that provides a no-op implementation of resetLatch() so that EAModes can share a common interface where there are specific behavioural exceptions.
