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
 * Basic EA enumerations
 */
interface IEffectiveAddress
{
    // GP Register based EA m odes
    const MODE_D     = 0; // Data register dN
    const MODE_A     = 1; // Address register aN
    const MODE_AI    = 2; // Address (aN)
    const MODE_AIPI  = 3; // Address Post Increment (aN)+
    const MODE_AIPD  = 4; // Address Pre Decrement -(aN)
    const MODE_AID   = 5; // Address with Displacement (d16, aN)
    const MODE_AII   = 6; // Address with Index (d8, An, Xn)

    const MODE_X     = 7; // All other cases, low bits are not register

    // Non - GP Register values for Mode X
    const MODE_X_SHORT  = 0; // Absolute short (xxx).w
    const MODE_X_LONG   = 1; // Absolute long (xxx).l
    const MODE_X_PC_D   = 2; // Program counter with displacement (d16, pc)
    const MODE_X_PC_X   = 3; // Program counter with index (d8, pc, xN)
    const MODE_X_IMM    = 4; // Immediate #imm
    const MASK_BASE_REG = 7;
}

