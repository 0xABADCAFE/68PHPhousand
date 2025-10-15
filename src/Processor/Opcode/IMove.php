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
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;

/**
 * Opode prefixes for conditional instructions
 */
interface IMove
{
    //                         ssEAEAEA
    const OP_CLR_B  = 0b0100001000000000;
    const OP_CLR_W  = 0b0100001001000000;
    const OP_CLR_L  = 0b0100001010000000;

    //                    ssEAEAEAEAEAEA - EA Destination, EA Source
    const OP_MOVE_B = 0b0001000000000000;
    const OP_MOVE_W = 0b0011000000000000;
    const OP_MOVE_L = 0b0010000000000000;

    //                    ssrrr   EAEAEA
    const OP_MOVEA  = 0b0000000001000000;

    //                      rrr nnnnnnnn
    const OP_MOVEQ  = 0b0111000000000000;

    //                            d   sEAEAEA
    const OP_MOVEM_R2M_W = 0b0100100010000000;
    const OP_MOVEM_R2M_L = 0b0100100011000000;
    const OP_MOVEM_M2R_W = 0b0100110010000000;
    const OP_MOVEM_M2R_L = 0b0100110011000000;

    const OP_MOVEM_R2M_EA = [
        IEffectiveAddress::MODE_AI   => IRegister::ADDR_REGS, // (aN)
        IEffectiveAddress::MODE_AIPI => IRegister::ADDR_REGS, // (aN)+
        IEffectiveAddress::MODE_AIPD => IRegister::ADDR_REGS, // -(aN)
        IEffectiveAddress::MODE_AID  => IRegister::ADDR_REGS, // d16(aN)
        IEffectiveAddress::MODE_AII  => IRegister::ADDR_REGS, // d8(xN,aN)
        IEffectiveAddress::MODE_X    => [
            IEffectiveAddress::MODE_X_SHORT,                  // (xxx).w
            IEffectiveAddress::MODE_X_LONG,                   // (xxx).l
            IEffectiveAddress::MODE_X_PC_D,                   // d16(pc)
            IEffectiveAddress::MODE_X_PC_X,                   // d8(xN,pc)
        ]
    ];

    const OP_MOVEM_M2R_EA = [
        IEffectiveAddress::MODE_AI   => IRegister::ADDR_REGS, // (aN)
        IEffectiveAddress::MODE_AIPI => IRegister::ADDR_REGS, // (aN)+
        IEffectiveAddress::MODE_AIPD => IRegister::ADDR_REGS, // -(aN)
        IEffectiveAddress::MODE_AID  => IRegister::ADDR_REGS, // d16(aN)
        IEffectiveAddress::MODE_AII  => IRegister::ADDR_REGS, // d8(xN,aN)
        IEffectiveAddress::MODE_X    => [
            IEffectiveAddress::MODE_X_SHORT,                  // (xxx).w
            IEffectiveAddress::MODE_X_LONG,                   // (xxx).l
            IEffectiveAddress::MODE_X_PC_D,                   // d16(pc)
            IEffectiveAddress::MODE_X_PC_X,                   // d8(xN,pc)
        ]
    ];



    const OP_MOVE_2_CCR  = 0b0100010011000000;

    // 010+
    const OP_MOVE_CCR    = 0b0100001011000000;

    //                               rrr
    const OP_SWAP   = 0b0100100001000000;

    //                      rrr   EAEAEA
    const OP_LEA    = 0b0100000111000000;

    //                            EAEAEA
    const OP_PEA    = 0b0100100001000000;

    //                      xxx1mmmmmyyy ; EXG Rx,Ry
    const OP_EXG_DD = 0b1100000101000000; // exg dx,dy
    const OP_EXG_AA = 0b1100000101001000; // exg ax,ay
    const OP_EXG_DA = 0b1100000110001000; // exg dx,ay

    const MASK_DST_EA = 0b0000111111000000;

    const OP_MOVE_SRC_EA_SHIFT = 6;

    // Set Conditional
    //                    0101cccc11EAEAEA // condition / displacement
    const OP_ST       = 0b0101000011000000;
    const OP_SF       = self::OP_ST|IOpcode::CC_F;
    const OP_SHI      = self::OP_ST|IOpcode::CC_HI;
    const OP_SLS      = self::OP_ST|IOpcode::CC_LS;
    const OP_SCC      = self::OP_ST|IOpcode::CC_CC;
    const OP_SCS      = self::OP_ST|IOpcode::CC_CS;
    const OP_SNE      = self::OP_ST|IOpcode::CC_NE;
    const OP_SEQ      = self::OP_ST|IOpcode::CC_EQ;
    const OP_SVC      = self::OP_ST|IOpcode::CC_VC;
    const OP_SVS      = self::OP_ST|IOpcode::CC_VS;
    const OP_SPL      = self::OP_ST|IOpcode::CC_PL;
    const OP_SMI      = self::OP_ST|IOpcode::CC_MI;
    const OP_SGE      = self::OP_ST|IOpcode::CC_GE;
    const OP_SLT      = self::OP_ST|IOpcode::CC_LT;
    const OP_SGT      = self::OP_ST|IOpcode::CC_GT;
    const OP_SLE      = self::OP_ST|IOpcode::CC_LE;
}
