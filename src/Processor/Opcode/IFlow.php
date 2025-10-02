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
 * Opode prefixes for flow instructions
 */
interface IFlow
{
//     const OP_STOP     = 0b0100111001110010; // exact match
//     const OP_RTE      = 0b0100111001110011; // exact match
//     const OP_TRAPV    = 0b0100111001110110; // exact match
//     const OP_RTR      = 0b0100111001110111; // exact match

    //                              EAEAEA
    const OP_JMP      = 0b0100111011000000;
    const OP_JSR      = 0b0100111010000000;
    const OP_RTS      = 0b0100111001110101;
    //                            xxxxxxxx
    const OP_BSR      = 0b0110000100000000;

    // BT is actually branch unconditional
    //                    ----ccccdddddddd // condition / displacement
    const OP_BRA      = 0b0110000000000000;
    const OP_BHI      = self::OP_BRA|IOpcode::CC_HI;
    const OP_BLS      = self::OP_BRA|IOpcode::CC_LS;
    const OP_BCC      = self::OP_BRA|IOpcode::CC_CC;
    const OP_BCS      = self::OP_BRA|IOpcode::CC_CS;
    const OP_BNE      = self::OP_BRA|IOpcode::CC_NE;
    const OP_BEQ      = self::OP_BRA|IOpcode::CC_EQ;
    const OP_BVC      = self::OP_BRA|IOpcode::CC_VC;
    const OP_BVS      = self::OP_BRA|IOpcode::CC_VS;
    const OP_BPL      = self::OP_BRA|IOpcode::CC_PL;
    const OP_BMI      = self::OP_BRA|IOpcode::CC_MI;
    const OP_BGE      = self::OP_BRA|IOpcode::CC_GE;
    const OP_BLT      = self::OP_BRA|IOpcode::CC_LT;
    const OP_BGT      = self::OP_BRA|IOpcode::CC_GT;
    const OP_BLE      = self::OP_BRA|IOpcode::CC_LE;

    //                    0101cccc11001rrr // condition / reg
    const OP_DBT      = 0b0101000011001000;
    const OP_DBF      = self::OP_DBT|IOpcode::CC_F;
    const OP_DBHI     = self::OP_DBT|IOpcode::CC_HI;
    const OP_DBLS     = self::OP_DBT|IOpcode::CC_LS;
    const OP_DBCC     = self::OP_DBT|IOpcode::CC_CC;
    const OP_DBCS     = self::OP_DBT|IOpcode::CC_CS;
    const OP_DBNE     = self::OP_DBT|IOpcode::CC_NE;
    const OP_DBEQ     = self::OP_DBT|IOpcode::CC_EQ;
    const OP_DBVC     = self::OP_DBT|IOpcode::CC_VC;
    const OP_DBVS     = self::OP_DBT|IOpcode::CC_VS;
    const OP_DBPL     = self::OP_DBT|IOpcode::CC_PL;
    const OP_DBMI     = self::OP_DBT|IOpcode::CC_MI;
    const OP_DBGE     = self::OP_DBT|IOpcode::CC_GE;
    const OP_DBLT     = self::OP_DBT|IOpcode::CC_LT;
    const OP_DBGT     = self::OP_DBT|IOpcode::CC_GT;
    const OP_DBLE     = self::OP_DBT|IOpcode::CC_LE;
}
