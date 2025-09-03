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
interface ILogical
{
    const OP_ORI_CCR  = 0b0000000000111100;
    const OP_ORI_SR   = 0b0000000001111100;
    const OP_ANDI_CCR = 0b0000001000111100;
    const OP_ANDI_SR  = 0b0000001001111100;
    const OP_EORI_CCR = 0b0000101000111100;
    const OP_EORI_SR  = 0b0000101001111100;

    //                            ssEAEAEA
    const OP_ORI_B    = 0b0000000000000000;
    const OP_ORI_W    = 0b0000000001000000;
    const OP_ORI_L    = 0b0000000010000000;
    const OP_ANDI_B   = 0b0000001000000000;
    const OP_ANDI_W   = 0b0000001001000000;
    const OP_ANDI_L   = 0b0000001010000000;
    const OP_EORI_B   = 0b0000101000000000;
    const OP_EORI_W   = 0b0000101001000000;
    const OP_EORI_L   = 0b0000101010000000;

    //                         rrrmssEAEAEA - r(eg) m(mode) s(ize)
    const OP_OR_DIR    = 0b0000000100000000;
    const OP_OR_EA2D_B = 0b1000000000000000;
    const OP_OR_EA2D_W = 0b1000000001000000;
    const OP_OR_EA2D_L = 0b1000000010000000;
    const OP_OR_D2EA_B = 0b1000000100000000;
    const OP_OR_D2EA_W = 0b1000000101000000;
    const OP_OR_D2EA_L = 0b1000000110000000;

    //                          rrrmssEAEAEA - r(eg) m(mode) s(ize)
    const OP_AND_DIR    = 0b0000000100000000;
    const OP_AND_EA2D_B = 0b1100000000000000;
    const OP_AND_EA2D_W = 0b1100000001000000;
    const OP_AND_EA2D_L = 0b1100000010000000;
    const OP_AND_D2EA_B = 0b1100000100000000;
    const OP_AND_D2EA_W = 0b1100000101000000;
    const OP_AND_D2EA_L = 0b1100000110000000;

    //                              ssEAEAEA
    const OP_NOT_B      = 0b0100011000000000;
    const OP_NOT_W      = 0b0100011001000000;
    const OP_NOT_L      = 0b0100011010000000;

}
