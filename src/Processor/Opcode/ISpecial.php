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
 * Opode prefixes for flow instructions
 */
interface ISpecial
{
    const OP_ILLEGAL  = 0b0100101011111100;
    const OP_RESET    = 0b0100111001110000;
    const OP_NOP      = 0b0100111001110001;
    const OP_STOP     = 0b0100111001110010;
    const OP_RTE      = 0b0100111001110011;
    const OP_RTS      = 0b0100111001110101;

    //                                vvvv - trap #<0-15>
    const OP_TRAP     = 0b0100111001000000;

    const OP_TRAPV    = 0b0100111001110110;
    const OP_RTR      = 0b0100111001110111;

    //                              EAEAEA
    const OP_TAS      = 0b0100101011000000;

    //                                 AAA
    const OP_LINK     = 0b0100111001010000;
    //                                 AAA
    const OP_UNLK     = 0b0100111001011000;

    // TODO - probably need to be in a different location
    const MASK_TRAP_NUM = 0xF; // 0-15
    const TRAP_USER_OFS = 32;  // 32-47
}
