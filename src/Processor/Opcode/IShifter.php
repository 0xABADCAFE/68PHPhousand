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
 * Opode prefixes for shift and rotate instructions
 */
interface IShifter
{
    // ASL immediate
    //                          iiiLssI00yyy - asl.b/w/l #i,dy i = 1-8
    const OP_ASL_ID_B   = 0b1110000100000000;
    const OP_ASL_ID_W   = 0b1110000101000000;
    const OP_ASL_ID_L   = 0b1110000110000000;

    // ASL dynamic
    //                          xxxLssR00yyy - asl.b/w/l dx,dy x mod 64
    const OP_ASL_DD_B   = 0b1110000100100000;
    const OP_ASL_DD_W   = 0b1110000101100000;
    const OP_ASL_DD_L   = 0b1110000110100000;

    // ASL Mem
    //                          000L11EAEAEA - asl(.w) <ea>
    const OP_ASL_M_W    = 0b1110000111000000;

    // ASR immediate
    //                          iiiRssI00yyy - asl.b/w/l #i,dy i = 1-8
    const OP_ASR_ID_B   = 0b1110000000000000;
    const OP_ASR_ID_W   = 0b1110000001000000;
    const OP_ASR_ID_L   = 0b1110000010000000;

    // ASR dynamic
    //                          xxxRssR00yyy - asl.b/w/l dx,dy x mod 64
    const OP_ASR_DD_B   = 0b1110000000100000;
    const OP_ASR_DD_W   = 0b1110000001100000;
    const OP_ASR_DD_L   = 0b1110000010100000;

    // ASR Mem
    //                          000R11EAEAEA - asr(.w) <ea>
    const OP_ASR_M_W    = 0b1110000011000000;


    // LSL immediate
    //                          iiiLssI01yyy - lsl.b/w/l #i,dy i = 1-8
    const OP_LSL_ID_B   = 0b1110000100001000;
    const OP_LSL_ID_W   = 0b1110000101001000;
    const OP_LSL_ID_L   = 0b1110000110001000;

    // LSL dynamic
    //                          xxxLssR01yyy - lsl.b/w/l dx,dy x mod 64
    const OP_LSL_DD_B   = 0b1110000100101000;
    const OP_LSL_DD_W   = 0b1110000101101000;
    const OP_LSL_DD_L   = 0b1110000110101000;

    // LSL Mem
    //                          001L11EAEAEA - lsl(.w) <ea>
    const OP_LSL_M_W    = 0b1110001111000000;

    // LSR immediate
    //                          iiiRssI01yyy - lsr.b #i,dy i = 1-8
    const OP_LSR_ID_B   = 0b1110000000001000;
    const OP_LSR_ID_W   = 0b1110000001001000;
    const OP_LSR_ID_L   = 0b1110000010001000;

    // LSR dynamic
    //                          xxxRssR01yyy - lsr.b dx,dy x mod 64
    const OP_LSR_DD_B   = 0b1110000000101000;
    const OP_LSR_DD_W   = 0b1110000001101000;
    const OP_LSR_DD_L   = 0b1110000010101000;

    // LSR Mem
    //                          001R11EAEAEA - lsr(.w) <eae
    const OP_LSR_M_W    = 0b1110001011000000;

    // ROL immediate
    //                          iiiLssI11yyy - rol.b/w/l #i,dy i = 1-8
    const OP_ROL_ID_B   = 0b1110000100011000;
    const OP_ROL_ID_W   = 0b1110000101011000;
    const OP_ROL_ID_L   = 0b1110000110011000;

    // ROL dynamic
    //                          xxxLssR01yyy - rol.b/w/l dx,dy x mod 64
    const OP_ROL_DD_B   = 0b1110000100111000;
    const OP_ROL_DD_W   = 0b1110000101111000;
    const OP_ROL_DD_L   = 0b1110000110111000;

    // ROL Mem
    //                          011L11EAEAEA - rol(.w) <ea>
    const OP_ROL_M_W    = 0b1110011111000000;

    // ROR immediate
    //                          iiiRssI11yyy - ror.b/w/l #i,dy i = 1-8
    const OP_ROR_ID_B   = 0b1110000000011000;
    const OP_ROR_ID_W   = 0b1110000001011000;
    const OP_ROR_ID_L   = 0b1110000010011000;

    // ROR dynamic
    //                          xxxRssR01yyy - ror.b/w/l dx,dy x mod 64
    const OP_ROR_DD_B   = 0b1110000000111000;
    const OP_ROR_DD_W   = 0b1110000001111000;
    const OP_ROR_DD_L   = 0b1110000010111000;

    // ROR Mem
    //                          011R11EAEAEA - ror(.w) <ea>
    const OP_ROR_M_W    = 0b1110011011000000;

    // ROXL immediate
    //                          iiiLssI10yyy - roxl.b/w/l #i,dy i = 1-8
    const OP_ROXL_ID_B  = 0b1110000100010000;
    const OP_ROXL_ID_W  = 0b1110000101010000;
    const OP_ROXL_ID_L  = 0b1110000110010000;

    // ROXL dynamic
    //                          xxxLssR10yyy - roxl.b/w/l dx,dy x mod 64
    const OP_ROXL_DD_B  = 0b1110000100110000;
    const OP_ROXL_DD_W  = 0b1110000101110000;
    const OP_ROXL_DD_L  = 0b1110000110110000;

    // ROXL Mem
    //                          010L11EAEAEA - roxl(.w) <ea>
    const OP_ROXL_M_W   = 0b1110010111000000;

    // ROXR immediate
    //                          iiiRssI10yyy - roxr.b/w/l #i,dy i = 1-8
    const OP_ROXR_ID_B  = 0b1110000000010000;
    const OP_ROXR_ID_W  = 0b1110000001010000;
    const OP_ROXR_ID_L  = 0b1110000010010000;

    // ROXR dynamic
    //                          xxxRssR10yyy - roxr.b/w/l dx,dy x mod 64
    const OP_ROXR_DD_B  = 0b1110000000110000;
    const OP_ROXR_DD_W  = 0b1110000001110000;
    const OP_ROXR_DD_L  = 0b1110000010110000;

    // ROXR Mem
    //                          010R11EAEAEA - roxr(.w) <ea>
    const OP_ROXR_M_W   = 0b1110010011000000;
}
