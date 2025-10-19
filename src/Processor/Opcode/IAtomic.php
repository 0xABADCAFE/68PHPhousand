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
 * Opcode prefixes for atomic instructions (68020+)
 */
interface IAtomic
{
    // CAS - Compare and Swap (68020+)
    //                    00001ss011EAEAEA - cas.b/w/l Dc,Du,<ea>
    const OP_CAS_B    = 0b0000101011000000;
    const OP_CAS_W    = 0b0000110011000000;
    const OP_CAS_L    = 0b0000111011000000;

    // CAS2 - Compare and Swap 2 (68020+)
    //                    00001ss011111100 - cas2.w/l Dc1:Dc2,Du1:Du2,(Rn1):(Rn2)
    const OP_CAS2_W   = 0b0000110011111100;
    const OP_CAS2_L   = 0b0000111011111100;
}
