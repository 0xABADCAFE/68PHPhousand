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
    //                      NNNmmmEAEAEA - cmp.b/w/l <ea>,dN - all src EA legal
    const OP_CMP_B  = 0b1011000000000000;
    const OP_CMP_W  = 0b1011000001000000;
    const OP_CMP_L  = 0b1011000010000000;

    //                      NNNmmmEAEAEA - cmpa.w/l <ea>,aN - all src EA legal
    const OP_CMPA_W = 0b1011000011000000;
    const OP_CMPA_L = 0b1011000111000000;

    //                          ssEAEAEA - cmpi.b/w/l #N,<ea> - data addressable modes
    const OP_CMPI_B = 0b0000110000000000;
    const OP_CMPI_W = 0b0000110001000000;
    const OP_CMPI_L = 0b0000110010000000;

    //                      xxxxss   yyy - cmpm.b/w/l (Ax)+,(Ay)+
    const OP_CMPM_B = 0b1011000100001000;
    const OP_CMPM_W = 0b1011000101001000;
    const OP_CMPM_L = 0b1011000110001000;



    //                            ssEAEAEA
    const OP_NEG_B    = 0b0100010000000000;
    const OP_NEG_W    = 0b0100010001000000;
    const OP_NEG_L    = 0b0100010010000000;


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

    //                      ----ddd-ssEAEAEA
    const OP_SUBQ_L     = 0b0101000110000000;

    //                          dddmmmEAEAEA
    const OP_ADD_EA2D_B = 0b1101000000000000;
    const OP_ADD_EA2D_W = 0b1101000001000000;
    const OP_ADD_EA2D_L = 0b1101000010000000;

    //                          dddmmmEAEAEA
    const OP_ADD_EA2A_W = 0b1101000011000000;
    const OP_ADD_EA2A_L = 0b1101000111000000;

    //                          dddmmmEAEAEA
    const OP_ADD_D2EA_B = 0b1101000100000000;
    const OP_ADD_D2EA_W = 0b1101000101000000;
    const OP_ADD_D2EA_L = 0b1101000110000000;

    //                          dddmmmEAEAEA
    const OP_SUB_EA2D_B = 0b1001000000000000;
    const OP_SUB_EA2D_W = 0b1001000001000000;
    const OP_SUB_EA2D_L = 0b1001000010000000;

    //                          dddmmmEAEAEA
    const OP_SUB_EA2A_W = 0b1001000011000000;
    const OP_SUB_EA2A_L = 0b1001000111000000;

    //                          dddmmmEAEAEA
    const OP_SUB_D2EA_B = 0b1001000100000000;
    const OP_SUB_D2EA_W = 0b1001000101000000;
    const OP_SUB_D2EA_L = 0b1001000110000000;

    //                          rrr111EAEAEA
    const OP_MULS_W     = 0b1100000111000000;

    //                          rrr011EAEAEA
    const OP_MULU_W     = 0b1100000011000000;
}
