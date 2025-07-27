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
 * Useful constants for opcode word interpretation
 */
interface IOpcode {
    const MASK_OP_LINE     = 0b1111000000000000; // Instruction line type
    const MASK_CC_TYPE     = 0b0000111100000000; // Condtion type for Bcc/Scc/DBcc
    const MASK_EA_REG      = 0b0000000000000111; // Register operand for most EA
    const MASK_REG_UPPER   = 0b0000111000000000; // Register operand various operations
    const MASK_IMM_SMALL   = 0b0000111000000000; // Small immediate for addq/subq
    const MASK_SMALL_DISP  = 0b0000000011111111; // 8-bit displacement
    const MASK_MOVEQ_IMM   = 0b0000000011111111; // 8-bit immediate for moveq
    const MASK_EA_MODE     = 0b0000000000111000; // Mode operand for most EA
    const MASK_EA_MODE2    = 0b0000000000111111; // Mode operand for special EA
    const MASK_OP_SIZE     = 0b0000000011000000; // Size operand for common logic/arithmetic
    const MASK_OP_SIZE_M   = 0b0011000000000000; // Size operand for move/movea
    const MASK_OP_SIZE_MPE = 0b0000000001000000; // Size operand for movem/movep/ext
    const MASK_OP_SIZE_A   = 0b0000000010000000; // Size operand for address ops
    const MASK_MOVEM_DIR   = 0b0000010000000000; // MOVEM direction
    const MASK_MOVEP_DIR   = 0x0000000010000000; // MOVEP direction
    const MASK_SHIFT_DIR   = 0x0000000010000000; // shift/rotate direction
    const MASK_MOVEUSP_DIR = 0x0000000000001000; // MOVE USP direction

    // Brief eXtension Word
    const MASK_BXW_MODE    = 0b1000000000000000; // direction
    const MASK_BXW_REG     = 0b0111000000000000; // register
    const MASK_BXW_DISP    = 0b0000000011111111; // displacement

    // EA modes
    //             =   ----------xxx---
    const EA_DREG  = 0b0000000000000000; // Data register dN
    const EA_AREG  = 0b0000000000001000; // Address register aN
    const EA_ADDR  = 0b0000000000010000; // Address (aN)
    const EA_ADPI  = 0b0000000000011000; // Address Post Increment (aN)+
    const EA_ADPD  = 0b0000000000100000; // Address Pre Decrement -(aN)
    const EA_AD_D  = 0b0000000000101000; // Address with Displacement (d16, aN)
    const EA_AD_X  = 0b0000000000110000; // Address with Index (d8, An, Xn)

    // Special EA cases
    //             =   ----------xxxxxx
    const EA_SHORT = 0b0000000000111000; // Absolute short (xxx).w
    const EA_LONG  = 0b0000000000111001; // Absolute long (xxx).l
    const EA_PC_D  = 0b0000000000111010; // Program counter with displacement (d16, pc)
    const EA_PC_X  = 0b0000000000111011; // Program counter with index (d8, pc, xN)
    const EA_IMM   = 0b0000000000111100; // Immediate #imm

    // Specific condition code requirenents for MASK_CC_TYPE
    //          =   ----xxxx--------
    const CC_T  = 0b0000000000000000; // True
    const CC_F  = 0b0000000100000000; // False
    const CC_HI = 0b0000001000000000; // High
    const CC_LS = 0b0000001100000000; // Low or Same
    const CC_CC = 0b0000010000000000; // Carry Clear
    const CC_CS = 0b0000010100000000; // Carry Set
    const CC_NE = 0b0000011000000000; // Not Equal
    const CC_EQ = 0b0000011100000000; // Equal
    const CC_VC = 0b0000100000000000; // Overflow Clear
    const CC_VS = 0b0000100100000000; // Overflow Set
    const CC_PL = 0b0000101000000000; // Plus
    const CC_MI = 0b0000101100000000; // Minus
    const CC_GE = 0b0000110000000000; // Greater or Equal
    const CC_LT = 0b0000110100000000; // Less Than
    const CC_GT = 0b0000111000000000; // Greater THan
    const CC_LE = 0b0000111100000000; // Less or Equal
}
