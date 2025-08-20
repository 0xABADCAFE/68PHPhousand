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
interface IConditional
{
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

    // BT is actually branch unconditional
    //                    ----ccccdddddddd // condition / displacement
    const OP_ST       = 0b0101000000000000;
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
