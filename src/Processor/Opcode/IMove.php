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

    const OP_MOVE_SRC_EA_SHIFT = 6;
}
