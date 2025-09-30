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
interface IMove
{
    //                         ssEAEAEA
    const OP_CLR_B  = 0b0100001000000000;
    const OP_CLR_W  = 0b0100001001000000;
    const OP_CLR_L  = 0b0100001010000000;

    //                    ssEAEAEAEAEAEA - EA Destination, EA Source
    const OP_MOVE_B = 0b0001000000000000;
    const OP_MOVE_W = 0b0011000000000000;
    const OP_MOVE_L = 0b0010000000000000;

    //                    ssrrr   EAEAEA
    const OP_MOVEA  = 0b0000000001000000;

    //                      rrr nnnnnnnn
    const OP_MOVEQ  = 0b0111000000000000;

    //                               rrr
    const OP_SWAP   = 0b0100100001000000;

    //                      xxx1mmmmmyyy ; EXG Rx,Ry
    const OP_EXG_DD = 0b1100000101000000; // exg dx,dy
    const OP_EXG_AA = 0b1100000101001000; // exg ax,ay
    const OP_EXG_DA = 0b1100000100001000; // exg dx,ay

    const MASK_DST_EA = 0b0000111111000000;

    const OP_MOVE_SRC_EA_SHIFT = 6;


}
