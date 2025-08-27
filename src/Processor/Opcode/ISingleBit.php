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

use ABadCafe\G8PHPhousand\Processor\IOpcode;


/**
 * Opode prefixes for conditional instructions
 */
interface ISingleBit
{
    const OP_BIT_DYN = 0b0000000100000000;
    //                   ----rrrdxxEAEAEA
    const OP_BTST_DN = 0b0000000100000000;
    const OP_BTST_I  = 0b0000100000000000; // immediate

    //                   ----rrrdxxEAEAEA
    const OP_BCHG_DN = 0b0000000101000000;
    const OP_BCHG_I  = 0b0000100001000000; // immediate

    //                   ----rrrdxxEAEAEA
    const OP_BCLR_DN = 0b0000000110000000;
    const OP_BCLR_I  = 0b0000100010000000; // immediate

    //                   ----rrrdxxEAEAEA
    const OP_BSET_DN = 0b0000000111000000;
    const OP_BSET_I  = 0b0000100011000000; // immediate



}
