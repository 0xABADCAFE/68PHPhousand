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

namespace ABadCafe\G8PHPhousand\Processor\Opcode;

/**
 * IBounds
 *
 * Opcodes for 68020+ bounds checking instructions.
 *
 * CHK2 - Check Register Against Bounds (exception if out of bounds)
 * CMP2 - Compare Register Against Bounds (set condition codes only)
 */
interface IBounds
{
    // CHK2/CMP2 base opcode: 0000 0000 11xx xxxx
    // Extension word specifies:
    //   - Register to check (D0-D7, A0-A7)
    //   - Size (.B, .W, .L)
    //   - CHK2 vs CMP2 (bit 11 of extension word)
    //
    // Format: CHK2.size <ea>,Rn  or  CMP2.size <ea>,Rn
    // <ea> points to two consecutive values in memory (lower bound, upper bound)

    const OP_CHK2_CMP2 = 0b0000000011000000; // CHK2/CMP2.B/W/L <ea>,Rn (uses extension word)
}
