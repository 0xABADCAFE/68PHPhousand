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

    //                            ssEAEAEA
    const OP_NEGX_B   = 0b0100000000000000;
    const OP_NEGX_W   = 0b0100000001000000;
    const OP_NEGX_L   = 0b0100000010000000;


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
    const OP_ADD_EA2D_B  = 0b1101000000000000;
    const OP_ADD_EA2D_W  = 0b1101000001000000;
    const OP_ADD_EA2D_L  = 0b1101000010000000;

    //                          dddmmmEAEAEA
    const OP_ADD_EA2A_W  = 0b1101000011000000;
    const OP_ADD_EA2A_L  = 0b1101000111000000;

    //                          dddmmmEAEAEA
    const OP_ADD_D2EA_B  = 0b1101000100000000;
    const OP_ADD_D2EA_W  = 0b1101000101000000;
    const OP_ADD_D2EA_L  = 0b1101000110000000;

    //                           xxx ss  myyy
    const OP_ADDX_DyDx_B = 0b1101000100000000; // addx.bwl Dy,Dx
    const OP_ADDX_DyDx_W = 0b1101000101000000; // addx.bwl Dy,Dx
    const OP_ADDX_DyDx_L = 0b1101000110000000; // addx.bwl Dy,Dx

    const OP_ADDX_AyAx_B = 0b1101000100001000; // addx.bwl -(Ay),-(Ax)
    const OP_ADDX_AyAx_W = 0b1101000101001000; // addx.bwl -(Ay),-(Ax)
    const OP_ADDX_AyAx_L = 0b1101000110001000; // addx.bwl -(Ay),-(Ax)


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

    // Note the reverse ordering of x and y as per the M68000PRM docs
    //                           yyy ss  mxxx
    const OP_SUBX_DxDy_B = 0b1001000100000000; // subx.bwl Dx,Dy
    const OP_SUBX_DxDy_W = 0b1001000101000000; // subx.bwl Dx,Dy
    const OP_SUBX_DxDy_L = 0b1001000110000000; // subx.bwl Dx,Dy

    const OP_SUBX_AxAy_B = 0b1001000100001000; // subx.bwl -(Ax),-(Ay)
    const OP_SUBX_AxAy_W = 0b1001000101001000; // subx.bwl -(Ax),-(Ay)
    const OP_SUBX_AxAy_L = 0b1001000110001000; // subx.bwl -(Ax),-(Ay)


    //                          rrr111EAEAEA
    const OP_MULS_W     = 0b1100000111000000;

    //                          rrr011EAEAEA
    const OP_MULU_W     = 0b1100000011000000;

    //                          rrr111EAEAEA
    const OP_DIVS_W     = 0b1000000111000000;

    //                          rrr011EAEAEA
    const OP_DIVU_W     = 0b1000000011000000;


    //                      0100100mmm000rrr
    const OP_EXT_W      = 0b0100100010000000;
    const OP_EXT_L      = 0b0100100011000000;
    const OP_EXTB_L     = 0b0100100111000000; // 020+

    //                              ssEAEAEA
    const OP_TST_B      = 0b0100101000000000;
    const OP_TST_W      = 0b0100101001000000;
    const OP_TST_L      = 0b0100101010000000;

    //                                EAEAEA
    const OP_NBCD       = 0b0100100000000000; // nbcd <ea>

    //                          xxx      yyy
    const OP_ABCD_R     = 0b1100000100000000; // abcd Dx,Dy
    const OP_ABCD_M     = 0b1100000100001000; // abcd (Ax)-,(Ay)

    const OP_SBCD_R     = 0b1000000100000000; // abcd Dx,Dy
    const OP_SBCD_M     = 0b1000000100001000; // abcd (Ax)-,(Ay)

}
