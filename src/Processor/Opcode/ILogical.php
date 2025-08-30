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

}
