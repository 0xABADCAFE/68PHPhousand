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

namespace ABadCafe\G8PHPhousand\Processor;

/**
 * Explicit instruction word MSB values enumerated to simplify instruction decode. We will
 * perform a switch case on the 256 possible values. Each case will then have to deal with
 * any remaining decode. Since the MSB is actually comprised of the 4-bit instruction line
 * and 4 additonal bits of potential operand data, whatever that data are, can be implied
 * by the handler rather than having to explicitly decode from the MB.
 *
 * For many instructions the first byte fully determines which operation we will perform but
 * sometimes not.
 *
 * Operations commented with * are privileged
 */
interface IOpcodeMSB {
    const OP_MSB_ORI     = 0b00000000; // 0000 000 0 - ORI to CCR, ORI to SR*, ORI
    const OP_MSB_ANDI    = 0b00000010; // 0000 001 0 - ANDI to CCR, ANDI to SR*, ANDI
    const OP_MSB_SUBI    = 0b00000100; // 0000 010 0 -
    const OP_MSB_ADDI    = 0b00000110; // 0000 011 0 -
    const OP_MSB_EORI    = 0b00001010; // 0000 101 0 - EORI to CCR, EORI to SR*, EORI
    const OP_MSB_CMPI    = 0b00001100; // 0000 110 0 -

    // Can be BTST, BCHG, BCLR, BSET
    const OP_MSB_BTST    = 0b00001000; // 0000 100 0 - BTST, BCHG, BCLR, BSET
    const OP_MSB_BCHG    = 0b00001000; // 0000 100 0 - BTST, BCHG, BCLR, BSET
    const OP_MSB_BCLR    = 0b00001000; // 0000 100 0 - BTST, BCHG, BCLR, BSET
    const OP_MSB_BSET    = 0b00001000; // 0000 100 0 - BTST, BCHG, BCLR, BSET

    const OP_MSB_BTST_D0 = 0b00000001; // 0000 000 1
    const OP_MSB_BTST_D1 = 0b00000011; // 0000 001 1
    const OP_MSB_BTST_D2 = 0b00000101; // 0000 010 1
    const OP_MSB_BTST_D3 = 0b00000111; // 0000 011 1
    const OP_MSB_BTST_D4 = 0b00001001; // 0000 100 1
    const OP_MSB_BTST_D5 = 0b00001011; // 0000 101 1
    const OP_MSB_BTST_D6 = 0b00001101; // 0000 110 1
    const OP_MSB_BTST_D7 = 0b00001111; // 0000 111 1

    const OP_MSB_BCHG_D0 = 0b00000001; // 0000 000 1
    const OP_MSB_BCHG_D1 = 0b00000011; // 0000 001 1
    const OP_MSB_BCHG_D2 = 0b00000101; // 0000 010 1
    const OP_MSB_BCHG_D3 = 0b00000111; // 0000 011 1
    const OP_MSB_BCHG_D4 = 0b00001001; // 0000 100 1
    const OP_MSB_BCHG_D5 = 0b00001011; // 0000 101 1
    const OP_MSB_BCHG_D6 = 0b00001101; // 0000 110 1
    const OP_MSB_BCHG_D7 = 0b00001111; // 0000 111 1

    const OP_MSB_BCLR_D0 = 0b00000001; // 0000 000 1
    const OP_MSB_BCLR_D1 = 0b00000011; // 0000 001 1
    const OP_MSB_BCLR_D2 = 0b00000101; // 0000 010 1
    const OP_MSB_BCLR_D3 = 0b00000111; // 0000 011 1
    const OP_MSB_BCLR_D4 = 0b00001001; // 0000 100 1
    const OP_MSB_BCLR_D5 = 0b00001011; // 0000 101 1
    const OP_MSB_BCLR_D6 = 0b00001101; // 0000 110 1
    const OP_MSB_BCLR_D7 = 0b00001111; // 0000 111 1

    const OP_MSB_BSET_D0 = 0b00000001; // 0000 000 1
    const OP_MSB_BSET_D1 = 0b00000011; // 0000 001 1
    const OP_MSB_BSET_D2 = 0b00000101; // 0000 010 1
    const OP_MSB_BSET_D3 = 0b00000111; // 0000 011 1
    const OP_MSB_BSET_D4 = 0b00001001; // 0000 100 1
    const OP_MSB_BSET_D5 = 0b00001011; // 0000 101 1
    const OP_MSB_BSET_D6 = 0b00001101; // 0000 110 1
    const OP_MSB_BSET_D7 = 0b00001111; // 0000 111 1

    const OP_MSB_MOVEP_D0 = 0b00000001; // 0000 000 1
    const OP_MSB_MOVEP_D1 = 0b00000011; // 0000 001 1
    const OP_MSB_MOVEP_D2 = 0b00000101; // 0000 010 1
    const OP_MSB_MOVEP_D3 = 0b00000111; // 0000 011 1
    const OP_MSB_MOVEP_D4 = 0b00001001; // 0000 100 1
    const OP_MSB_MOVEP_D5 = 0b00001011; // 0000 101 1
    const OP_MSB_MOVEP_D6 = 0b00001101; // 0000 110 1
    const OP_MSB_MOVEP_D7 = 0b00001111; // 0000 111 1

    // Move instructions use up lines 0001, 0010 and 0011...

    // MOVEA.w                                  //   .w  An
    const OP_MSB_MOVEA_W_A0    = 0b00110000; // 0011 000 0
    const OP_MSB_MOVEA_W_A1    = 0b00110010; // 0011 001 0
    const OP_MSB_MOVEA_W_A2    = 0b00110100; // 0011 010 0
    const OP_MSB_MOVEA_W_A3    = 0b00110110; // 0011 011 0
    const OP_MSB_MOVEA_W_A4    = 0b00111000; // 0011 100 0
    const OP_MSB_MOVEA_W_A5    = 0b00111010; // 0011 101 0
    const OP_MSB_MOVEA_W_A6    = 0b00111100; // 0011 110 0
    const OP_MSB_MOVEA_W_A7    = 0b00111110; // 0011 111 0

    // MOVEA.l                                  //   .l  An
    const OP_MSB_MOVEA_L_A0    = 0b00100000; // 0010 000 0
    const OP_MSB_MOVEA_L_A1    = 0b00100010; // 0010 001 0
    const OP_MSB_MOVEA_L_A2    = 0b00100100; // 0010 010 0
    const OP_MSB_MOVEA_L_A3    = 0b00100110; // 0010 011 0
    const OP_MSB_MOVEA_L_A4    = 0b00101000; // 0010 100 0
    const OP_MSB_MOVEA_L_A5    = 0b00101010; // 0010 101 0
    const OP_MSB_MOVEA_L_A6    = 0b00101100; // 0010 110 0
    const OP_MSB_MOVEA_L_A7    = 0b00101110; // 0010 111 0

    // MOVE.b - NOTE - Least Significant bit is upper bit of source EA mode spanning byte boundary
    //                                          //   .b  Xn
    const OP_MSB_MOVE_B_X0_0      = 0b00010000; // 0001 000 x [LSB doesn't matter here]
    const OP_MSB_MOVE_B_X0_1      = 0b00010001; // 0001 000 x
    const OP_MSB_MOVE_B_X1_0      = 0b00010010; // 0001 001 x
    const OP_MSB_MOVE_B_X1_1      = 0b00010011; // 0001 001 x
    const OP_MSB_MOVE_B_X2_0      = 0b00010100; // 0001 010 x
    const OP_MSB_MOVE_B_X2_1      = 0b00010101; // 0001 010 x
    const OP_MSB_MOVE_B_X3_0      = 0b00010110; // 0001 011 x
    const OP_MSB_MOVE_B_X3_1      = 0b00010111; // 0001 011 x
    const OP_MSB_MOVE_B_X4_0      = 0b00011000; // 0001 100 x
    const OP_MSB_MOVE_B_X4_1      = 0b00011001; // 0001 100 x
    const OP_MSB_MOVE_B_X5_0      = 0b00011010; // 0001 101 x
    const OP_MSB_MOVE_B_X5_1      = 0b00011011; // 0001 101 x
    const OP_MSB_MOVE_B_X6_0      = 0b00011100; // 0001 110 x
    const OP_MSB_MOVE_B_X6_1      = 0b00011101; // 0001 110 x
    const OP_MSB_MOVE_B_X7_0      = 0b00011110; // 0001 111 x
    const OP_MSB_MOVE_B_X7_1      = 0b00011111; // 0001 111 x

    // MOVE.w - NOTE - Least Significant bit is upper bit of source EA mode spanning byte boundary
    //                                          //   .w  Xn
    const OP_MSB_MOVE_W_X0_0      = 0b00110000; // 0011 000 x [LSB doesn't matter here]
    const OP_MSB_MOVE_W_X0_1      = 0b00110001; // 0011 000 x
    const OP_MSB_MOVE_W_X1_0      = 0b00110010; // 0011 001 x
    const OP_MSB_MOVE_W_X1_1      = 0b00110011; // 0011 001 x
    const OP_MSB_MOVE_W_X2_0      = 0b00110100; // 0011 010 x
    const OP_MSB_MOVE_W_X2_1      = 0b00110101; // 0011 010 x
    const OP_MSB_MOVE_W_X3_0      = 0b00110110; // 0011 011 x
    const OP_MSB_MOVE_W_X3_1      = 0b00110111; // 0011 011 x
    const OP_MSB_MOVE_W_X4_0      = 0b00111000; // 0011 100 x
    const OP_MSB_MOVE_W_X4_1      = 0b00111001; // 0011 100 x
    const OP_MSB_MOVE_W_X5_0      = 0b00111010; // 0011 101 x
    const OP_MSB_MOVE_W_X5_1      = 0b00111011; // 0011 101 x
    const OP_MSB_MOVE_W_X6_0      = 0b00111100; // 0011 110 x
    const OP_MSB_MOVE_W_X6_1      = 0b00111101; // 0011 110 x
    const OP_MSB_MOVE_W_X7_0      = 0b00111110; // 0011 111 x
    const OP_MSB_MOVE_W_X7_1      = 0b00111111; // 0011 111 x

    // MOVE.l - NOTE - Least Significant bit is upper bit of source EA mode spanning byte boundary
    //                                          //   .l  Xn
    const OP_MSB_MOVE_L_X0_0      = 0b00100000; // 0010 000 x [LSB doesn't matter here]
    const OP_MSB_MOVE_L_X0_1      = 0b00100001; // 0010 000 x
    const OP_MSB_MOVE_L_X1_0      = 0b00100010; // 0010 001 x
    const OP_MSB_MOVE_L_X1_1      = 0b00100011; // 0010 001 x
    const OP_MSB_MOVE_L_X2_0      = 0b00100100; // 0010 010 x
    const OP_MSB_MOVE_L_X2_1      = 0b00100101; // 0010 010 x
    const OP_MSB_MOVE_L_X3_0      = 0b00100110; // 0010 011 x
    const OP_MSB_MOVE_L_X3_1      = 0b00100111; // 0010 011 x
    const OP_MSB_MOVE_L_X4_0      = 0b00101000; // 0010 100 x
    const OP_MSB_MOVE_L_X4_1      = 0b00101001; // 0010 100 x
    const OP_MSB_MOVE_L_X5_0      = 0b00101010; // 0010 101 x
    const OP_MSB_MOVE_L_X5_1      = 0b00101011; // 0010 101 x
    const OP_MSB_MOVE_L_X6_0      = 0b00101100; // 0010 110 x
    const OP_MSB_MOVE_L_X6_1      = 0b00101101; // 0010 110 x
    const OP_MSB_MOVE_L_X7_0      = 0b00101110; // 0010 111 x
    const OP_MSB_MOVE_L_X7_1      = 0b00101111; // 0010 111 x

    const OP_MSB_NEGX             = 0b01000000; // 0100 0000
    const OP_MSB_MOVE_FROM_SR     = 0b01000000; // 0100 0000

    const OP_MSB_CLR              = 0b01000010; // 0100 0010
    const OP_MSB_NEG              = 0b01000100; // 0100 0100
    const OP_MSB_MOVE_TO_CCR      = 0b01000100; // 0100 0100

    const OP_MSB_NOT              = 0b01000110; // 0100 0110
    const OP_MSB_MOVE_TO_SR       = 0b01000110; // 0100 0110

    // Can be EXT, NBCD, SWAP or PEA
    const OP_MSB_EXT_NBCD = 0b01001000; // 0100 1000
    const OP_MSB_EXT_SWAP = 0b01001000; // 0100 1000
    const OP_MSB_EXT_PEA  = 0b01001000; // 0100 1000

    // Can be ILLEGAL, TAS or TST
    const OP_MSB_ILLEGAL  = 0b01001010; // 0100 1010
    const OP_MSB_TAS      = 0b01001010; // 0100 1010
    const OP_MSB_TST      = 0b01001010; // 0100 1010


    // Can be TRAP, LINK, UNLK, MOVE USP, RESET, NOP, STOP, RTE, RTS, TRAPV, RTR, JSR or JMP!
    const OP_MSB_TRAP     = 0b01001110; // 0100 1110
    const OP_MSB_LINK     = 0b01001110; // 0100 1110
    const OP_MSB_ULNK     = 0b01001110; // 0100 1110
    const OP_MSB_MOVE_USP = 0b01001110; // 0100 1110
    const OP_MSB_RESET    = 0b01001110; // 0100 1110
    const OP_MSB_NOP      = 0b01001110; // 0100 1110
    const OP_MSB_STOP     = 0b01001110; // 0100 1110
    const OP_MSB_RTE      = 0b01001110; // 0100 1110
    const OP_MSB_RTS      = 0b01001110; // 0100 1110
    const OP_MSB_TRAPV    = 0b01001110; // 0100 1110
    const OP_MSB_RTR      = 0b01001110; // 0100 1110
    const OP_MSB_JSR      = 0b01001110; // 0100 1110
    const OP_MSB_JMP      = 0b01001110; // 0100 1110

    // MOVEM
    //                                           //        D
    const OP_MSB_MOVEM_R2M         = 0b01001000; // 0100 1 0 00
    const OP_MSB_MOVEM_M2R         = 0b01001100; // 0100 1 1 00

    // Can be LEA <>, aN or CHK DN
    //                                           //       Xn
    const OP_MSB_LEA_A0        = 0b01000001; // 0100 000 1
    const OP_MSB_LEA_A1        = 0b01000011; // 0100 001 1
    const OP_MSB_LEA_A2        = 0b01000101; // 0100 010 1
    const OP_MSB_LEA_A3        = 0b01000111; // 0100 011 1
    const OP_MSB_LEA_A4        = 0b01001001; // 0100 100 1
    const OP_MSB_LEA_A5        = 0b01001011; // 0100 101 1
    const OP_MSB_LEA_A6        = 0b01001101; // 0100 110 1
    const OP_MSB_LEA_A7        = 0b01001111; // 0100 111 1

    const OP_MSB_CHK_D0        = 0b01000001; // 0100 000 1
    const OP_MSB_CHK_D1        = 0b01000011; // 0100 001 1
    const OP_MSB_CHK_D2        = 0b01000101; // 0100 010 1
    const OP_MSB_CHK_D3        = 0b01000111; // 0100 011 1
    const OP_MSB_CHK_D4        = 0b01001001; // 0100 100 1
    const OP_MSB_CHK_D5        = 0b01001011; // 0100 101 1
    const OP_MSB_CHK_D6        = 0b01001101; // 0100 110 1
    const OP_MSB_CHK_D7        = 0b01001111; // 0100 111 1

    // 0101 xxxx cover a lot of aliased operations with different interpetations of xxxx

    // ADDQ #N / SCC                             //      N-1
    const OP_MSB_ADDQ_1            = 0b01010000; // 0101 000 0
    const OP_MSB_ADDQ_2            = 0b01010010; // 0101 001 0
    const OP_MSB_ADDQ_3            = 0b01010100; // 0101 010 0
    const OP_MSB_ADDQ_4            = 0b01010110; // 0101 011 0
    const OP_MSB_ADDQ_5            = 0b01011000; // 0101 100 0
    const OP_MSB_ADDQ_6            = 0b01011010; // 0101 101 0
    const OP_MSB_ADDQ_7            = 0b01011100; // 0101 110 0
    const OP_MSB_ADDQ_8            = 0b01011110; // 0101 111 0

    // SUBQ #N                                   //      N-1
    const OP_MSB_SUBQ_1            = 0b01010001; // 0101 000 1
    const OP_MSB_SUBQ_2            = 0b01010011; // 0101 001 1
    const OP_MSB_SUBQ_3            = 0b01010101; // 0101 010 1
    const OP_MSB_SUBQ_4            = 0b01010111; // 0101 011 1
    const OP_MSB_SUBQ_5            = 0b01011001; // 0101 100 1
    const OP_MSB_SUBQ_6            = 0b01011011; // 0101 101 1
    const OP_MSB_SUBQ_7            = 0b01011101; // 0101 110 1
    const OP_MSB_SUBQ_8            = 0b01011111; // 0101 111 1

    // Scc / DBcc
    const OP_MSB_SCC_DBCC_T        = 0b01010000; // aliases OP_MSB_ADDQ_1
    const OP_MSB_SCC_DBCC_F        = 0b01010001; // aliases OP_MSB_SUBQ_1
    const OP_MSB_SCC_DBCC_HI       = 0b01010010; // aliases OP_MSB_ADDQ_2
    const OP_MSB_SCC_DBCC_LS       = 0b01010011; // aliases OP_MSB_SUBQ_2
    const OP_MSB_SCC_DBCC_CC       = 0b01010100; // aliases OP_MSB_ADDQ_3
    const OP_MSB_SCC_DBCC_CS       = 0b01010101; // aliases OP_MSB_SUBQ_3
    const OP_MSB_SCC_DBCC_NE       = 0b01010110; // aliases OP_MSB_ADDQ_4
    const OP_MSB_SCC_DBCC_EQ       = 0b01010111; // aliases OP_MSB_SUBQ_4
    const OP_MSB_SCC_DBCC_VC       = 0b01011000; // aliases OP_MSB_ADDQ_5
    const OP_MSB_SCC_DBCC_VS       = 0b01011001; // aliases OP_MSB_SUBQ_5
    const OP_MSB_SCC_DBCC_PL       = 0b01011010; // aliases OP_MSB_ADDQ_6
    const OP_MSB_SCC_DBCC_MI       = 0b01011011; // aliases OP_MSB_SUBQ_6
    const OP_MSB_SCC_DBCC_GE       = 0b01011100; // aliases OP_MSB_ADDQ_7
    const OP_MSB_SCC_DBCC_LT       = 0b01011101; // aliases OP_MSB_SUBQ_7
    const OP_MSB_SCC_DBCC_GT       = 0b01011110; // aliases OP_MSB_ADDQ_8
    const OP_MSB_SCC_DBCC_LE       = 0b01011111; // aliases OP_MSB_SUBQ_8

    // BT is always unconditional BRA
    const OP_MSB_BRA               = 0b01100000; // 0110 0000

    // BF is replaced by BSR
    const OP_MSB_BSR               = 0b01100001; // 0110 0001

    // BCC
    const OP_MSB_BCC_HI            = 0b01100010; // 0110 cc
    const OP_MSB_BCC_LS            = 0b01100011; // 0110 cc
    const OP_MSB_BCC_CC            = 0b01100100; // 0110 cc
    const OP_MSB_BCC_CS            = 0b01100101; // 0110 cc
    const OP_MSB_BCC_NE            = 0b01100110; // 0110 cc
    const OP_MSB_BCC_EQ            = 0b01100111; // 0110 cc
    const OP_MSB_BCC_VC            = 0b01101000; // 0110 cc
    const OP_MSB_BCC_VS            = 0b01101001; // 0110 cc
    const OP_MSB_BCC_PL            = 0b01101010; // 0110 cc
    const OP_MSB_BCC_MI            = 0b01101011; // 0110 cc
    const OP_MSB_BCC_GE            = 0b01101100; // 0110 cc
    const OP_MSB_BCC_LT            = 0b01101101; // 0110 cc
    const OP_MSB_BCC_GT            = 0b01101110; // 0110 cc
    const OP_MSB_BCC_LE            = 0b01101111; // 0110 cc

    // MOVEQ
    const OP_MSB_MOVEQ_D0          = 0b01110000; // 0111 000 0
    const OP_MSB_MOVEQ_D1          = 0b01110010; // 0111 001 0
    const OP_MSB_MOVEQ_D2          = 0b01110100; // 0111 010 0
    const OP_MSB_MOVEQ_D3          = 0b01110110; // 0111 011 0
    const OP_MSB_MOVEQ_D4          = 0b01111000; // 0111 100 0
    const OP_MSB_MOVEQ_D5          = 0b01111010; // 0111 101 0
    const OP_MSB_MOVEQ_D6          = 0b01111100; // 0111 110 0
    const OP_MSB_MOVEQ_D7          = 0b01111110; // 0111 111 0

    // DIVU
    const OP_MSB_DIVU_D0           = 0b10000000; // 1000 000 0
    const OP_MSB_DIVU_D1           = 0b10000010; // 1000 001 0
    const OP_MSB_DIVU_D2           = 0b10000100; // 1000 010 0
    const OP_MSB_DIVU_D3           = 0b10000110; // 1000 011 0
    const OP_MSB_DIVU_D4           = 0b10001000; // 1000 100 0
    const OP_MSB_DIVU_D5           = 0b10001010; // 1000 101 0
    const OP_MSB_DIVU_D6           = 0b10001100; // 1000 110 0
    const OP_MSB_DIVU_D7           = 0b10001110; // 1000 111 0

    // Can be DIVS or SBCD
    const OP_MSB_DIVS_D0    = 0b10000001; // 1000 000 1
    const OP_MSB_DIVS_D1    = 0b10000011; // 1000 001 1
    const OP_MSB_DIVS_D2    = 0b10000101; // 1000 010 1
    const OP_MSB_DIVS_D3    = 0b10000111; // 1000 011 1
    const OP_MSB_DIVS_D4    = 0b10001001; // 1000 100 1
    const OP_MSB_DIVS_D5    = 0b10001011; // 1000 101 1
    const OP_MSB_DIVS_D6    = 0b10001101; // 1000 110 1
    const OP_MSB_DIVS_D7    = 0b10001111; // 1000 111 1

    const OP_MSB_SBCD_X0    = 0b10000001; // 1000 000 1
    const OP_MSB_SBCD_X1    = 0b10000011; // 1000 001 1
    const OP_MSB_SBCD_X2    = 0b10000101; // 1000 010 1
    const OP_MSB_SBCD_X3    = 0b10000111; // 1000 011 1
    const OP_MSB_SBCD_X4    = 0b10001001; // 1000 100 1
    const OP_MSB_SBCD_X5    = 0b10001011; // 1000 101 1
    const OP_MSB_SBCD_X6    = 0b10001101; // 1000 110 1
    const OP_MSB_SBCD_X7    = 0b10001111; // 1000 111 1

}


