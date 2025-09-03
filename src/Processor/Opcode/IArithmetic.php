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
interface IArithmetic
{
    //                    --------ssEAEAEA
    const OP_SUBI_B   = 0b0000010000000000;
    const OP_SUBI_W   = 0b0000010001000000;
    const OP_SUBI_L   = 0b0000010010000000;

    //                    --------ssEAEAEA
    const OP_ADDI_B   = 0b0000011000000000;
    const OP_ADDI_W   = 0b0000011001000000;
    const OP_ADDI_L   = 0b0000011010000000;

    //                    ----ddd-ssEAEAEA
    const OP_ADDQ_B   = 0b0101000000000000;

    //                    ----ddd-ssEAEAEA
    const OP_ADDQ_W   = 0b0101000001000000;

    //                    ----ddd-ssEAEAEA
    const OP_ADDQ_L   = 0b0101000010000000;

    //                    ----ddd-ssEAEAEA
    const OP_SUBQ_B   = 0b0101000100000000;

    //                    ----ddd-ssEAEAEA
    const OP_SUBQ_W   = 0b0101000101000000;

    //                    ----ddd-ssEAEAEA
    const OP_SUBQ_L   = 0b0101000110000000;
}
