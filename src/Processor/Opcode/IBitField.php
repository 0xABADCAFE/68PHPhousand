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
 * IBitField
 *
 * Opcodes for 68020+ bit field instructions.
 *
 * Bit field instructions operate on arbitrary bit sequences (1-32 bits) in memory
 * or data registers. The bit field is specified by an offset and width in the
 * extension word.
 *
 * Extension word format:
 * - Bits 15-12: Register for BFINS/BFEXTS/BFEXTU (Dn)
 * - Bit 11: Offset field (0=immediate, 1=data register)
 * - Bits 10-6: Offset value or register number
 * - Bit 5: Width field (0=immediate, 1=data register)
 * - Bits 4-0: Width value or register number
 */
interface IBitField
{
    // Base opcodes: 1110 xxxx 11xx xxxx
    // All use extension word for bit field specification

    const OP_BFTST  = 0b1110100011000000; // BFTST <ea>{offset:width} - Test bit field
    const OP_BFEXTU = 0b1110100111000000; // BFEXTU <ea>{offset:width},Dn - Extract unsigned
    const OP_BFEXTS = 0b1110101111000000; // BFEXTS <ea>{offset:width},Dn - Extract signed
    const OP_BFCLR  = 0b1110110011000000; // BFCLR <ea>{offset:width} - Clear bits
    const OP_BFFFO  = 0b1110110111000000; // BFFFO <ea>{offset:width},Dn - Find first one
    const OP_BFSET  = 0b1110111011000000; // BFSET <ea>{offset:width} - Set bits
    const OP_BFINS  = 0b1110111111000000; // BFINS Dn,<ea>{offset:width} - Insert bits
    const OP_BFCHG  = 0b1110101011000000; // BFCHG <ea>{offset:width} - Change (toggle) bits
}
