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
interface IOpcode
{
    const MASK_OP_LINE     = 0b1111000000000000; // Instruction line type
    const MASK_CC_TYPE     = 0b0000111100000000; // Condition type for Bcc/Scc/DBcc
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
    const MASK_MOVEP_DIR   = 0b0000000010000000; // MOVEP direction
    const MASK_SHIFT_DIR   = 0b0000000010000000; // shift/rotate direction
    const MASK_MOVEUSP_DIR = 0b0000000000001000; // MOVE USP direction

    const MASK_OP_PREFIX   = 0b1111111111000000;

    const MASK_OP_STD_EA   = 0b0000000000111111; // Standard 6-bit EA mode

    // Brief eXtension Word
    const MASK_BXW_MODE    = 0b1000000000000000; // direction
    const MASK_BXW_REG     = 0b0111000000000000; // register
    const MASK_BXW_DISP    = 0b0000000011111111; // displacement

    const REG_EA_D0 = IRegister::D0;
    const REG_EA_D1 = IRegister::D1;
    const REG_EA_D2 = IRegister::D2;
    const REG_EA_D3 = IRegister::D3;
    const REG_EA_D4 = IRegister::D4;
    const REG_EA_D5 = IRegister::D5;
    const REG_EA_D6 = IRegister::D6;
    const REG_EA_D7 = IRegister::D7;

    const REG_EA_A0 = IRegister::A0;
    const REG_EA_A1 = IRegister::A1;
    const REG_EA_A2 = IRegister::A2;
    const REG_EA_A3 = IRegister::A3;
    const REG_EA_A4 = IRegister::A4;
    const REG_EA_A5 = IRegister::A5;
    const REG_EA_A6 = IRegister::A6;
    const REG_EA_A7 = IRegister::A7;

    // Values for registers encoded in bits 9-11
    const REG_UP_SHIFT = 9;
    const REG_UP_D0 = IRegister::D0 << self::REG_UP_SHIFT;
    const REG_UP_D1 = IRegister::D1 << self::REG_UP_SHIFT;
    const REG_UP_D2 = IRegister::D2 << self::REG_UP_SHIFT;
    const REG_UP_D3 = IRegister::D3 << self::REG_UP_SHIFT;
    const REG_UP_D4 = IRegister::D4 << self::REG_UP_SHIFT;
    const REG_UP_D5 = IRegister::D5 << self::REG_UP_SHIFT;
    const REG_UP_D6 = IRegister::D6 << self::REG_UP_SHIFT;
    const REG_UP_D7 = IRegister::D7 << self::REG_UP_SHIFT;

    const REG_UP_A0 = IRegister::A0 << self::REG_UP_SHIFT;
    const REG_UP_A1 = IRegister::A1 << self::REG_UP_SHIFT;
    const REG_UP_A2 = IRegister::A2 << self::REG_UP_SHIFT;
    const REG_UP_A3 = IRegister::A3 << self::REG_UP_SHIFT;
    const REG_UP_A4 = IRegister::A4 << self::REG_UP_SHIFT;
    const REG_UP_A5 = IRegister::A5 << self::REG_UP_SHIFT;
    const REG_UP_A6 = IRegister::A6 << self::REG_UP_SHIFT;
    const REG_UP_A7 = IRegister::A7 << self::REG_UP_SHIFT;

    // EA modes with register params
    //             =   ----------xxx---
    const LSB_EA_MODE_SHIFT = 3;
    const LSB_EA_D     = IEffectiveAddress::MODE_D    << self::LSB_EA_MODE_SHIFT; // Data register dN
    const LSB_EA_A     = IEffectiveAddress::MODE_A    << self::LSB_EA_MODE_SHIFT; // Address register aN
    const LSB_EA_AI    = IEffectiveAddress::MODE_AI   << self::LSB_EA_MODE_SHIFT; // Address (aN)
    const LSB_EA_AIPI  = IEffectiveAddress::MODE_AIPI << self::LSB_EA_MODE_SHIFT; // Address Post Increment (aN)+
    const LSB_EA_AIPD  = IEffectiveAddress::MODE_AIPD << self::LSB_EA_MODE_SHIFT; // Address Pre Decrement -(aN)
    const LSB_EA_AD    = IEffectiveAddress::MODE_AID  << self::LSB_EA_MODE_SHIFT; // Address with Displacement (d16, aN)
    const LSB_EA_AII   = IEffectiveAddress::MODE_AII  << self::LSB_EA_MODE_SHIFT; // Address with Index (d8, An, Xn)

    const LSB_EA_X     = IEffectiveAddress::MODE_X    << self::LSB_EA_MODE_SHIFT; // Special cases

    // Special EA cases
    //                 =   ----------xxxxxx
    const LSB_EA_SHORT = 0b0000000000111000; // Absolute short (xxx).w
    const LSB_EA_LONG  = 0b0000000000111001; // Absolute long (xxx).l
    const LSB_EA_PC_D  = 0b0000000000111010; // Program counter with displacement (d16, pc)
    const LSB_EA_PC_X  = 0b0000000000111011; // Program counter with index (d8, pc, xN)
    const LSB_EA_IMM   = 0b0000000000111100; // Immediate #imm

    // Specific condition code requirenents for MASK_CC_TYPE
    const CC_SHIFT = 8;
    const CC_T     = IConditionCode::T  << self::CC_SHIFT; // True
    const CC_F     = IConditionCode::F  << self::CC_SHIFT; // False
    const CC_HI    = IConditionCode::HI << self::CC_SHIFT; // High
    const CC_LS    = IConditionCode::LS << self::CC_SHIFT; // Low or Same
    const CC_CC    = IConditionCode::CC << self::CC_SHIFT; // Carry Clear
    const CC_CS    = IConditionCode::CS << self::CC_SHIFT; // Carry Set
    const CC_NE    = IConditionCode::NE << self::CC_SHIFT; // Not Equal
    const CC_EQ    = IConditionCode::EQ << self::CC_SHIFT; // Equal
    const CC_VC    = IConditionCode::VC << self::CC_SHIFT; // Overflow Clear
    const CC_VS    = IConditionCode::VS << self::CC_SHIFT; // Overflow Set
    const CC_PL    = IConditionCode::PL << self::CC_SHIFT; // Plus
    const CC_MI    = IConditionCode::HI << self::CC_SHIFT; // Minus
    const CC_GE    = IConditionCode::GE << self::CC_SHIFT; // Greater or Equal
    const CC_LT    = IConditionCode::LT << self::CC_SHIFT; // Less Than
    const CC_GT    = IConditionCode::GT << self::CC_SHIFT; // Greater Than
    const CC_LE    = IConditionCode::LE << self::CC_SHIFT; // Less or Equal

    // Brief Extension Word register bits m|rrr|000|dddddddd
    // m = mode (1: address, 0: data)
    // r = register num
    // d = displacement
    const BXW_REG_SHIFT = 12;
    const BXW_DISP_MASK = 0xFF;
    const BXW_REG_MASK  = 0x7000;
    const BXW_REG_ADDR  = 0x8000;
    const BXW_IDX_SIZE  = 0x0800;
    const BXW_IDX_REG   = self::BXW_REG_MASK|self::BXW_REG_ADDR;
    const BXW_REG_D0    = IRegister::D0 << self::BXW_REG_SHIFT;
    const BXW_REG_D1    = IRegister::D1 << self::BXW_REG_SHIFT;
    const BXW_REG_D2    = IRegister::D2 << self::BXW_REG_SHIFT;
    const BXW_REG_D3    = IRegister::D3 << self::BXW_REG_SHIFT;
    const BXW_REG_D4    = IRegister::D4 << self::BXW_REG_SHIFT;
    const BXW_REG_D5    = IRegister::D5 << self::BXW_REG_SHIFT;
    const BXW_REG_D6    = IRegister::D6 << self::BXW_REG_SHIFT;
    const BXW_REG_D7    = IRegister::D7 << self::BXW_REG_SHIFT;

    const BXW_REG_A0    = self::BXW_REG_ADDR | (IRegister::A0 << self::BXW_REG_SHIFT);
    const BXW_REG_A1    = self::BXW_REG_ADDR | (IRegister::A1 << self::BXW_REG_SHIFT);
    const BXW_REG_A2    = self::BXW_REG_ADDR | (IRegister::A2 << self::BXW_REG_SHIFT);
    const BXW_REG_A3    = self::BXW_REG_ADDR | (IRegister::A3 << self::BXW_REG_SHIFT);
    const BXW_REG_A4    = self::BXW_REG_ADDR | (IRegister::A4 << self::BXW_REG_SHIFT);
    const BXW_REG_A5    = self::BXW_REG_ADDR | (IRegister::A5 << self::BXW_REG_SHIFT);
    const BXW_REG_A6    = self::BXW_REG_ADDR | (IRegister::A6 << self::BXW_REG_SHIFT);
    const BXW_REG_A7    = self::BXW_REG_ADDR | (IRegister::A7 << self::BXW_REG_SHIFT);

}

