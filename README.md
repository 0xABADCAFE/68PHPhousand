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

Work in progress. Follows a similar design principle to [SixPHPhive02](https://github.com/0xABADCAFE/sixphphive02/tree/main) with a decoupled memory / CPU model.

# Interfaces

## ABadCafe\G8PHPhousand\IDevice

Principle interface for any device (CPU or something attached) that can be soft or hard reset.

## ABadCafe\G8PHPhousand\Device\IReadable

Defines an entity that can be read from as byte, word (16-bit) or long (32-bit). All values read are considered to be raw/unsigned.

## ABadCafe\G8PHPhousand\Device\IRWriteable

Defines an entity that can be written to as byte, word (16-bit) or long (32-bit). All values written are considered to be raw/unsigned.

