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

/**
 * Explicit upper 10 bits of the opcode word. We are going for a switch/case of 1024
 * entries like the hardened masochists we are. The lower 6 bits are usually effective
 * address data.
 *
 * We also include exact matches for opcodes that are ambiguous to parse otherwise.
 */
interface IPrefix {

    //                  --fedcba9876543210
    const OP_ORI_CCR  = 0b0000000000111100; // exact match
    const OP_ORI_SR   = 0b0000000001111100; // exact match

    //                    --------ssEAEAEA
    const OP_ORI_B    = 0b0000000000000000;
    const OP_ORI_W    = 0b0000000001000000;
    const OP_ORI_L    = 0b0000000010000000;

    //                  --fedcba9876543210
    const OP_ANDI_CCR = 0b0000000000111100; // exact match
    const OP_ANDI_SR  = 0b0000000001111100; // exact match

    //                    --------ssEAEAEA
    const OP_ANDI_B   = 0b0000001000000000;
    const OP_ANDI_W   = 0b0000001001000000;
    const OP_ANDI_L   = 0b0000001010000000;

    //                    --------ssEAEAEA
    const OP_SUBI_B   = 0b0000010000000000;
    const OP_SUBI_W   = 0b0000010001000000;
    const OP_SUBI_L   = 0b0000010010000000;

    //                    --------ssEAEAEA
    const OP_ADDI_B   = 0b0000011000000000;
    const OP_ADDI_W   = 0b0000011001000000;
    const OP_ADDI_L   = 0b0000011010000000;

    //                  --fedcba9876543210
    const OP_EORI_CCR = 0b0000101000111100; // exact match
    const OP_EORI_SR  = 0b0000101001111100; // exact match

    //                    --------ssEAEAEA
    const OP_EORI_B   = 0b0000101000000000;
    const OP_EORI_W   = 0b0000101001000000;
    const OP_EORI_L   = 0b0000101010000000;

    //                    --------ssEAEAEA
    const OP_CMPI_B   = 0b0000110000000000;
    const OP_CMPI_W   = 0b0000110001000000;
    const OP_CMPI_L   = 0b0000110010000000;

    const OP_ILLEGAL  = 0b0100101011111100; // exact match
    const OP_RESET    = 0b0100111001110000; // exact match
    const OP_NOP      = 0b0100111001110001; // exact match
    const OP_STOP     = 0b0100111001110010; // exact match
    const OP_RTE      = 0b0100111001110011; // exact match
    const OP_RTS      = 0b0100111001110101; // exact match
    const OP_TRAPV    = 0b0100111001110110; // exact match
    const OP_RTR      = 0b0100111001110111; // exact match
}
