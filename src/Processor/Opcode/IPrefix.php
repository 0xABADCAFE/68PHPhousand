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
interface IPrefix
{
    //                  --fedcba9876543210
    const OP_ORI_CCR  = 0b0000000000111100; // exact match
    const OP_ORI_SR   = 0b0000000001111100; // exact match

    //                    --------ssEAEAEA
    const OP_ORI_B    = 0b0000000000000000;
    const OP_ORI_W    = 0b0000000001000000;
    const OP_ORI_L    = 0b0000000010000000;

    //                  --fedcba9876543210
    const OP_ANDI_CCR = 0b0000001000111100; // exact match
    const OP_ANDI_SR  = 0b0000001001111100; // exact match

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


    // Test bit, dynamic (bit number in data reg)
    //                    --------ssEAEAEA
    const OP_BTST_D0  = 0b0000000100000000;
    const OP_BTST_D1  = 0b0000001100000000;
    const OP_BTST_D2  = 0b0000010100000000;
    const OP_BTST_D3  = 0b0000011100000000;
    const OP_BTST_D4  = 0b0000100100000000;
    const OP_BTST_D5  = 0b0000101100000000;
    const OP_BTST_D6  = 0b0000110100000000;
    const OP_BTST_D7  = 0b0000111100000000;

    // Test bit, immediate in ext word
    //                    --------ssEAEAEA
    const OP_BTST_I   = 0b0000100000000000;

    // Change bit, dynamic (bit number in data reg)
    //                    --------ssEAEAEA
    const OP_BCHG_D0  = 0b0000000101000000;
    const OP_BCHG_D1  = 0b0000001101000000;
    const OP_BCHG_D2  = 0b0000010101000000;
    const OP_BCHG_D3  = 0b0000011101000000;
    const OP_BCHG_D4  = 0b0000100101000000;
    const OP_BCHG_D5  = 0b0000101101000000;
    const OP_BCHG_D6  = 0b0000110101000000;
    const OP_BCHG_D7  = 0b0000111101000000;

    // Change bit, immediate in ext word
    //                    --------ssEAEAEA
    const OP_BCHG_I   = 0b0000100001000000;

    // Clear bit, dynamic (bit number in data reg)
    //                    --------ssEAEAEA
    const OP_BCLR_D0  = 0b0000000110000000;
    const OP_BCLR_D1  = 0b0000001110000000;
    const OP_BCLR_D2  = 0b0000010110000000;
    const OP_BCLR_D3  = 0b0000011110000000;
    const OP_BCLR_D4  = 0b0000100110000000;
    const OP_BCLR_D5  = 0b0000101110000000;
    const OP_BCLR_D6  = 0b0000110110000000;
    const OP_BCLR_D7  = 0b0000111110000000;

    // Clear bit, immediate in ext word
    //                    --------ssEAEAEA
    const OP_BCLR_I   = 0b0000100010000000;

    // Set bit, dynamic (bit number in data reg)
    //                    --------ssEAEAEA
    const OP_BSET_D0  = 0b0000000111000000;
    const OP_BSET_D1  = 0b0000001111000000;
    const OP_BSET_D2  = 0b0000010111000000;
    const OP_BSET_D3  = 0b0000011111000000;
    const OP_BSET_D4  = 0b0000100111000000;
    const OP_BSET_D5  = 0b0000101111000000;
    const OP_BSET_D6  = 0b0000110111000000;
    const OP_BSET_D7  = 0b0000111111000000;

    // Set bit, immediate in ext word
    //                    --------ssEAEAEA
    const OP_BSET_I   = 0b0000100011000000;

    // Logical Shift Left
    //                    ----nnnDss-M-rrr
    const OP_LSL_8_B  = 0b1110000000000000;
    const OP_LSL_1_B  = 0b1110001000000000;
    const OP_LSL_2_B  = 0b1110010000000000;
    const OP_LSL_3_B  = 0b1110011000000000;
    const OP_LSL_4_B  = 0b1110100000000000;
    const OP_LSL_5_B  = 0b1110101000000000;
    const OP_LSL_6_B  = 0b1110110000000000;
    const OP_LSL_7_B  = 0b1110111000000000;

    //                    ----nnnDss-M-rrr
    const OP_LSL_8_W  = 0b1110000001000000;
    const OP_LSL_1_W  = 0b1110001001000000;
    const OP_LSL_2_W  = 0b1110010001000000;
    const OP_LSL_3_W  = 0b1110011001000000;
    const OP_LSL_4_W  = 0b1110100001000000;
    const OP_LSL_5_W  = 0b1110101001000000;
    const OP_LSL_6_W  = 0b1110110001000000;
    const OP_LSL_7_W  = 0b1110111001000000;

    //                    ----nnnDss-M-rrr
    const OP_LSL_8_L  = 0b1110000010000000;
    const OP_LSL_1_L  = 0b1110001010000000;
    const OP_LSL_2_L  = 0b1110010010000000;
    const OP_LSL_3_L  = 0b1110011010000000;
    const OP_LSL_4_L  = 0b1110100010000000;
    const OP_LSL_5_L  = 0b1110101010000000;
    const OP_LSL_6_L  = 0b1110110010000000;
    const OP_LSL_7_L  = 0b1110111010000000;

    //                    -------DssEAEAEA
    const OP_LSL_M    = 0b1110001011000000;

    //                    ----nnnDss-M-rrr
    const OP_LSR_8_B  = 0b1110000100000000;
    const OP_LSR_1_B  = 0b1110001100000000;
    const OP_LSR_2_B  = 0b1110010100000000;
    const OP_LSR_3_B  = 0b1110011100000000;
    const OP_LSR_4_B  = 0b1110100100000000;
    const OP_LSR_5_B  = 0b1110101100000000;
    const OP_LSR_6_B  = 0b1110110100000000;
    const OP_LSR_7_B  = 0b1110111100000000;

    //                    ----nnnDss-M-rrr
    const OP_LSR_8_W  = 0b1110000101000000;
    const OP_LSR_1_W  = 0b1110001101000000;
    const OP_LSR_2_W  = 0b1110010101000000;
    const OP_LSR_3_W  = 0b1110011101000000;
    const OP_LSR_4_W  = 0b1110100101000000;
    const OP_LSR_5_W  = 0b1110101101000000;
    const OP_LSR_6_W  = 0b1110110101000000;
    const OP_LSR_7_W  = 0b1110111101000000;

    //                    ----nnnDss-M-rrr
    const OP_LSR_8_L  = 0b1110000110000000;
    const OP_LSR_1_L  = 0b1110001110000000;
    const OP_LSR_2_L  = 0b1110010110000000;
    const OP_LSR_3_L  = 0b1110011110000000;
    const OP_LSR_4_L  = 0b1110100110000000;
    const OP_LSR_5_L  = 0b1110101110000000;
    const OP_LSR_6_L  = 0b1110110110000000;
    const OP_LSR_7_L  = 0b1110111110000000;

    //                    -------DssEAEAEA
    const OP_LSR_M    = 0b1110001111000000;


    const OP_ILLEGAL  = 0b0100101011111100; // exact match
    const OP_RESET    = 0b0100111001110000; // exact match
    const OP_NOP      = 0b0100111001110001; // exact match
    const OP_STOP     = 0b0100111001110010; // exact match
    const OP_RTE      = 0b0100111001110011; // exact match
    const OP_RTS      = 0b0100111001110101; // exact match
    const OP_TRAPV    = 0b0100111001110110; // exact match
    const OP_RTR      = 0b0100111001110111; // exact match
}
